<?php

namespace App\Http\Controllers;

use App\Models\OpnameSession;
use App\Models\OpnameEntry;
use App\Models\VarianceReview;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Summary cards
        $activeSessions = OpnameSession::where('status', 'in_progress')->count();
        $totalEntries = OpnameEntry::count();
        $totalVariances = OpnameEntry::where('variance', '!=', 0)->count();
        $pendingReviews = VarianceReview::whereIn('status', ['pending', 'escalated'])->count();
        $autoApproved = VarianceReview::where('status', 'auto_approved')->count();

        // Variance distribution (for pie chart)
        $varianceDistribution = VarianceReview::select('severity', DB::raw('count(*) as count'))
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        // Status distribution
        $statusDistribution = VarianceReview::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Recent activity
        $recentActivity = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Top discrepancy items
        $topDiscrepancies = OpnameEntry::with(['item'])
            ->orderByRaw('ABS(variance) DESC')
            ->take(10)
            ->get();

        return Inertia::render('Dashboard', [
            'activeSessions' => $activeSessions,
            'totalEntries' => $totalEntries,
            'totalVariances' => $totalVariances,
            'pendingReviews' => $pendingReviews,
            'autoApproved' => $autoApproved,
            'varianceDistribution' => $varianceDistribution,
            'statusDistribution' => $statusDistribution,
            'recentActivity' => $recentActivity,
            'topDiscrepancies' => $topDiscrepancies,
        ]);
    }
}
