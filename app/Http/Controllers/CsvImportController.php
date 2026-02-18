<?php

namespace App\Http\Controllers;

use App\Models\OpnameSession;
use App\Models\OpnameImport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\CsvImportService;

class CsvImportController extends Controller
{
    protected $csvImportService;

    public function __construct(CsvImportService $csvImportService)
    {
        $this->csvImportService = $csvImportService;
    }

    public function index()
    {
        return Inertia::render('Import/Index', [
            'imports' => OpnameImport::with(['session', 'uploader'])->latest()->paginate(10),
        ]);
    }

    /**
     * Step 1: Upload file, store it temporarily, redirect to preview
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx',
            'opname_date' => 'required|date',
        ]);

        $file = $request->file('file');
        $storedPath = $file->store('imports', 'local');

        // Store upload info in session for the preview & process steps
        $request->session()->put('import_data', [
            'file_path' => $storedPath,
            'file_name' => $file->getClientOriginalName(),
            'opname_date' => $request->input('opname_date'),
        ]);

        return redirect()->route('import.preview');
    }

    /**
     * Step 2: Show preview page (GET request)
     */
    public function preview(Request $request)
    {
        $importData = $request->session()->get('import_data');

        if (!$importData) {
            return redirect()->route('import.index')->with('error', 'Tidak ada file untuk di-preview. Silakan upload ulang.');
        }

        $fullPath = storage_path("app/private/{$importData['file_path']}");
        
        try {
            // Create UploadedFile from stored path for preview
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $fullPath,
                $importData['file_name'],
                null,
                null,
                true
            );
            $preview = $this->csvImportService->preview($uploadedFile);
        } catch (\Exception $e) {
            $request->session()->forget('import_data');
            return redirect()->route('import.index')->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        return Inertia::render('Import/Preview', [
            'preview' => $preview,
            'fileName' => $importData['file_name'],
            'opnameDate' => $importData['opname_date'],
        ]);
    }

    /**
     * Step 3: Actually process the import after user confirmation
     */
    public function process(Request $request)
    {
        $importData = $request->session()->get('import_data');

        if (!$importData) {
            return redirect()->route('import.index')->with('error', 'Sesi import tidak ditemukan. Silakan upload ulang.');
        }

        $opnameDate = $importData['opname_date'];
        $filePath = $importData['file_path'];
        $fileName = $importData['file_name'];

        $session = OpnameSession::create([
            'session_code' => OpnameSession::generateCode(),
            'opname_date' => $opnameDate,
            'conducted_by' => auth()->id(),
            'status' => 'in_progress',
            'started_at' => now(),
            'notes' => 'Imported via CSV/Excel',
        ]);

        try {
            $fullPath = storage_path("app/private/{$filePath}");
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $fullPath,
                $fileName,
                null,
                null,
                true
            );

            $import = $this->csvImportService->import($uploadedFile, $session, auth()->id());

            // Clear session data
            $request->session()->forget('import_data');

            return redirect()->route('opname-sessions.show', $session->id)
                ->with('success', "Import berhasil! {$import->imported_rows} dari {$import->total_rows} data masuk.");

        } catch (\Exception $e) {
            $session->update(['status' => 'draft']);
            $request->session()->forget('import_data');
            return redirect()->route('import.index')->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }
}
