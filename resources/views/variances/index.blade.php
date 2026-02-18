<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tinjauan Selisih (Variance Review)</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Filters --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-4">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tingkat Keparahan</label>
                        <select name="severity" class="mt-1 rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Semua</option>
                            @foreach(['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'critical' => 'Kritis'] as $k => $v)
                                <option value="{{ $k }}" {{ request('severity') === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Semua</option>
                            @foreach(['auto_approved' => 'Otomatis Disetujui', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'escalated' => 'Eskalasi'] as $k => $v)
                                <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Departemen</label>
                        <select name="warehouse_id" class="mt-1 rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Semua</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700 transition">Filter</button>
                    <a href="{{ route('variances.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Atur Ulang</a>
                </form>
            </div>

            {{-- Reviews Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departemen</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Selisih</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Keparahan</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peninjau</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($reviews as $review)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-medium text-gray-900">{{ $review->opnameEntry->item->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $review->opnameEntry->item->item_code ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $review->opnameEntry->session->warehouse->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold {{ $review->opnameEntry->variance < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $review->opnameEntry->variance > 0 ? '+' : '' }}{{ number_format($review->opnameEntry->variance, 2) }}
                                    <div class="text-xs text-gray-400">Sistem: {{ number_format($review->opnameEntry->system_qty, 2) }} → Fisik: {{ number_format($review->opnameEntry->counted_qty, 2) }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-center">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full
                                        {{ $review->severity === 'low' ? 'bg-green-100 text-green-800' :
                                           ($review->severity === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                                           ($review->severity === 'high' ? 'bg-orange-100 text-orange-800' :
                                           'bg-red-100 text-red-800')) }}">
                                        {{ ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'critical' => 'Kritis'][$review->severity] ?? ucfirst($review->severity) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-center">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full
                                        {{ $review->status === 'auto_approved' || $review->status === 'approved' ? 'bg-green-100 text-green-800' :
                                           ($review->status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                           ($review->status === 'escalated' ? 'bg-red-100 text-red-800' :
                                           'bg-gray-100 text-gray-800')) }}">
                                        {{ ['auto_approved' => 'Otomatis Disetujui', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'escalated' => 'Eskalasi'][$review->status] ?? ucfirst(str_replace('_', ' ', $review->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $review->reviewer->name ?? '-' }}
                                    @if($review->reviewed_at)
                                        <div class="text-xs text-gray-400">{{ $review->reviewed_at->format('d/m/Y H:i') }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-center">
                                    @if($review->canBeReviewed())
                                    <div x-data="{ open: false }" class="relative inline-block">
                                        <button @click="open = !open" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                            Tinjau ▾
                                        </button>
                                        <div x-show="open" @click.away="open = false" class="absolute right-0 z-10 mt-1 w-64 bg-white rounded-lg shadow-lg border p-3">
                                            <form method="POST" action="{{ route('variances.approve', $review) }}" class="mb-2">
                                                @csrf
                                                <textarea name="notes" rows="2" class="w-full text-xs rounded border-gray-300 mb-1" placeholder="Catatan (opsional)"></textarea>
                                                <button type="submit" class="w-full bg-green-600 text-white text-xs py-1.5 rounded hover:bg-green-700 transition">✓ Setujui</button>
                                            </form>
                                            <form method="POST" action="{{ route('variances.reject', $review) }}">
                                                @csrf
                                                <input type="hidden" name="notes" value="">
                                                <button type="submit" class="w-full bg-red-600 text-white text-xs py-1.5 rounded hover:bg-red-700 transition">✕ Tolak</button>
                                            </form>
                                        </div>
                                    </div>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">Tidak ada data selisih ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t">
                    {{ $reviews->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
