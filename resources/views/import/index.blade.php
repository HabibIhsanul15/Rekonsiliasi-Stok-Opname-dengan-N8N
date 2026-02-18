<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-white tracking-tight">Import Data Opname</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Upload Form --}}
        <div class="glass-card p-8">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-white mb-2 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Upload File (CSV / Excel)
                </h3>
                <p class="text-sm text-slate-500 mb-4">
                    Upload file hasil opname untuk diproses sistem. Sesi opname akan dibuat otomatis.<br>
                    Format: <code class="bg-white/5 border border-white/10 px-1.5 py-0.5 rounded text-indigo-300 text-xs">.csv</code>
                    <code class="bg-white/5 border border-white/10 px-1.5 py-0.5 rounded text-indigo-300 text-xs">.xlsx</code>
                    <code class="bg-white/5 border border-white/10 px-1.5 py-0.5 rounded text-indigo-300 text-xs">.xls</code><br>
                    Kolom wajib: <code class="text-emerald-400 text-xs">item_code</code>, <code class="text-emerald-400 text-xs">counted_qty</code>. Opsional: <code class="text-slate-400 text-xs">notes</code>.
                </p>

                <form method="POST" action="{{ route('import.upload') }}" enctype="multipart/form-data">
                    @csrf
                    {{-- Session selection removed as per request --}}
                    
                    <div class="mb-6">
                        <label for="csv_file" class="form-label">File Import <span class="text-red-400">*</span></label>
                        <div class="mt-1 relative">
                            <input type="file" name="csv_file" id="csv_file" accept=".csv,.txt,.xlsx,.xls" required
                                   class="block w-full text-sm text-slate-400 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-500/15 file:text-indigo-400 hover:file:bg-indigo-500/25 file:transition file:cursor-pointer">
                        </div>
                        @error('csv_file')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="w-full btn-primary py-3 text-base">&#x1F4C1; Unggah &amp; Proses</button>
                </form>
            </div>

            <div class="border-t border-white/5 pt-4">
                <h4 class="text-sm font-medium text-slate-400 mb-2">Contoh Format:</h4>
                <div class="rounded-xl p-4 font-mono text-xs text-slate-500 overflow-x-auto" style="background: rgba(0,0,0,0.3);">
                    item_code,counted_qty,notes<br>
                    ITM-001,100,sesuai<br>
                    ITM-002,47,kurang 3 dari sistem<br>
                    ITM-003,250,
                </div>
            </div>
        </div>

        {{-- Import History --}}
        <div class="glass-card overflow-hidden animate-fade-in-up" style="animation-delay: 0.1s;">
            <div class="p-4 border-b border-white/5">
                <h3 class="text-base font-semibold text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span> Riwayat Import &amp; Opname
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kode Import</th>
                            <th>Total Baris</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Berhasil</th>
                            <th class="text-right">Gagal</th>
                            <th class="text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $import)
                        <tr>
                            <td class="text-slate-400 text-xs">{{ $import->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <span class="font-medium text-slate-200 block">{{ $import->session->session_code ?? '-' }}</span>
                                <span class="text-xs text-slate-500">{{ $import->file_name }}</span>
                            </td>
                            <td class="text-slate-300">{{ number_format($import->total_rows) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $import->status === 'completed' ? 'badge-green' : ($import->status === 'processing' ? 'badge-blue' : 'badge-red') }}">
                                    {{ ['completed' => 'Selesai', 'processing' => 'Proses', 'failed' => 'Gagal'][$import->status] ?? ucfirst($import->status) }}
                                </span>
                            </td>
                            <td class="text-right text-emerald-400">{{ number_format($import->imported_rows) }}</td>
                            <td class="text-right {{ $import->failed_rows > 0 ? 'text-red-400 font-semibold' : 'text-slate-500' }}">{{ number_format($import->failed_rows) }}</td>
                            <td class="text-center">
                                @if($import->session)
                                    <a href="{{ route('opname-sessions.show', $import->session) }}" class="btn-secondary py-1 px-3 text-xs">Lihat Hasil</a>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-8 text-slate-500">Belum ada riwayat import.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($history->hasPages())
            <div class="px-4 py-3 border-t border-white/5">{{ $history->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
