<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-white tracking-tight">Edit Sesi: {{ $opnameSession->session_code }}</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="glass-card p-8">
            <form method="POST" action="{{ route('opname-sessions.update', $opnameSession) }}">
                @csrf @method('PUT')
                <div class="mb-5">
                    <label class="form-label">Kode Sesi</label>
                    <input type="text" value="{{ $opnameSession->session_code }}" disabled class="form-input-dark opacity-50 cursor-not-allowed">
                </div>
                <div class="mb-5">
                    <label class="form-label">Departemen</label>
                    <input type="text" value="{{ $opnameSession->warehouse->name }}" disabled class="form-input-dark opacity-50 cursor-not-allowed">
                </div>
                <div class="mb-5">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-input-dark">
                        @foreach(['draft', 'in_progress'] as $s)
                            <option value="{{ $s }}" {{ $opnameSession->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-6">
                    <label for="notes" class="form-label">Catatan</label>
                    <textarea name="notes" id="notes" rows="3" class="form-input-dark">{{ old('notes', $opnameSession->notes) }}</textarea>
                </div>
                <div class="flex justify-between">
                    <form method="POST" action="{{ route('opname-sessions.destroy', $opnameSession) }}">@csrf @method('DELETE')
                        @if($opnameSession->status === 'draft')
                        <button type="submit" class="text-red-400 hover:text-red-300 text-sm transition" onclick="return confirm('Hapus sesi ini?')">Hapus Sesi</button>
                        @endif
                    </form>
                    <div class="flex gap-3">
                        <a href="{{ route('opname-sessions.show', $opnameSession) }}" class="btn-ghost">Batal</a>
                        <button type="submit" class="btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
