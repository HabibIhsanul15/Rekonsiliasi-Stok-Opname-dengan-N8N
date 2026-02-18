<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sesi Stock Opname</h2>
            <a href="{{ route('opname-sessions.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                + Buat Sesi Baru
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Filters --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-4">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Semua Status</option>
                            @foreach(['draft' => 'Draf', 'in_progress' => 'Sedang Berjalan', 'completed' => 'Selesai', 'closed' => 'Ditutup'] as $k => $v)
                                <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Departemen</label>
                        <select name="warehouse_id" class="mt-1 rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Semua Departemen</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700 transition">Filter</button>
                </form>
            </div>

            {{-- Sessions Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Sesi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departemen</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelaksana</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Entries</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($sessions as $session)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-indigo-600">
                                    <a href="{{ route('opname-sessions.show', $session) }}">{{ $session->session_code }}</a>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $session->warehouse->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $session->conductor->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-center">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full
                                        {{ $session->status === 'draft' ? 'bg-gray-100 text-gray-800' :
                                           ($session->status === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                                           ($session->status === 'completed' ? 'bg-green-100 text-green-800' :
                                           'bg-red-100 text-red-800')) }}">
                                        {{ ['draft' => 'Draf', 'in_progress' => 'Sedang Berjalan', 'completed' => 'Selesai', 'closed' => 'Ditutup'][$session->status] ?? ucfirst(str_replace('_', ' ', $session->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $session->entries_count }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $session->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3 text-sm text-center">
                                    <a href="{{ route('opname-sessions.show', $session) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">Detail</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada sesi opname.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t">
                    {{ $sessions->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
