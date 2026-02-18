<?php

namespace App\Services;

use App\Models\Item;
use App\Models\OpnameEntry;
use App\Models\OpnameImport;
use App\Models\OpnameSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Rap2hpoutre\FastExcel\FastExcel;

class CsvImportService
{
    /**
     * Import file (CSV/XLSX) into opname entries
     */
    public function import(UploadedFile $file, OpnameSession $session, int $userId): OpnameImport
    {
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('imports', 'local');

        $import = OpnameImport::create([
            'opname_session_id' => $session->id,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'status' => 'processing',
            'uploaded_by' => $userId,
        ]);

        try {
            $result = $this->processFile($filePath, $session);

            $import->update([
                'total_rows' => $result['total'],
                'imported_rows' => $result['imported'],
                'failed_rows' => $result['failed'],
                'status' => $result['failed'] > 0 ? 'completed' : 'completed',
                'errors' => $result['errors'] ?: null,
            ]);
        } catch (\Exception $e) {
            $import->update([
                'status' => 'failed',
                'errors' => [['row' => 0, 'message' => $e->getMessage()]],
            ]);
        }

        return $import;
    }

    /**
     * Parse and process the file using FastExcel
     */
    private function processFile(string $filePath, OpnameSession $session): array
    {
        $fullPath = storage_path("app/private/{$filePath}");
        
        $result = ['total' => 0, 'imported' => 0, 'failed' => 0, 'errors' => []];
        $rowNum = 0;
        $importedEntries = [];

        DB::beginTransaction();
        try {
            (new FastExcel)->import($fullPath, function ($line) use (&$result, &$rowNum, &$importedEntries, $session) {
                $rowNum++;
                $result['total']++;

                // Normalize keys to lowercase
                $line = array_change_key_case($line, CASE_LOWER);
                
                $itemCode = trim($line['item_code'] ?? '');
                $countedQty = trim($line['counted_qty'] ?? '');
                $notes = isset($line['notes']) ? trim($line['notes']) : null;

                // Validate
                if (empty($itemCode)) {
                    $result['failed']++;
                    $result['errors'][] = ['row' => $rowNum, 'message' => "item_code is empty"];
                    return;
                }

                if (!is_numeric($countedQty)) {
                    $result['failed']++;
                    $result['errors'][] = ['row' => $rowNum, 'message' => "counted_qty '{$countedQty}' is not a number"];
                    return;
                }

                // Find item globally by code (ignoring warehouse as per request)
                $item = Item::where('item_code', $itemCode)->first();

                if (!$item) {
                    $result['failed']++;
                    $result['errors'][] = ['row' => $rowNum, 'message' => "Item '{$itemCode}' not found in system (Sync Accurate first)"];
                    return;
                }

                // Create or update entry
                $entry = OpnameEntry::updateOrCreate(
                    [
                        'opname_session_id' => $session->id,
                        'item_id' => $item->id,
                    ],
                    [
                        'system_qty' => $item->system_stock,
                        'counted_qty' => (float) $countedQty,
                        'variance' => (float) $countedQty - (float) $item->system_stock,
                        'variance_pct' => $item->system_stock != 0
                            ? round(((float) $countedQty - (float) $item->system_stock) / (float) $item->system_stock * 100, 2)
                            : 0,
                        'notes' => $notes,
                    ]
                );

                $importedEntries[] = $entry;
                $result['imported']++;
            });

            // Auto-create variance reviews for all imported entries
            $varianceService = app(\App\Services\VarianceService::class);
            foreach ($importedEntries as $entry) {
                $varianceService->createOrUpdateReview($entry);
            }

            // Mark session as completed
            if ($result['imported'] > 0) {
                $session->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $result;
    }

    /**
     * Preview file contents (first N rows)
     */
    public function preview(UploadedFile $file, int $maxRows = 10): array
    {
        $path = $file->getRealPath();
        $rows = [];
        $headers = [];
        $total = 0;

        // Use import with a limit? FastExcel doesn't have a simple limit on import without iterating.
        // But for preview we can just manually iterate or use the collection approach for small preview.
        // FastExcel import returns a collection if no callback.
        
        // However, if we want to just read N rows without loading whole file if it's huge:
        // We can throw exception to stop, or just use the iterator manually if exposed?
        // FastExcel doesn't expose iterator easily on `import`.
        // But `(new FastExcel)->sheet(1)->import($path)` returns collection.
        // Let's blindly use import and take(10) - might be slow for huge files but acceptable for preview.
        // Optimally: use OpenSpout reader directly. But for simplicity let's rely on FastExcel.
        // Wait, better approach: use a callback and return false/exception? 
        // No, let's just use `(new FastExcel)->import($path)->take($maxRows)`. It reads everything first though.
        
        // Actually: `(new FastExcel)->configureCsv(';', '}', '\n', 'gbk')->import($file)`
        // Ideally we shouldn't read whole file for preview.
        // We can replicate the logic:
        
        $collection = (new FastExcel)->import($path);
        $total = $collection->count();
        $preview = $collection->take($maxRows);
        
        if ($preview->count() > 0) {
            $headers = array_keys($preview->first());
            $rows = $preview->toArray();
        }

        return ['headers' => $headers, 'rows' => $rows, 'total' => $total];
    }
}
