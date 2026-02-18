<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\OpnameEntry;
use App\Models\OpnameSession;
use App\Models\ActivityLog;
use App\Services\VarianceService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(private VarianceService $varianceService) {}

    /**
     * Receive opname data from n8n webhook
     *
     * Expected payload:
     * {
     *   "session_code": "SO-20260218-001",  // optional, will create new if not provided
     *   "warehouse_code": "WH-001",
     *   "items": [
     *     {"item_code": "ITM-001", "counted_qty": 100, "notes": "optional"},
     *     ...
     *   ]
     * }
     */
    public function receive(Request $request)
    {
        // Simple token validation
        $token = config('services.n8n.webhook_token');
        if ($token && $request->header('X-Webhook-Token') !== $token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'warehouse_code' => 'required|string|exists:warehouses,code',
            'session_code' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_code' => 'required|string',
            'items.*.counted_qty' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $warehouse = \App\Models\Warehouse::where('code', $validated['warehouse_code'])->firstOrFail();

        // Find or create session
        $session = null;
        if (!empty($validated['session_code'])) {
            $session = OpnameSession::where('session_code', $validated['session_code'])->first();
        }

        if (!$session) {
            $session = OpnameSession::create([
                'session_code' => $validated['session_code'] ?? OpnameSession::generateCode(),
                'warehouse_id' => $warehouse->id,
                'conducted_by' => 1, // system user
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        $imported = 0;
        $errors = [];

        foreach ($validated['items'] as $itemData) {
            $item = Item::where('item_code', $itemData['item_code'])
                ->where('warehouse_id', $warehouse->id)
                ->first();

            if (!$item) {
                $errors[] = "Item '{$itemData['item_code']}' not found in warehouse {$warehouse->code}";
                continue;
            }

            $entry = OpnameEntry::updateOrCreate(
                [
                    'opname_session_id' => $session->id,
                    'item_id' => $item->id,
                ],
                [
                    'system_qty' => $item->system_stock,
                    'counted_qty' => $itemData['counted_qty'],
                    'notes' => $itemData['notes'] ?? null,
                ]
            );

            $entry->calculateVariance();
            $entry->save();
            $imported++;
        }

        ActivityLog::log($session, 'webhook_received', null, [
            'imported' => $imported,
            'errors' => $errors,
        ]);

        return response()->json([
            'success' => true,
            'session_code' => $session->session_code,
            'imported' => $imported,
            'errors' => $errors,
        ]);
    }

    /**
     * Get system stock data for n8n to compare
     */
    public function systemStock(Request $request)
    {
        $token = config('services.n8n.webhook_token');
        if ($token && $request->header('X-Webhook-Token') !== $token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $items = Item::with('warehouse')
            ->when($request->warehouse_code, function ($q, $code) {
                $q->whereHas('warehouse', fn($wq) => $wq->where('code', $code));
            })
            ->get()
            ->map(fn($item) => [
                'item_code' => $item->item_code,
                'name' => $item->name,
                'system_stock' => $item->system_stock,
                'unit' => $item->unit,
                'warehouse_code' => $item->warehouse->code,
            ]);

        return response()->json(['items' => $items]);
    }
}
