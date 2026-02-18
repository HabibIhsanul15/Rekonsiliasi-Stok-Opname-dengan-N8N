<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-white tracking-tight">Buat Sesi Opname Baru</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="glass-card p-8">
            <form method="POST" action="{{ route('opname-sessions.store') }}">
                @csrf
                <div class="mb-5">
                    <label for="warehouse_id" class="form-label">Departemen <span class="text-red-400">*</span></label>
                    <select name="warehouse_id" id="warehouse_id" required class="form-input-dark">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->code }} - {{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="mb-6">
                    <label for="notes" class="form-label">Catatan</label>
                    <textarea name="notes" id="notes" rows="3" class="form-input-dark" placeholder="Catatan opsional tentang sesi ini...">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('opname-sessions.index') }}" class="btn-ghost">Batal</a>
                    <button type="submit" class="btn-primary">Buat Sesi</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
