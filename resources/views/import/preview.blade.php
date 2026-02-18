<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pratinjau Import</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        Sesi: <strong>{{ $session->session_code }}</strong> &bull;
                        Departemen: <strong>{{ $session->warehouse->name }}</strong> &bull;
                        Total Baris: <strong>{{ $preview['total'] }}</strong>
                    </p>
                </div>

                {{-- Preview Table --}}
                @if(count($preview['rows']) > 0)
                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach($preview['headers'] as $header)
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($preview['rows'] as $row)
                            <tr>
                                @foreach($preview['headers'] as $header)
                                <td class="px-3 py-2 text-gray-700">{{ $row[$header] ?? '-' }}</td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($preview['total'] > count($preview['rows']))
                <p class="text-xs text-gray-400 mb-4">Menampilkan {{ count($preview['rows']) }} dari {{ $preview['total'] }} baris</p>
                @endif

                {{-- Confirm Import --}}
                <div class="flex gap-3">
                    <a href="{{ route('import.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <form method="POST" action="{{ route('import.process') }}">
                        @csrf
                        <input type="hidden" name="session_id" value="{{ $session->id }}">
                        <input type="hidden" name="temp_path" value="{{ $tempPath }}">
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700 transition"
                                onclick="return confirm('Lanjutkan import {{ $preview['total'] }} baris?')">
                            ✓ Konfirmasi Import
                        </button>
                    </form>
                </div>
                @else
                <p class="text-gray-500">Tidak ada data untuk diimport.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
