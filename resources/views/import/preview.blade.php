<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-white tracking-tight">Pratinjau Import</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto">
        <div class="glass-card p-6">
            <div class="mb-4 flex flex-wrap gap-4 text-sm text-slate-400">
                <span>Sesi: <strong class="text-white">{{ $session->session_code }}</strong></span>
                <span>&middot;</span>
                <span>Departemen: <strong class="text-white">{{ $session->warehouse->name }}</strong></span>
                <span>&middot;</span>
                <span>Total Baris: <strong class="text-indigo-400">{{ $preview['total'] }}</strong></span>
            </div>

            @if(count($preview['rows']) > 0)
            <div class="overflow-x-auto mb-6">
                <table class="data-table text-sm">
                    <thead>
                        <tr>
                            @foreach($preview['headers'] as $header)
                            <th>{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($preview['rows'] as $row)
                        <tr>
                            @foreach($preview['headers'] as $header)
                            <td class="text-slate-300">{{ $row[$header] ?? '-' }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($preview['total'] > count($preview['rows']))
            <p class="text-xs text-slate-600 mb-4">Menampilkan {{ count($preview['rows']) }} dari {{ $preview['total'] }} baris</p>
            @endif

            <div class="flex gap-3">
                <a href="{{ route('import.index') }}" class="btn-ghost">Batal</a>
                <form method="POST" action="{{ route('import.process') }}">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $session->id }}">
                    <input type="hidden" name="temp_path" value="{{ $tempPath }}">
                    <button type="submit" class="btn-success" onclick="return confirm('Lanjutkan import {{ $preview['total'] }} baris?')">&#x2713; Konfirmasi Import</button>
                </form>
            </div>
            @else
            <p class="text-slate-500">Tidak ada data untuk diimport.</p>
            @endif
        </div>
    </div>
</x-app-layout>
