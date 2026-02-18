<?php

namespace App\Http\Controllers;

use App\Models\OpnameSession;
use App\Models\Warehouse;
use App\Services\VarianceService;
use Illuminate\Http\Request;

class OpnameSessionController extends Controller
{
    public function __construct(private VarianceService $varianceService) {}

    public function index(Request $request)
    {
        $sessions = OpnameSession::with(['warehouse', 'conductor'])
            ->withCount('entries')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->warehouse_id, fn($q, $w) => $q->where('warehouse_id', $w))
            ->latest()
            ->paginate(15);

        $warehouses = Warehouse::active()->get();

        return view('opname-sessions.index', compact('sessions', 'warehouses'));
    }

    public function create()
    {
        $warehouses = Warehouse::active()->get();
        return view('opname-sessions.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $session = OpnameSession::create([
            'session_code' => OpnameSession::generateCode(),
            'warehouse_id' => $validated['warehouse_id'],
            'conducted_by' => auth()->id(),
            'status' => 'draft',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('opname-sessions.show', $session)
            ->with('success', 'Sesi opname berhasil dibuat: ' . $session->session_code);
    }

    public function show(OpnameSession $opnameSession)
    {
        $opnameSession->load([
            'warehouse',
            'conductor',
            'entries.item',
            'entries.varianceReview',
        ]);

        $warehouseItems = $opnameSession->warehouse->items()->orderBy('item_code')->get();

        return view('opname-sessions.show', compact('opnameSession', 'warehouseItems'));
    }

    public function edit(OpnameSession $opnameSession)
    {
        if ($opnameSession->status === 'closed') {
            return back()->with('error', 'Sesi yang sudah ditutup tidak bisa diedit.');
        }

        $warehouses = Warehouse::active()->get();
        return view('opname-sessions.edit', compact('opnameSession', 'warehouses'));
    }

    public function update(Request $request, OpnameSession $opnameSession)
    {
        if ($opnameSession->status === 'closed') {
            return back()->with('error', 'Sesi yang sudah ditutup tidak bisa diedit.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|in:draft,in_progress',
        ]);

        $opnameSession->update($validated);

        return redirect()->route('opname-sessions.show', $opnameSession)
            ->with('success', 'Sesi opname berhasil diperbarui.');
    }

    public function destroy(OpnameSession $opnameSession)
    {
        if ($opnameSession->status !== 'draft') {
            return back()->with('error', 'Hanya sesi draft yang bisa dihapus.');
        }

        $opnameSession->delete();
        return redirect()->route('opname-sessions.index')
            ->with('success', 'Sesi opname berhasil dihapus.');
    }

    /**
     * Start the session (change status to in_progress)
     */
    public function start(OpnameSession $opnameSession)
    {
        if ($opnameSession->status !== 'draft') {
            return back()->with('error', 'Sesi sudah dimulai atau selesai.');
        }

        $opnameSession->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return back()->with('success', 'Sesi opname dimulai.');
    }

    /**
     * Process variance for all entries
     */
    public function process(OpnameSession $opnameSession)
    {
        if ($opnameSession->entries()->count() === 0) {
            return back()->with('error', 'Tidak ada data untuk diproses.');
        }

        $stats = $this->varianceService->processSession($opnameSession);

        return back()->with('success',
            "Variance diproses: {$stats['total']} total, {$stats['auto_approved']} auto-approved, {$stats['pending']} pending, {$stats['escalated']} escalated."
        );
    }
}
