<?php

namespace App\Http\Controllers;

use App\Models\OpnameSession;
use App\Services\VarianceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OpnameSessionController extends Controller
{
    public function __construct(private VarianceService $varianceService) {}

    public function show(OpnameSession $opnameSession)
    {
        $opnameSession->load(['conductor', 'entries.item', 'entries.varianceReview', 'varianceReviews']);
        
        return Inertia::render('OpnameSessions/Show', [
            'session' => $opnameSession
        ]);
    }

    /**
     * Mark session as completed (after reconciliation)
     */
    public function complete(OpnameSession $opnameSession)
    {
        $opnameSession->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('opname-sessions.show', $opnameSession->id)
            ->with('success', 'Sesi opname berhasil diselesaikan.');
    }

    public function destroy(OpnameSession $opnameSession)
    {
        $opnameSession->delete();
        
        return redirect('/import')->with('success', 'Riwayat opname berhasil dihapus.');
    }
}
