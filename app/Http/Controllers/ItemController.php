<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemUnitConversion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        $items = $query->with('unitConversions')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Items/Index', [
            'items' => $items,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Items/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required|string|unique:items,item_code',
            'name' => 'required|string|max:255',
            'jenis_barang' => 'required|string',
            'kategori_barang' => 'nullable|string',
            'unit' => 'required|string',
            'conversions' => 'nullable|array',
            'conversions.*.unit_name' => 'required|string',
            'conversions.*.conversion_qty' => 'required|numeric|min:0.0001',
        ]);

        $item = Item::create([
            'item_code' => $validated['item_code'],
            'name' => $validated['name'],
            'jenis_barang' => $validated['jenis_barang'],
            'kategori_barang' => $validated['kategori_barang'],
            'unit' => $validated['unit'],
        ]);

        if (!empty($validated['conversions'])) {
            foreach ($validated['conversions'] as $conv) {
                ItemUnitConversion::create([
                    'item_id' => $item->id,
                    'unit_name' => $conv['unit_name'],
                    'conversion_qty' => $conv['conversion_qty'],
                ]);
            }
        }

        return redirect()->route('items.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function edit(Item $item)
    {
        $item->load('unitConversions');
        return Inertia::render('Items/Edit', [
            'item' => $item
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'item_code' => ['required', 'string', Rule::unique('items')->ignore($item->id)],
            'name' => 'required|string|max:255',
            'jenis_barang' => 'required|string',
            'kategori_barang' => 'nullable|string',
            'unit' => 'required|string',
            'conversions' => 'nullable|array',
            'conversions.*.unit_name' => 'required|string',
            'conversions.*.conversion_qty' => 'required|numeric|min:0.0001',
        ]);

        $item->update([
            'item_code' => $validated['item_code'],
            'name' => $validated['name'],
            'jenis_barang' => $validated['jenis_barang'],
            'kategori_barang' => $validated['kategori_barang'],
            'unit' => $validated['unit'],
        ]);

        // Sync conversions: Delete all then recreate is simplest for now
        $item->unitConversions()->delete();
        
        if (!empty($validated['conversions'])) {
            foreach ($validated['conversions'] as $conv) {
                ItemUnitConversion::create([
                    'item_id' => $item->id,
                    'unit_name' => $conv['unit_name'],
                    'conversion_qty' => $conv['conversion_qty'],
                ]);
            }
        }

        return redirect()->route('items.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Barang berhasil dihapus.');
    }
}
