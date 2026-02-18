<?php

namespace App\Http\Controllers;

use App\Models\OpnameEntry;
use App\Models\OpnameSession;
use App\Models\VarianceReview;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::active()->get();

        // Variance distribution by severity
        $severityData = VarianceReview::select('severity', DB::raw('count(*) as count'))
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        // Variance by warehouse
        $warehouseData = DB::table('opname_entries')
            ->join('opname_sessions', 'opname_entries.opname_session_id', '=', 'opname_sessions.id')
            ->join('warehouses', 'opname_sessions.warehouse_id', '=', 'warehouses.id')
            ->select(
                'warehouses.name',
                DB::raw('SUM(ABS(opname_entries.variance)) as total_abs_variance'),
                DB::raw('AVG(ABS(opname_entries.variance)) as avg_abs_variance'),
                DB::raw('COUNT(*) as total_entries'),
                DB::raw('SUM(CASE WHEN opname_entries.variance != 0 THEN 1 ELSE 0 END) as variance_entries')
            )
            ->when($request->warehouse_id, fn($q, $w) => $q->where('warehouses.id', $w))
            ->groupBy('warehouses.id', 'warehouses.name')
            ->get();

        // Top discrepancy items
        $topItems = OpnameEntry::with(['item', 'session.warehouse'])
            ->where('variance', '!=', 0)
            ->orderByRaw('ABS(variance) DESC')
            ->take(20)
            ->get();

        // Approval turnaround time (avg hours between entry creation and review)
        $avgTurnaround = VarianceReview::whereNotNull('reviewed_at')
            ->where('auto_resolved', false)
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, reviewed_at)) as avg_hours')
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

        return view('analytics.index', compact(
            'warehouses', 'severityData', 'warehouseData', 'topItems',
            'avgTurnaround', 'shrinkageTrend', 'statusSummary'
        ));
    }
}
