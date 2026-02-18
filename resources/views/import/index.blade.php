<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Import Data</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Upload File (CSV / Excel)</h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Format yang diterima: <code class="bg-gray-100 px-1 rounded">.csv</code>, <code class="bg-gray-100 px-1 rounded">.xlsx</code>, <code class="bg-gray-100 px-1 rounded">.xls</code>.<br>
                        Kolom wajib: <code class="bg-gray-100 px-1 rounded">item_code</code>, <code class="bg-gray-100 px-1 rounded">counted_qty</code>. Opsional: <code class="bg-gray-100 px-1 rounded">notes</code>.
                    </p>

                    <form method="POST" action="{{ route('import.upload') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="session_id" class="block text-sm font-medium text-gray-700">Sesi Opname <span class="text-red-500">*</span></label>
                            <select name="session_id" id="session_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">-- Pilih Sesi --</option>
                                @foreach($sessions as $session)
                                    <option value="{{ $session->id }}" {{ request('session_id') == $session->id ? 'selected' : '' }}>
                                        {{ $session->session_code }} - {{ $session->warehouse->name ?? '' }} ({{ ['draft' => 'Draf', 'in_progress' => 'Sedang Berjalan', 'completed' => 'Selesai', 'closed' => 'Ditutup'][$session->status] ?? $session->status }})
                                    </option>
                                @endforeach
                            </select>
                            @error('session_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="csv_file" class="block text-sm font-medium text-gray-700">File Import <span class="text-red-500">*</span></label>
                            <input type="file" name="csv_file" id="csv_file" accept=".csv,.txt,.xlsx,.xls" required
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('csv_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-md text-sm font-medium hover:bg-indigo-700 transition">
                            📁 Unggah & Pratinjau
                        </button>
                    </form>
                </div>

                {{-- Example CSV format --}}
                <div class="border-t pt-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Contoh Format:</h4>
                    <div class="bg-gray-50 rounded-md p-3 font-mono text-xs text-gray-600 overflow-x-auto">
                        item_code,counted_qty,notes<br>
                        ITM-001,100,sesuai<br>
                        ITM-002,47,kurang 3 dari sistem<br>
                        ITM-003,250,
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
