<?php

namespace App\Http\Controllers;

use App\Models\OpnameSession;
use App\Models\OpnameEntry;
use App\Models\VarianceReview;
use App\Models\ActivityLog;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Summary cards
        $totalSessions = OpnameSession::count();
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

        // Variance by warehouse
        $varianceByWarehouse = DB::table('opname_entries')
            ->join('opname_sessions', 'opname_entries.opname_session_id', '=', 'opname_sessions.id')
            ->join('warehouses', 'opname_sessions.warehouse_id', '=', 'warehouses.id')
            ->select(
                'warehouses.name as warehouse',
                DB::raw('SUM(ABS(opname_entries.variance)) as total_variance'),
                DB::raw('COUNT(*) as entry_count')
            )
            ->groupBy('warehouses.id', 'warehouses.name')
            ->get();

        // Recent activity
        $recentActivity = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Top discrepancy items
        $topDiscrepancies = OpnameEntry::with(['item', 'session.warehouse'])
            ->orderByRaw('ABS(variance) DESC')
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'totalSessions', 'activeSessions', 'totalEntries', 'totalVariances',
            'pendingReviews', 'autoApproved', 'varianceDistribution', 'statusDistribution',
            'varianceByWarehouse', 'recentActivity', 'topDiscrepancies'
        ));
    }
}
