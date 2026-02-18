<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-white tracking-tight">Tinjauan Selisih</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-4">
        {{-- Filters --}}
        <div class="glass-card p-4">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="form-label">Tingkat Keparahan</label>
                    <select name="severity" class="form-input-dark">
                        <option value="">Semua</option>
                        @foreach(['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'critical' => 'Kritis'] as $k => $v)
                            <option value="{{ $k }}" {{ request('severity') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input-dark">
                        <option value="">Semua</option>
                        @foreach(['auto_approved' => 'Otomatis Disetujui', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'escalated' => 'Eskalasi'] as $k => $v)
                            <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Departemen</label>
                    <select name="warehouse_id" class="form-input-dark">
                        <option value="">Semua</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('variances.index') }}" class="text-sm text-slate-500 hover:text-slate-300 transition">Atur Ulang</a>
            </form>
        </div>

        {{-- Table --}}
        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Departemen</th>
                            <th class="text-right">Selisih</th>
                            <th class="text-center">Keparahan</th>
                            <th class="text-center">Status</th>
                            <th>Peninjau</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                        <tr>
                            <td>
                                <span class="font-medium text-slate-200">{{ $review->opnameEntry->item->name ?? '-' }}</span>
                                <span class="block text-xs text-slate-500">{{ $review->opnameEntry->item->item_code ?? '-' }}</span>
                            </td>
                            <td class="text-slate-400">{{ $review->opnameEntry->session->warehouse->name ?? '-' }}</td>
                            <td class="text-right">
                                <span class="font-semibold {{ $review->opnameEntry->variance < 0 ? 'text-red-400' : 'text-emerald-400' }}">
                                    {{ $review->opnameEntry->variance > 0 ? '+' : '' }}{{ number_format($review->opnameEntry->variance, 2) }}
                                </span>
                                <span class="block text-xs text-slate-600">{{ number_format($review->opnameEntry->system_qty, 2) }} &rarr; {{ number_format($review->opnameEntry->counted_qty, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $review->severity === 'low' ? 'badge-green' : ($review->severity === 'medium' ? 'badge-yellow' : ($review->severity === 'high' ? 'badge-orange' : 'badge-red')) }}">
                                    {{ ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'critical' => 'Kritis'][$review->severity] ?? ucfirst($review->severity) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $review->status === 'auto_approved' || $review->status === 'approved' ? 'badge-green' : ($review->status === 'pending' ? 'badge-yellow' : ($review->status === 'escalated' ? 'badge-red' : 'badge-gray')) }}">
                                    {{ ['auto_approved' => 'Otomatis', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'escalated' => 'Eskalasi'][$review->status] ?? ucfirst(str_replace('_', ' ', $review->status)) }}
                                </span>
                            </td>
                            <td class="text-slate-400 text-sm">
                                {{ $review->reviewer->name ?? '-' }}
                                @if($review->reviewed_at)
                                    <span class="block text-xs text-slate-600">{{ $review->reviewed_at->format('d/m/Y H:i') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($review->canBeReviewed())
                                <div x-data="{ open: false }" class="relative inline-block">
                                    <button @click="open = !open" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium transition">Tinjau &#x25BE;</button>
                                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 z-10 mt-1 w-64 glass-card border border-white/10 rounded-xl p-3 shadow-2xl">
                                        <form method="POST" action="{{ route('variances.approve', $review) }}" class="mb-2">@csrf
                                            <textarea name="notes" rows="2" class="form-input-dark text-xs mb-2" placeholder="Catatan (opsional)"></textarea>
                                            <button type="submit" class="w-full btn-success text-xs py-1.5">&#x2713; Setujui</button>
                                        </form>
                                        <form method="POST" action="{{ route('variances.reject', $review) }}">@csrf
                                            <input type="hidden" name="notes" value="">
                                            <button type="submit" class="w-full btn-danger text-xs py-1.5">&#x2715; Tolak</button>
                                        </form>
                                    </div>
                                </div>
                                @else <span class="text-xs text-slate-600">-</span> @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-8 text-slate-500">Tidak ada data selisih ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-white/5">{{ $reviews->withQueryString()->links() }}</div>
        </div>
    </div>
</x-app-layout>
