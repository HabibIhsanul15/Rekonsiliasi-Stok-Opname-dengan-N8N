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
     * Receive opname/reconciliation results from n8n webhook
     *
     * Expected payload (hasil rekonsiliasi dari N8N):
     * {
     *   "session_code": "SO-20260218-001",  // optional, will create new if not provided
     *   "items": [
     *     {"item_code": "100001", "counted_qty": 128, "system_qty": 130, "notes": "kurang 2"},
     *     {"item_code": "100002", "counted_qty": 1100, "system_qty": 1111, "notes": "selisih 11"},
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
            'session_code' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_code' => 'required|string',
            'items.*.counted_qty' => 'required|numeric|min:0',
            'items.*.system_qty' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        // Find or create session
        $session = null;
        if (!empty($validated['session_code'])) {
            $session = OpnameSession::where('session_code', $validated['session_code'])->first();
        }

        if (!$session) {
            $session = OpnameSession::create([
                'session_code' => $validated['session_code'] ?? OpnameSession::generateCode(),
                'conducted_by' => 1, // system user
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        $imported = 0;
        $errors = [];

        foreach ($validated['items'] as $itemData) {
            $item = Item::where('item_code', $itemData['item_code'])->first();

            if (!$item) {
                $errors[] = "Item '{$itemData['item_code']}' not found in system";
                continue;
            }

            $systemQty = $itemData['system_qty'] ?? 0;
            $countedQty = $itemData['counted_qty'];
            $variance = $countedQty - $systemQty;
            $variancePct = $systemQty != 0
                ? round($variance / $systemQty * 100, 2)
                : 0;

            $entry = OpnameEntry::updateOrCreate(
                [
                    'opname_session_id' => $session->id,
                    'item_id' => $item->id,
                ],
                [
                    'system_qty' => $systemQty,
                    'counted_qty' => $countedQty,
                    'variance' => $variance,
                    'variance_pct' => $variancePct,
                    'notes' => $itemData['notes'] ?? null,
                ]
            );

            // Auto-create variance review
            $this->varianceService->createOrUpdateReview($entry);

            $imported++;
        }

        // Session stays 'in_progress' — user completes it manually after reviewing variance data

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

        $items = Item::with('unitConversions')->get()
            ->map(fn($item) => [
                'item_code' => $item->item_code,
                'name' => $item->name,
                'jenis_barang' => $item->jenis_barang,
                'kategori_barang' => $item->kategori_barang,
                'unit' => $item->unit,
                'unit_conversions' => $item->unitConversions->map(fn($c) => [
                    'unit_name' => $c->unit_name,
                    'conversion_qty' => $c->conversion_qty,
                ]),
            ]);

        return response()->json(['items' => $items]);
    }
    
    public function sessionEntries(Request $request, OpnameSession $opnameSession)
{
    // Auth sederhana pakai header X-API-KEY (biar tidak bisa diakses sembarang orang)
    $key = $request->header('X-API-KEY') ?? $request->query('token');
    abort_unless($key && hash_equals((string) env('N8N_API_KEY'), (string) $key), 401, 'Unauthorized');

    $entries = OpnameEntry::query()
        ->where('opname_session_id', $opnameSession->id)
        ->join('items', 'items.id', '=', 'opname_entries.item_id')
        ->orderBy('opname_entries.id')
        ->get([
            'opname_entries.id as entry_id',
            'items.item_code',
            'items.name as item_name',
            'items.unit',
            'opname_entries.counted_qty',
            'opname_entries.notes',
        ]);

    return response()->json([
        'session' => [
            'id' => $opnameSession->id,
            'session_code' => $opnameSession->session_code,
            'opname_date' => $opnameSession->opname_date,
            'status' => $opnameSession->status,
        ],
        'entries' => $entries,
    ]);
}
}
