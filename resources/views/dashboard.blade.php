<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Dasbor</h2>
                <p class="text-sm text-slate-500 mt-0.5">Ringkasan aktivitas stock opname</p>
            </div>
            <div class="text-xs text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 animate-fade-in-up">
            <div class="stat-card">
                <div class="stat-icon bg-indigo-500"></div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Sesi</p>
                <p class="text-2xl font-bold text-white mt-1">{{ number_format($totalSessions) }}</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-blue-500"></div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Sesi Aktif</p>
                <p class="text-2xl font-bold text-blue-400 mt-1">{{ number_format($activeSessions) }}</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-cyan-500"></div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Entry</p>
                <p class="text-2xl font-bold text-white mt-1">{{ number_format($totalEntries) }}</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-orange-500"></div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Selisih Ditemukan</p>
                <p class="text-2xl font-bold text-orange-400 mt-1">{{ number_format($totalVariances) }}</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-red-500"></div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Menunggu Review</p>
                <p class="text-2xl font-bold text-red-400 mt-1">{{ number_format($pendingReviews) }}</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-emerald-500"></div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Otomatis Disetujui</p>
                <p class="text-2xl font-bold text-emerald-400 mt-1">{{ number_format($autoApproved) }}</p>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in-up" style="animation-delay: 0.1s;">
            <div class="glass-card p-6">
                <h3 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Distribusi Keparahan
                </h3>
                <canvas id="severityChart" height="250"></canvas>
            </div>
            <div class="glass-card p-6">
                <h3 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span> Status Review
                </h3>
                <canvas id="statusChart" height="250"></canvas>
            </div>
        </div>

        {{-- Variance by Warehouse --}}
        <div class="glass-card p-6 animate-fade-in-up" style="animation-delay: 0.2s;">
            <h3 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan-500"></span> Selisih per Departemen
            </h3>
            <canvas id="warehouseChart" height="120"></canvas>
        </div>

        {{-- Top Discrepancies --}}
        <div class="glass-card p-6 animate-fade-in-up" style="animation-delay: 0.3s;">
            <h3 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-red-500"></span> 10 Item Selisih Terbesar
            </h3>
            @if($topDiscrepancies->count() > 0)
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-right">Sistem</th>
                            <th class="text-right">Fisik</th>
                            <th class="text-right">Selisih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topDiscrepancies as $entry)
                        <tr>
                            <td>
                                <span class="font-medium text-slate-200">{{ $entry->item->name ?? '-' }}</span>
                                <span class="block text-xs text-slate-500">{{ $entry->item->item_code ?? '-' }}</span>
                            </td>
                            <td class="text-right text-slate-300">{{ number_format($entry->system_qty, 2) }}</td>
                            <td class="text-right text-slate-300 font-medium">{{ number_format($entry->counted_qty, 2) }}</td>
                            <td class="text-right font-semibold {{ $entry->variance < 0 ? 'text-red-400' : ($entry->variance > 0 ? 'text-emerald-400' : 'text-slate-500') }}">
                                {{ $entry->variance > 0 ? '+' : '' }}{{ number_format($entry->variance, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-slate-500 text-sm">Belum ada data selisih.</p>
            @endif
        </div>

        {{-- Recent Activity --}}
        <div class="glass-card p-6 animate-fade-in-up" style="animation-delay: 0.4s;">
            <h3 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span> Aktivitas Terbaru
            </h3>
            @if($recentActivity->count() > 0)
            <div class="space-y-3">
                @foreach($recentActivity as $log)
                <div class="flex items-center gap-3 text-sm">
                    <span class="badge
                        {{ $log->action === 'approved' ? 'badge-green' :
                           ($log->action === 'rejected' ? 'badge-red' :
                           ($log->action === 'escalated' ? 'badge-yellow' :
                           'badge-blue')) }}">
                        {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                    </span>
                    <span class="text-slate-400">{{ $log->user->name ?? 'System' }}</span>
                    <span class="ml-auto text-slate-600 text-xs">{{ $log->created_at->diffForHumans() }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-slate-500 text-sm">Belum ada aktivitas.</p>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        Chart.defaults.color = '#94a3b8';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.05)';

        const severityData = @json($varianceDistribution);
        const statusData = @json($statusDistribution);
        const warehouseData = @json($varianceByWarehouse);

        if (Object.keys(severityData).length > 0) {
            new Chart(document.getElementById('severityChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(severityData).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
                    datasets: [{
                        data: Object.values(severityData),
                        backgroundColor: ['#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
                        borderWidth: 0,
                        hoverOffset: 8,
                    }]
                },
                options: { responsive: true, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } } } }
            });
        }

        if (Object.keys(statusData).length > 0) {
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData).map(s => s.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: ['#10B981', '#3B82F6', '#22C55E', '#EF4444', '#F59E0B'],
                        borderWidth: 0,
                        hoverOffset: 8,
                    }]
                },
                options: { responsive: true, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } } } }
            });
        }

        if (warehouseData.length > 0) {
            new Chart(document.getElementById('warehouseChart'), {
                type: 'bar',
                data: {
                    labels: warehouseData.map(w => w.warehouse),
                    datasets: [{
                        label: 'Total Abs. Variance',
                        data: warehouseData.map(w => w.total_variance),
                        backgroundColor: 'rgba(99, 102, 241, 0.6)',
                        borderRadius: 8,
                    }, {
                        label: 'Jumlah Entry',
                        data: warehouseData.map(w => w.entry_count),
                        backgroundColor: 'rgba(139, 92, 246, 0.4)',
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle' } } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.03)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
