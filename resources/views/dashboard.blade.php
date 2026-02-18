<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                    <div class="text-sm font-medium text-gray-500">Total Sesi</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalSessions) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                    <div class="text-sm font-medium text-gray-500">Sesi Aktif</div>
                    <div class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($activeSessions) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                    <div class="text-sm font-medium text-gray-500">Total Entry</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalEntries) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                    <div class="text-sm font-medium text-gray-500">Variance Ditemukan</div>
                    <div class="text-2xl font-bold text-orange-500 mt-1">{{ number_format($totalVariances) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                    <div class="text-sm font-medium text-gray-500">Menunggu Review</div>
                    <div class="text-2xl font-bold text-red-600 mt-1">{{ number_format($pendingReviews) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                    <div class="text-sm font-medium text-gray-500">Otomatis Disetujui</div>
                    <div class="text-2xl font-bold text-green-600 mt-1">{{ number_format($autoApproved) }}</div>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Variance Distribution Pie --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Keparahan (Severity)</h3>
                    <canvas id="severityChart" height="250"></canvas>
                </div>

                {{-- Status Distribution Pie --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Review</h3>
                    <canvas id="statusChart" height="250"></canvas>
                </div>
            </div>

            {{-- Variance by Warehouse --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Selisih per Departemen</h3>
                <canvas id="warehouseChart" height="120"></canvas>
            </div>

            {{-- Top Discrepancies --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">10 Item Selisih Terbesar</h3>
                @if($topDiscrepancies->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departemen</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sistem</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Fisik</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($topDiscrepancies as $entry)
                            <tr>
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-medium text-gray-900">{{ $entry->item->name ?? '-' }}</div>
                                    <div class="text-gray-500 text-xs">{{ $entry->item->item_code ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $entry->session->warehouse->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($entry->system_qty, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($entry->counted_qty, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold {{ $entry->variance < 0 ? 'text-red-600' : ($entry->variance > 0 ? 'text-green-600' : 'text-gray-500') }}">
                                    {{ $entry->variance > 0 ? '+' : '' }}{{ number_format($entry->variance, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-gray-500 text-sm">Belum ada data selisih.</p>
                @endif
            </div>

            {{-- Recent Activity --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h3>
                @if($recentActivity->count() > 0)
                <div class="space-y-3">
                    @foreach($recentActivity as $log)
                    <div class="flex items-center text-sm">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ $log->action === 'approved' ? 'bg-green-100 text-green-800' :
                               ($log->action === 'rejected' ? 'bg-red-100 text-red-800' :
                               ($log->action === 'escalated' ? 'bg-yellow-100 text-yellow-800' :
                               'bg-blue-100 text-blue-800')) }}">
                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                        </span>
                        <span class="ml-3 text-gray-600">{{ $log->user->name ?? 'System' }}</span>
                        <span class="ml-auto text-gray-400 text-xs">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-sm">Belum ada aktivitas.</p>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const severityData = @json($varianceDistribution);
        const statusData = @json($statusDistribution);
        const warehouseData = @json($varianceByWarehouse);

        // Severity Pie
        if (Object.keys(severityData).length > 0) {
            new Chart(document.getElementById('severityChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(severityData).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
                    datasets: [{
                        data: Object.values(severityData),
                        backgroundColor: ['#10B981', '#F59E0B', '#EF4444', '#7C3AED'],
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        }

        // Status Pie
        if (Object.keys(statusData).length > 0) {
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData).map(s => s.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: ['#10B981', '#3B82F6', '#22C55E', '#EF4444', '#F59E0B'],
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        }

        // Warehouse Bar
        if (warehouseData.length > 0) {
            new Chart(document.getElementById('warehouseChart'), {
                type: 'bar',
                data: {
                    labels: warehouseData.map(w => w.warehouse),
                    datasets: [{
                        label: 'Total Abs. Variance',
                        data: warehouseData.map(w => w.total_variance),
                        backgroundColor: '#6366F1',
                    }, {
                        label: 'Jumlah Entry',
                        data: warehouseData.map(w => w.entry_count),
                        backgroundColor: '#A5B4FC',
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'top' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
