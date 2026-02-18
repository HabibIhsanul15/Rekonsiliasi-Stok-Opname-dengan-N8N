<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-white tracking-tight">Analitik &amp; Laporan</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Summary Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 animate-fade-in-up">
            <div class="stat-card">
                <div class="stat-icon bg-emerald-500"></div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Otomatis Disetujui</p>
                <p class="text-2xl font-bold text-emerald-400 mt-1">{{ number_format($statusSummary['auto_approved'] ?? 0) }}</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-amber-500"></div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Menunggu Review</p>
                <p class="text-2xl font-bold text-amber-400 mt-1">{{ number_format($statusSummary['pending'] ?? 0) }}</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-red-500"></div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Eskalasi</p>
                <p class="text-2xl font-bold text-red-400 mt-1">{{ number_format($statusSummary['escalated'] ?? 0) }}</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-indigo-500"></div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Rata-rata Waktu Proses</p>
                <p class="text-2xl font-bold text-indigo-400 mt-1">{{ $avgTurnaround ? number_format($avgTurnaround, 1) . 'h' : 'N/A' }}</p>
            </div>
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in-up" style="animation-delay: 0.1s;">
            <div class="glass-card p-6">
                <h3 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span> Distribusi Tingkat Keparahan
                </h3>
                <canvas id="severityPie" height="250"></canvas>
            </div>
            <div class="glass-card p-6">
                <h3 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Selisih per Departemen
                </h3>
                <canvas id="warehouseBar" height="250"></canvas>
            </div>
        </div>

        {{-- Shrinkage Trend --}}
        <div class="glass-card p-6 animate-fade-in-up" style="animation-delay: 0.2s;">
            <h3 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan-500"></span> Tren Penyusutan (Bulanan)
            </h3>
            <canvas id="shrinkageLine" height="120"></canvas>
        </div>

        {{-- Top Discrepancy Items --}}
        <div class="glass-card p-6 animate-fade-in-up" style="animation-delay: 0.3s;">
            <h3 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-red-500"></span> 20 Item Selisih Terbesar
            </h3>
            @if($topItems->count() > 0)
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Item</th><th>Departemen</th>
                            <th class="text-right">Sistem</th><th class="text-right">Fisik</th>
                            <th class="text-right">Selisih</th><th class="text-right">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topItems as $i => $entry)
                        <tr>
                            <td class="text-slate-600">{{ $i + 1 }}</td>
                            <td>
                                <span class="font-medium text-slate-200">{{ $entry->item->name ?? '-' }}</span>
                                <span class="block text-xs text-slate-500">{{ $entry->item->item_code ?? '-' }}</span>
                            </td>
                            <td class="text-slate-400">{{ $entry->session->warehouse->name ?? '-' }}</td>
                            <td class="text-right text-slate-300">{{ number_format($entry->system_qty, 2) }}</td>
                            <td class="text-right text-slate-300">{{ number_format($entry->counted_qty, 2) }}</td>
                            <td class="text-right font-semibold {{ $entry->variance < 0 ? 'text-red-400' : 'text-emerald-400' }}">
                                {{ $entry->variance > 0 ? '+' : '' }}{{ number_format($entry->variance, 2) }}
                            </td>
                            <td class="text-right {{ abs($entry->variance_pct) > 10 ? 'text-red-400 font-semibold' : 'text-slate-500' }}">
                                {{ number_format($entry->variance_pct, 1) }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else <p class="text-slate-500 text-sm">Belum ada data.</p> @endif
        </div>

        {{-- Warehouse Detail --}}
        <div class="glass-card p-6 animate-fade-in-up" style="animation-delay: 0.4s;">
            <h3 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span> Detail Departemen
            </h3>
            @if($warehouseData->count() > 0)
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Departemen</th>
                            <th class="text-right">Total Entry</th>
                            <th class="text-right">Ada Selisih</th>
                            <th class="text-right">Total Selisih Abs</th>
                            <th class="text-right">Rata-rata Selisih Abs</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warehouseData as $wd)
                        <tr>
                            <td class="font-medium text-slate-200">{{ $wd->name }}</td>
                            <td class="text-right text-slate-300">{{ number_format($wd->total_entries) }}</td>
                            <td class="text-right text-orange-400 font-medium">{{ number_format($wd->variance_entries) }}</td>
                            <td class="text-right text-red-400 font-semibold">{{ number_format($wd->total_abs_variance, 2) }}</td>
                            <td class="text-right text-slate-300">{{ number_format($wd->avg_abs_variance, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else <p class="text-slate-500 text-sm">Belum ada data.</p> @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        Chart.defaults.color = '#94a3b8';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.05)';

        const severityData = @json($severityData);
        const warehouseData = @json($warehouseData);
        const shrinkageData = @json($shrinkageTrend);

        if (Object.keys(severityData).length > 0) {
            new Chart(document.getElementById('severityPie'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(severityData).map(s => {
                        const map = {'low': 'Rendah', 'medium': 'Sedang', 'high': 'Tinggi', 'critical': 'Kritis'};
                        return map[s] || s;
                    }),
                    datasets: [{ data: Object.values(severityData), backgroundColor: ['#10B981','#F59E0B','#F97316','#EF4444'], borderWidth: 0, hoverOffset: 8 }]
                },
                options: { responsive: true, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } } } }
            });
        }

        if (warehouseData.length > 0) {
            new Chart(document.getElementById('warehouseBar'), {
                type: 'bar',
                data: {
                    labels: warehouseData.map(w => w.name),
                    datasets: [
                        { label: 'Total Selisih Absolut', data: warehouseData.map(w => parseFloat(w.total_abs_variance)), backgroundColor: 'rgba(99,102,241,0.6)', borderRadius: 8 },
                        { label: 'Rata-rata Selisih', data: warehouseData.map(w => parseFloat(w.avg_abs_variance)), backgroundColor: 'rgba(139,92,246,0.4)', borderRadius: 8 }
                    ]
                },
                options: { responsive: true, plugins: { legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle' } } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.03)' } }, x: { grid: { display: false } } } }
            });
        }

        if (shrinkageData.length > 0) {
            new Chart(document.getElementById('shrinkageLine'), {
                type: 'line',
                data: {
                    labels: shrinkageData.map(s => s.month),
                    datasets: [
                        { label: 'Total Selisih (Bersih)', data: shrinkageData.map(s => parseFloat(s.total_variance)), borderColor: '#EF4444', backgroundColor: 'rgba(239,68,68,0.08)', fill: true, tension: 0.4 },
                        { label: 'Total Selisih Absolut', data: shrinkageData.map(s => parseFloat(s.total_abs_variance)), borderColor: '#6366F1', backgroundColor: 'rgba(99,102,241,0.08)', fill: true, tension: 0.4 }
                    ]
                },
                options: { responsive: true, plugins: { legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle' } } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.03)' } }, x: { grid: { display: false } } } }
            });
        }
    </script>
    @endpush
</x-app-layout>
