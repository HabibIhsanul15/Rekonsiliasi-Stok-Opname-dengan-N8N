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

    public function destroy(OpnameSession $opnameSession)
    {
        // Delete related files if any (optional, usually handled by model boot or manually)
        // Here we just rely on cascade delete for database records
        // For physical files (imports), could delete them too
        
        $opnameSession->delete();
        
        return redirect('/import')->with('success', 'Riwayat opname berhasil dihapus.');
    }
}
