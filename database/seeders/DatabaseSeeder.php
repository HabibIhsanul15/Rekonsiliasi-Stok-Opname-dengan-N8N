<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@stockopname.com'],
            [
                'name' => 'Admin Stock Opname',
                'password' => Hash::make('password123'),
            ]
        );

        // Departemen
        $dep1 = Warehouse::updateOrCreate(['code' => 'LOTTE'], ['name' => 'Departemen LOTTE', 'location' => 'LOTTE']);
        $dep2 = Warehouse::updateOrCreate(['code' => 'BEI'], ['name' => 'Departemen BEI', 'location' => 'BEI']);

        // Sample items for each departemen
        $items = [
            // LOTTE Items
            ['item_code' => 'ITM-001', 'name' => 'Paper Bag', 'category' => 'Packaging', 'unit' => 'Pcs', 'system_stock' => 130, 'warehouse_id' => $dep1->id],
            ['item_code' => 'ITM-002', 'name' => 'Cup Paper', 'category' => 'Packaging', 'unit' => 'Pcs', 'system_stock' => 1111, 'warehouse_id' => $dep1->id],
            ['item_code' => 'ITM-003', 'name' => 'Bean Coffee', 'category' => 'Ingredients', 'unit' => 'Gram', 'system_stock' => 12060, 'warehouse_id' => $dep1->id],
            
            // BEI Items
            ['item_code' => 'ITM-004', 'name' => 'Whipping Cream', 'category' => 'Ingredients', 'unit' => 'ml', 'system_stock' => 11000, 'warehouse_id' => $dep2->id],
            ['item_code' => 'ITM-005', 'name' => 'Cheese Blueberry', 'category' => 'Food', 'unit' => 'Pcs', 'system_stock' => 35, 'warehouse_id' => $dep2->id],
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(['item_code' => $item['item_code']], $item);
        }
    }
}
