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

                // Create or update entry — only store counted_qty
                // N8N will later pull this data and compare with Accurate's stock
                $entry = OpnameEntry::updateOrCreate(
                    [
                        'opname_session_id' => $session->id,
                        'item_id' => $item->id,
                    ],
                    [
                        'system_qty' => 0, // akan diisi oleh N8N dari Accurate
                        'counted_qty' => (float) $countedQty,
                        'variance' => 0,   // akan dihitung oleh N8N
                        'variance_pct' => 0, // akan dihitung oleh N8N
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
     * 
     * OpenSpout requires the correct file extension to determine reader type.
     * PHP temp uploads use .tmp extension, so we copy to a temp file with the original extension.
     */
    public function preview(UploadedFile $file, int $maxRows = 10): array
    {
        $rows = [];
        $headers = [];
        $total = 0;

        // Copy to temp file with correct extension so OpenSpout can detect format
        $extension = $file->getClientOriginalExtension() ?: 'csv';
        $tempFile = tempnam(sys_get_temp_dir(), 'opname_') . '.' . $extension;
        copy($file->getRealPath(), $tempFile);
        
        try {
            $collection = (new FastExcel)->import($tempFile);
            $total = $collection->count();
            $preview = $collection->take($maxRows);
            
            if ($preview->count() > 0) {
                $headers = array_keys($preview->first());
                $rows = array_values($preview->toArray());
            }
        } finally {
            // Always clean up temp file
            @unlink($tempFile);
        }

        return ['headers' => $headers, 'rows' => $rows, 'total' => $total];
    }
}
