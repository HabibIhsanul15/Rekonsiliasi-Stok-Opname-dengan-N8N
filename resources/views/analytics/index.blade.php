<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Analitik & Laporan</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Summary Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <div class="text-sm font-medium text-gray-500">Otomatis Disetujui</div>
                    <div class="text-2xl font-bold text-green-600 mt-1">{{ number_format($statusSummary['auto_approved'] ?? 0) }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <div class="text-sm font-medium text-gray-500">Menunggu Review</div>
                    <div class="text-2xl font-bold text-yellow-600 mt-1">{{ number_format($statusSummary['pending'] ?? 0) }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <div class="text-sm font-medium text-gray-500">Eskalasi</div>
                    <div class="text-2xl font-bold text-red-600 mt-1">{{ number_format($statusSummary['escalated'] ?? 0) }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <div class="text-sm font-medium text-gray-500">Rata-rata Waktu Proses</div>
                    <div class="text-2xl font-bold text-indigo-600 mt-1">{{ $avgTurnaround ? number_format($avgTurnaround, 1) . 'h' : 'N/A' }}</div>
                </div>
            </div>

            {{-- Charts --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Severity Distribution --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Tingkat Keparahan</h3>
                    <canvas id="severityPie" height="250"></canvas>
                </div>

                {{-- Warehouse Comparison --}}
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Selisih per Departemen</h3>
                    <canvas id="warehouseBar" height="250"></canvas>
                </div>
            </div>

            {{-- Shrinkage Trend --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Tren Penyusutan (Bulanan)</h3>
                <canvas id="shrinkageLine" height="120"></canvas>
            </div>

            {{-- Top Discrepancy Items --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">20 Item Selisih Terbesar</h3>
                @if($topItems->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departemen</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sistem</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Fisik</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Selisih</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($topItems as $i => $entry)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $entry->item->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $entry->item->item_code ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $entry->session->warehouse->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($entry->system_qty, 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($entry->counted_qty, 2) }}</td>
                                <td class="px-4 py-3 text-right font-semibold {{ $entry->variance < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $entry->variance > 0 ? '+' : '' }}{{ number_format($entry->variance, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right {{ abs($entry->variance_pct) > 10 ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                    {{ number_format($entry->variance_pct, 1) }}%
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-gray-500 text-sm">Belum ada data.</p>
                @endif
            </div>

            {{-- Warehouse Detail Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail Departemen</h3>
                @if($warehouseData->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departemen</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Entry</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ada Selisih</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Selisih Abs</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Rata-rata Selisih Abs</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($warehouseData as $wd)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $wd->name }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($wd->total_entries) }}</td>
                                <td class="px-4 py-3 text-right text-orange-600 font-medium">{{ number_format($wd->variance_entries) }}</td>
                                <td class="px-4 py-3 text-right text-red-600 font-semibold">{{ number_format($wd->total_abs_variance, 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($wd->avg_abs_variance, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-gray-500 text-sm">Belum ada data.</p>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const severityData = @json($severityData);
        const warehouseData = @json($warehouseData);
        const shrinkageData = @json($shrinkageTrend);

        // Severity Pie
        if (Object.keys(severityData).length > 0) {
            new Chart(document.getElementById('severityPie'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(severityData).map(s => {
                        const map = {'low': 'Rendah', 'medium': 'Sedang', 'high': 'Tinggi', 'critical': 'Kritis'};
                        return map[s] || s.charAt(0).toUpperCase() + s.slice(1);
                    }),
                    datasets: [{
                        data: Object.values(severityData),
                        backgroundColor: ['#10B981', '#F59E0B', '#F97316', '#EF4444'],
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        }

        // Warehouse Bar
        if (warehouseData.length > 0) {
            new Chart(document.getElementById('warehouseBar'), {
                type: 'bar',
                data: {
                    labels: warehouseData.map(w => w.name),
                    datasets: [{
                        label: 'Total Selisih Absolut',
                        data: warehouseData.map(w => parseFloat(w.total_abs_variance)),
                        backgroundColor: '#6366F1',
                    }, {
                        label: 'Rata-rata Selisih Absolut',
                        data: warehouseData.map(w => parseFloat(w.avg_abs_variance)),
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

        // Shrinkage Trend Line
        if (shrinkageData.length > 0) {
            new Chart(document.getElementById('shrinkageLine'), {
                type: 'line',
                data: {
                    labels: shrinkageData.map(s => s.month),
                    datasets: [{
                        label: 'Total Selisih (Bersih)',
                        data: shrinkageData.map(s => parseFloat(s.total_variance)),
                        borderColor: '#EF4444',
                        backgroundColor: 'rgba(239,68,68,0.1)',
                        fill: true,
                        tension: 0.3,
                    }, {
                        label: 'Total Selisih Absolut',
                        data: shrinkageData.map(s => parseFloat(s.total_abs_variance)),
                        borderColor: '#6366F1',
                        backgroundColor: 'rgba(99,102,241,0.1)',
                        fill: true,
                        tension: 0.3,
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
