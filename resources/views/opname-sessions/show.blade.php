<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $opnameSession->session_code }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $opnameSession->warehouse->name }} &bull; {{ $opnameSession->conductor->name }}</p>
            </div>
            <div class="flex gap-2">
                @if($opnameSession->status === 'draft')
                    <form method="POST" action="{{ route('opname-sessions.start', $opnameSession) }}">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                            ▶ Mulai Opname
                        </button>
                    </form>
                @endif
                @if(in_array($opnameSession->status, ['in_progress', 'completed']))
                    <form method="POST" action="{{ route('opname-sessions.process', $opnameSession) }}">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
                                onclick="return confirm('Proses selisih untuk semua item?')">
                            ⚙ Proses Selisih
                        </button>
                    </form>
                @endif
                <span class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-lg
                    {{ $opnameSession->status === 'draft' ? 'bg-gray-100 text-gray-800' :
                       ($opnameSession->status === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                       ($opnameSession->status === 'completed' ? 'bg-green-100 text-green-800' :
                       'bg-red-100 text-red-800')) }}">
                    {{ ['draft' => 'Draf', 'in_progress' => 'Sedang Berjalan', 'completed' => 'Selesai', 'closed' => 'Ditutup'][$opnameSession->status] ?? ucfirst(str_replace('_', ' ', $opnameSession->status)) }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Session Info --}}
            @if($opnameSession->notes)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-yellow-800"><strong>Catatan:</strong> {{ $opnameSession->notes }}</p>
            </div>
            @endif

            {{-- Add Entry Form --}}
            @if(in_array($opnameSession->status, ['draft', 'in_progress']))
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Tambah Data</h3>
                <form method="POST" action="{{ route('entries.store', $opnameSession) }}" class="flex flex-wrap gap-4 items-end">
                    @csrf
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700">Item</label>
                        <select name="item_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Pilih Item --</option>
                            @foreach($warehouseItems as $item)
                                <option value="{{ $item->id }}">{{ $item->item_code }} - {{ $item->name }} (Stok: {{ number_format($item->system_stock, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-40">
                        <label class="block text-sm font-medium text-gray-700">Qty Fisik</label>
                        <input type="number" name="counted_qty" step="0.01" min="0" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-sm font-medium text-gray-700">Catatan</label>
                        <input type="text" name="notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                               placeholder="Opsional...">
                    </div>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                        + Simpan
                    </button>
                </form>
            </div>
            @endif

            {{-- Entries Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Item ({{ $opnameSession->entries->count() }})</h3>
                    @if(in_array($opnameSession->status, ['draft', 'in_progress']))
                    <a href="{{ route('import.index', ['session_id' => $opnameSession->id]) }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                        📁 Import Data
                    </a>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stok Sistem</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Fisik</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Selisih</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">%</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tinjauan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catatan</th>
                                @if(in_array($opnameSession->status, ['draft', 'in_progress']))
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($opnameSession->entries as $entry)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-medium text-gray-900">{{ $entry->item->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $entry->item->item_code ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ number_format($entry->system_qty, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 font-medium">{{ number_format($entry->counted_qty, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold {{ $entry->variance < 0 ? 'text-red-600' : ($entry->variance > 0 ? 'text-green-600' : 'text-gray-400') }}">
                                    {{ $entry->variance > 0 ? '+' : '' }}{{ number_format($entry->variance, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right {{ abs($entry->variance_pct) > 10 ? 'text-red-600' : 'text-gray-500' }}">
                                    {{ number_format($entry->variance_pct, 1) }}%
                                </td>
                                <td class="px-4 py-3 text-sm text-center">
                                    @if($entry->varianceReview)
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full
                                            {{ $entry->varianceReview->status === 'auto_approved' ? 'bg-green-100 text-green-800' :
                                               ($entry->varianceReview->status === 'approved' ? 'bg-green-100 text-green-800' :
                                               ($entry->varianceReview->status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                               ($entry->varianceReview->status === 'escalated' ? 'bg-red-100 text-red-800' :
                                               'bg-gray-100 text-gray-800'))) }}">
                                            {{ ['auto_approved' => 'Otomatis Disetujui', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'escalated' => 'Eskalasi'][$entry->varianceReview->status] ?? ucfirst(str_replace('_', ' ', $entry->varianceReview->status)) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 max-w-[150px] truncate">{{ $entry->notes ?? '-' }}</td>
                                @if(in_array($opnameSession->status, ['draft', 'in_progress']))
                                <td class="px-4 py-3 text-sm text-center">
                                    <form method="POST" action="{{ route('entries.destroy', [$opnameSession, $entry]) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs" onclick="return confirm('Hapus entry ini?')">Hapus</button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">Belum ada data. Tambahkan di atas atau import file.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
