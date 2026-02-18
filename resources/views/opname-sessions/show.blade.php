    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white tracking-tight">Detail Import: {{ $opnameSession->session_code }}</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs text-slate-500 bg-white/5 px-2 py-0.5 rounded">{{ $opnameSession->warehouse->name }}</span>
                    <span class="text-xs text-slate-500">&bull;</span>
                    <span class="text-xs text-slate-500">{{ $opnameSession->conductor->name ?? 'System (N8N)' }}</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="badge {{ $opnameSession->status === 'draft' ? 'badge-gray' : ($opnameSession->status === 'in_progress' ? 'badge-blue' : ($opnameSession->status === 'completed' ? 'badge-green' : 'badge-red')) }}">
                    {{ ['draft' => 'Draf', 'in_progress' => 'Sedang Berjalan', 'completed' => 'Selesai', 'closed' => 'Ditutup'][$opnameSession->status] ?? ucfirst(str_replace('_', ' ', $opnameSession->status)) }}
                </span>
                @if($opnameSession->completed_at)
                    <span class="text-xs text-slate-600">Selesai: {{ $opnameSession->completed_at->format('d M Y H:i') }}</span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">

        @if($opnameSession->notes)
        <div class="glass-card p-4 border-amber-500/20 flex items-start gap-3">
            <span class="text-amber-400 text-lg">&#x1F4CC;</span>
            <p class="text-sm text-amber-300/80"><strong class="text-amber-300">Catatan:</strong> {{ $opnameSession->notes }}</p>
        </div>
        @endif

        {{-- Session Summary --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 animate-fade-in-up">
            <div class="stat-card">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Item</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $opnameSession->entries->count() }}</p>
            </div>
            <div class="stat-card">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Ada Selisih</p>
                <p class="text-2xl font-bold text-orange-400 mt-1">{{ $opnameSession->entries->where('variance', '!=', 0)->count() }}</p>
            </div>
            <div class="stat-card">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Auto Approved</p>
                <p class="text-2xl font-bold text-emerald-400 mt-1">{{ $opnameSession->entries->filter(fn($e) => $e->varianceReview && $e->varianceReview->status === 'auto_approved')->count() }}</p>
            </div>
            <div class="stat-card">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Perlu Review</p>
                <p class="text-2xl font-bold text-red-400 mt-1">{{ $opnameSession->entries->filter(fn($e) => $e->varianceReview && in_array($e->varianceReview->status, ['pending', 'escalated']))->count() }}</p>
            </div>
        </div>

        {{-- Entries Table --}}
        <div class="glass-card overflow-hidden animate-fade-in-up" style="animation-delay: 0.1s;">
            <div class="p-4 border-b border-white/5">
                <h3 class="text-base font-semibold text-white">Daftar Item ({{ $opnameSession->entries->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-right">Stok Sistem</th>
                            <th class="text-right">Fisik</th>
                            <th class="text-right">Selisih</th>
                            <th class="text-right">%</th>
                            <th class="text-center">Tinjauan</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($opnameSession->entries as $entry)
                        <tr>
                            <td>
                                <span class="font-medium text-slate-200">{{ $entry->item->name ?? '-' }}</span>
                                <span class="block text-xs text-slate-500">{{ $entry->item->item_code ?? '-' }}</span>
                            </td>
                            <td class="text-right text-slate-300">{{ number_format($entry->system_qty, 2) }}</td>
                            <td class="text-right text-slate-200 font-medium">{{ number_format($entry->counted_qty, 2) }}</td>
                            <td class="text-right font-semibold {{ $entry->variance < 0 ? 'text-red-400' : ($entry->variance > 0 ? 'text-emerald-400' : 'text-slate-500') }}">
                                {{ $entry->variance > 0 ? '+' : '' }}{{ number_format($entry->variance, 2) }}
                            </td>
                            <td class="text-right {{ abs($entry->variance_pct) > 10 ? 'text-red-400' : 'text-slate-500' }}">{{ number_format($entry->variance_pct, 1) }}%</td>
                            <td class="text-center">
                                @if($entry->varianceReview)
                                    <span class="badge {{ $entry->varianceReview->status === 'auto_approved' || $entry->varianceReview->status === 'approved' ? 'badge-green' : ($entry->varianceReview->status === 'pending' ? 'badge-yellow' : ($entry->varianceReview->status === 'escalated' ? 'badge-red' : 'badge-gray')) }}">
                                        {{ ['auto_approved' => 'Otomatis', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'escalated' => 'Eskalasi'][$entry->varianceReview->status] ?? ucfirst(str_replace('_', ' ', $entry->varianceReview->status)) }}
                                    </span>
                                @else <span class="text-xs text-slate-600">-</span> @endif
                            </td>
                            <td class="text-slate-500 max-w-[150px] truncate">{{ $entry->notes ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-8 text-slate-500">Belum ada data dari N8N.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
