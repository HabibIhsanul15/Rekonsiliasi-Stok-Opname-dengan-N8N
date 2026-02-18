<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Sesi: {{ $opnameSession->session_code }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('opname-sessions.update', $opnameSession) }}">
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Kode Sesi</label>
                        <input type="text" value="{{ $opnameSession->session_code }}" disabled
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm text-sm">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Departemen</label>
                        <input type="text" value="{{ $opnameSession->warehouse->name }}" disabled
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm text-sm">
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @foreach(['draft', 'in_progress'] as $s)
                                <option value="{{ $s }}" {{ $opnameSession->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('notes', $opnameSession->notes) }}</textarea>
                    </div>

                    <div class="flex justify-between">
                        <form method="POST" action="{{ route('opname-sessions.destroy', $opnameSession) }}">
                            @csrf @method('DELETE')
                            @if($opnameSession->status === 'draft')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Hapus sesi ini?')">Hapus Sesi</button>
                            @endif
                        </form>
                        <div class="flex gap-3">
                            <a href="{{ route('opname-sessions.show', $opnameSession) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 transition">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
