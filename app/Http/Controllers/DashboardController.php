<?php

namespace App\Http\Controllers;

use App\Models\OpnameSession;
use App\Models\OpnameEntry;
use App\Models\VarianceReview;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Summary Cards
        $totalEntries = OpnameEntry::count();
        $totalVariances = OpnameEntry::where('variance', '!=', 0)->count();
        $matchedItems = $totalEntries - $totalVariances;
        
        // Hitung Akurasi: (Item Cocok / Total Item) * 100
        $accuracyRate = $totalEntries > 0 ? round(($matchedItems / $totalEntries) * 100, 1) : 0;

        $surplusItems = OpnameEntry::where('variance', '>', 0)->count();
        $deficitItems = OpnameEntry::where('variance', '<', 0)->count();

        // 2. Chart Kiri: Distribusi Selisih (Plus vs Minus)
        // Kita hitung jumlah item yang plus vs minus
        $distributionData = [
            ['name' => 'Surplus (+)', 'value' => $surplusItems, 'color' => '#10B981'], // Hijau
            ['name' => 'Defisit (-)', 'value' => $deficitItems, 'color' => '#EF4444'], // Merah
        ];

        // 3. Chart Kanan: Trend Selisih per Tanggal (Line Chart)
        // Sumbu X: Tanggal, Sumbu Y: Total Selisih Absolut (Seberapa melenceng stoknya)
        $trendData = OpnameEntry::select(
            DB::raw('DATE(created_at) as date'), 
            DB::raw('SUM(ABS(variance)) as total_diff')
        )
        ->groupBy('date')
        ->orderBy('date', 'ASC')
        ->take(7) // Ambil 7 hari terakhir
        ->get()
        ->map(fn($item) => [
            'date' => Carbon::parse($item->date)->format('d M'),
            'value' => (float) $item->total_diff
        ]);

        // 4. Tabel Bawah: Top 10 Selisih Terbesar
        $topDiscrepancies = OpnameEntry::with(['item'])
            ->where('variance', '!=', 0)
            ->orderByRaw('ABS(variance) DESC')
            ->take(10)
            ->get();

        // 5. Recent Activity
        $recentActivity = ActivityLog::with('user')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'totalEntries' => $totalEntries,
            'totalVariances' => $totalVariances,
            'accuracyRate' => $accuracyRate,
            'surplusItems' => $surplusItems,
            'deficitItems' => $deficitItems,
            'distributionData' => $distributionData,
            'trendData' => $trendData,
            'topDiscrepancies' => $topDiscrepancies,
            'recentActivity' => $recentActivity,
        ]);
    }
}
