<?php

namespace App\Http\Controllers;

use App\Models\OpnameImport;
use App\Models\OpnameSession;
use App\Models\Warehouse;
use App\Services\CsvImportService;
use Illuminate\Http\Request;

class CsvImportController extends Controller
{
    public function __construct(private CsvImportService $csvImportService) {}

    public function index()
    {
        $history = OpnameImport::with(['session.warehouse', 'uploader'])
            ->latest()
            ->paginate(10);

        return view('import.index', compact('history'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240', // 10MB
        ]);

        $file = $request->file('csv_file');
        
        // Auto-create session for this import
        // Default to first active warehouse since user requested no selection
        $warehouse = Warehouse::active()->first();
        if (!$warehouse) {
             return back()->with('error', 'Tidak ada gudang aktif ditemukan. Harap buat gudang terlebih dahulu.');
        }

        $sessionCode = 'IMP-' . now()->format('YmdHis');
        $session = OpnameSession::create([
            'session_code' => $sessionCode,
            'warehouse_id' => $warehouse->id,
            'conducted_by' => auth()->id(),
            'status' => 'in_progress',
            'started_at' => now(),
            'notes' => 'Imported via CSV Upload',
        ]);

        $preview = $this->csvImportService->preview($file);

        // Store the file temporarily for the process step
        $tempPath = $file->store('temp', 'local');

        return view('import.preview', compact('preview', 'session', 'tempPath'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:opname_sessions,id',
            'temp_path' => 'required|string',
        ]);

        $session = OpnameSession::findOrFail($request->session_id);
        $fullPath = storage_path("app/private/{$request->temp_path}");

        if (!file_exists($fullPath)) {
            return redirect()->route('import.index')
                ->with('error', 'File tidak ditemukan. Silakan upload ulang.');
        }

        // Create UploadedFile from temp path
        $file = new \Illuminate\Http\UploadedFile($fullPath, basename($fullPath));
        $import = $this->csvImportService->import($file, $session, auth()->id());

        // Clean temp
        @unlink($fullPath);

        if ($import->status === 'failed') {
            return redirect()->route('import.index')
                ->with('error', 'Import gagal: ' . ($import->errors[0]['message'] ?? 'Kesalahan tidak diketahui'));
        }

        return redirect()->route('opname-sessions.show', $session)
            ->with('success', "Import selesai: {$import->imported_rows} berhasil, {$import->failed_rows} gagal dari {$import->total_rows} total. Variance otomatis diproses.");
    }
}
