<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\OpnameEntry;
use App\Models\OpnameSession;
use Illuminate\Http\Request;

class OpnameEntryController extends Controller
{
    public function store(Request $request, OpnameSession $opnameSession)
    {
        if ($opnameSession->status === 'closed' || $opnameSession->status === 'completed') {
            return back()->with('error', 'Sesi sudah selesai, tidak bisa menambah data.');
        }

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'counted_qty' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $item = Item::findOrFail($validated['item_id']);

        $entry = OpnameEntry::updateOrCreate(
            [
                'opname_session_id' => $opnameSession->id,
                'item_id' => $item->id,
            ],
            [
                'system_qty' => $item->system_stock,
                'counted_qty' => $validated['counted_qty'],
                'notes' => $validated['notes'] ?? null,
            ]
        );

        // Auto-calculate variance
        $entry->calculateVariance();
        $entry->save();

        return back()->with('success', "Entry untuk {$item->name} berhasil disimpan.");
    }

    public function update(Request $request, OpnameSession $opnameSession, OpnameEntry $entry)
    {
        if ($opnameSession->status === 'closed' || $opnameSession->status === 'completed') {
            return back()->with('error', 'Sesi sudah selesai.');
        }

        $validated = $request->validate([
            'counted_qty' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $entry->update([
            'counted_qty' => $validated['counted_qty'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $entry->calculateVariance();
        $entry->save();

        return back()->with('success', 'Entry berhasil diupdate.');
    }

    public function destroy(OpnameSession $opnameSession, OpnameEntry $entry)
    {
        if ($opnameSession->status === 'closed' || $opnameSession->status === 'completed') {
            return back()->with('error', 'Sesi sudah selesai.');
        }

        $entry->delete();
        return back()->with('success', 'Entry berhasil dihapus.');
    }

    /**
     * Bulk store entries (AJAX-friendly)
     */
    public function bulkStore(Request $request, OpnameSession $opnameSession)
    {
        $validated = $request->validate([
            'entries' => 'required|array|min:1',
            'entries.*.item_id' => 'required|exists:items,id',
            'entries.*.counted_qty' => 'required|numeric|min:0',
            'entries.*.notes' => 'nullable|string|max:500',
        ]);

        $count = 0;
        foreach ($validated['entries'] as $entryData) {
            $item = Item::find($entryData['item_id']);
            if (!$item) continue;

            $entry = OpnameEntry::updateOrCreate(
                [
                    'opname_session_id' => $opnameSession->id,
                    'item_id' => $item->id,
                ],
                [
                    'system_qty' => $item->system_stock,
                    'counted_qty' => $entryData['counted_qty'],
                    'notes' => $entryData['notes'] ?? null,
                ]
            );

            $entry->calculateVariance();
            $entry->save();
            $count++;
        }

        return back()->with('success', "{$count} entries berhasil disimpan.");
    }
}
