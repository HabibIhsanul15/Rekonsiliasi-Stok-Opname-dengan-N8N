<?php

namespace App\Http\Controllers;

use App\Models\OpnameEntry;
use App\Models\OpnameSession;
use App\Models\VarianceReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // Variance distribution by severity
        $severityData = VarianceReview::select('severity', DB::raw('count(*) as count'))
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        // Top discrepancy items
        $topItems = OpnameEntry::with(['item'])
            ->where('variance', '!=', 0)
            ->orderByRaw('ABS(variance) DESC')
            ->take(20)
            ->get();

        // Approval turnaround time (avg hours between entry creation and review)
        $avgTurnaround = VarianceReview::whereNotNull('reviewed_at')
            ->where('auto_resolved', false)
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, reviewed_at) / 3600) as avg_hours')
            ->value('avg_hours');

        // Shrinkage trend (monthly)
        $shrinkageTrend = DB::table('opname_entries')
            ->join('opname_sessions', 'opname_entries.opname_session_id', '=', 'opname_sessions.id')
            ->select(
                DB::raw("DATE_FORMAT(opname_sessions.started_at, '%Y-%m') as month"),
                DB::raw('SUM(opname_entries.variance) as total_variance'),
                DB::raw('SUM(ABS(opname_entries.variance)) as total_abs_variance'),
                DB::raw('COUNT(*) as total_entries')
            )
            ->whereNotNull('opname_sessions.started_at')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Status summary
        $statusSummary = VarianceReview::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return Inertia::render('Analytics', [
            'severityData' => $severityData,
            'topItems' => $topItems,
            'avgTurnaround' => $avgTurnaround,
            'shrinkageTrend' => $shrinkageTrend,
            'statusSummary' => $statusSummary,
        ]);
    }
}
