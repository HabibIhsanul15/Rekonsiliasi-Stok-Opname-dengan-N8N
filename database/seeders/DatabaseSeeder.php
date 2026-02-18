<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Item;
use App\Models\OpnameSession;
use App\Models\OpnameEntry;
use App\Models\VarianceReview;
use App\Models\ActivityLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@stockopname.com'],
            [
                'name' => 'Admin Stock Opname',
                'password' => Hash::make('password123'),
            ]
        );

        // Departemen
        $dep1 = Warehouse::updateOrCreate(['code' => 'LOTTE'], ['name' => 'Departemen LOTTE', 'location' => 'LOTTE', 'is_active' => true]);
        $dep2 = Warehouse::updateOrCreate(['code' => 'BEI'], ['name' => 'Departemen BEI', 'location' => 'BEI', 'is_active' => true]);

        // Items for LOTTE
        $items = [
            ['item_code' => 'ITM-001', 'name' => 'Paper Bag', 'category' => 'Packaging', 'unit' => 'Pcs', 'system_stock' => 130, 'warehouse_id' => $dep1->id],
            ['item_code' => 'ITM-002', 'name' => 'Cup Paper', 'category' => 'Packaging', 'unit' => 'Pcs', 'system_stock' => 1111, 'warehouse_id' => $dep1->id],
            ['item_code' => 'ITM-003', 'name' => 'Bean Coffee', 'category' => 'Ingredients', 'unit' => 'Gram', 'system_stock' => 12060, 'warehouse_id' => $dep1->id],
            ['item_code' => 'ITM-006', 'name' => 'Plastic Lid', 'category' => 'Packaging', 'unit' => 'Pcs', 'system_stock' => 500, 'warehouse_id' => $dep1->id],
            ['item_code' => 'ITM-007', 'name' => 'Sugar Syrup', 'category' => 'Ingredients', 'unit' => 'ml', 'system_stock' => 5000, 'warehouse_id' => $dep1->id],
            // BEI Items
            ['item_code' => 'ITM-004', 'name' => 'Whipping Cream', 'category' => 'Ingredients', 'unit' => 'ml', 'system_stock' => 11000, 'warehouse_id' => $dep2->id],
            ['item_code' => 'ITM-005', 'name' => 'Cheese Blueberry', 'category' => 'Food', 'unit' => 'Pcs', 'system_stock' => 35, 'warehouse_id' => $dep2->id],
            ['item_code' => 'ITM-008', 'name' => 'Chocolate Powder', 'category' => 'Ingredients', 'unit' => 'Gram', 'system_stock' => 8000, 'warehouse_id' => $dep2->id],
            ['item_code' => 'ITM-009', 'name' => 'Matcha Powder', 'category' => 'Ingredients', 'unit' => 'Gram', 'system_stock' => 3000, 'warehouse_id' => $dep2->id],
            ['item_code' => 'ITM-010', 'name' => 'Napkin Tissue', 'category' => 'Supplies', 'unit' => 'Pack', 'system_stock' => 200, 'warehouse_id' => $dep2->id],
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(['item_code' => $item['item_code']], $item);
        }

        // ────────────────────────────────────────────
        // Dummy Opname Sessions + Entries + Reviews
        // (Simulating data that would come from N8N)
        // ────────────────────────────────────────────

        // Session 1: Completed - LOTTE
        $session1 = OpnameSession::updateOrCreate(
            ['session_code' => 'SO-20260218-001'],
            [
                'warehouse_id' => $dep1->id,
                'conducted_by' => $admin->id,
                'status' => 'completed',
                'started_at' => now()->subHours(5),
                'completed_at' => now()->subHours(3),
                'notes' => 'Opname rutin harian via N8N - LOTTE',
            ]
        );

        // Session 2: Completed - BEI  
        $session2 = OpnameSession::updateOrCreate(
            ['session_code' => 'SO-20260218-002'],
            [
                'warehouse_id' => $dep2->id,
                'conducted_by' => $admin->id,
                'status' => 'completed',
                'started_at' => now()->subHours(4),
                'completed_at' => now()->subHours(2),
                'notes' => 'Opname rutin harian via N8N - BEI',
            ]
        );

        // Entries for Session 1 (LOTTE) - simulating variance from Accurate
        $lotteItems = Item::where('warehouse_id', $dep1->id)->get();
        $lotteVariances = [
            'ITM-001' => 125,   // -5 (low)
            'ITM-002' => 1100,  // -11 (medium)
            'ITM-003' => 11800, // -260 (critical)
            'ITM-006' => 498,   // -2 (low, auto-approve)
            'ITM-007' => 4950,  // -50 (high)
        ];

        foreach ($lotteItems as $item) {
            $counted = $lotteVariances[$item->item_code] ?? $item->system_stock;
            $variance = $counted - $item->system_stock;
            $variancePct = $item->system_stock != 0 ? round($variance / $item->system_stock * 100, 2) : 0;

            $entry = OpnameEntry::updateOrCreate(
                ['opname_session_id' => $session1->id, 'item_id' => $item->id],
                [
                    'system_qty' => $item->system_stock,
                    'counted_qty' => $counted,
                    'variance' => $variance,
                    'variance_pct' => $variancePct,
                    'notes' => $variance == 0 ? 'Sesuai' : null,
                ]
            );

            // Create variance review
            $absVariance = abs($variance);
            $severity = $absVariance <= 2 ? 'low' : ($absVariance <= 5 ? 'medium' : ($absVariance <= 10 ? 'high' : 'critical'));
            $status = $absVariance <= 2 ? 'auto_approved' : ($absVariance <= 10 ? 'pending' : 'escalated');

            VarianceReview::updateOrCreate(
                ['opname_entry_id' => $entry->id],
                [
                    'severity' => $severity,
                    'status' => $status,
                    'auto_resolved' => $status === 'auto_approved',
                    'reviewed_at' => $status === 'auto_approved' ? now()->subHours(3) : null,
                    'reviewed_by' => $status === 'auto_approved' ? null : null,
                ]
            );
        }

        // Entries for Session 2 (BEI)  
        $beiItems = Item::where('warehouse_id', $dep2->id)->get();
        $beiVariances = [
            'ITM-004' => 10800, // -200 (critical)
            'ITM-005' => 33,    // -2 (low, auto)
            'ITM-008' => 7990,  // -10 (high)
            'ITM-009' => 3000,  // 0 (no variance)
            'ITM-010' => 195,   // -5 (medium)
        ];

        foreach ($beiItems as $item) {
            $counted = $beiVariances[$item->item_code] ?? $item->system_stock;
            $variance = $counted - $item->system_stock;
            $variancePct = $item->system_stock != 0 ? round($variance / $item->system_stock * 100, 2) : 0;

            $entry = OpnameEntry::updateOrCreate(
                ['opname_session_id' => $session2->id, 'item_id' => $item->id],
                [
                    'system_qty' => $item->system_stock,
                    'counted_qty' => $counted,
                    'variance' => $variance,
                    'variance_pct' => $variancePct,
                    'notes' => $variance == 0 ? 'Sesuai' : null,
                ]
            );

            $absVariance = abs($variance);
            $severity = $absVariance <= 2 ? 'low' : ($absVariance <= 5 ? 'medium' : ($absVariance <= 10 ? 'high' : 'critical'));
            $status = $absVariance <= 2 ? 'auto_approved' : ($absVariance <= 10 ? 'pending' : 'escalated');

            VarianceReview::updateOrCreate(
                ['opname_entry_id' => $entry->id],
                [
                    'severity' => $severity,
                    'status' => $status,
                    'auto_resolved' => $status === 'auto_approved',
                    'reviewed_at' => $status === 'auto_approved' ? now()->subHours(2) : null,
                ]
            );
        }

        // Activity logs
        ActivityLog::create([
            'loggable_type' => OpnameSession::class,
            'loggable_id' => $session1->id,
            'action' => 'webhook_received',
            'metadata' => ['imported' => 5, 'errors' => []],
            'created_at' => now()->subHours(3),
        ]);
        ActivityLog::create([
            'loggable_type' => OpnameSession::class,
            'loggable_id' => $session2->id,
            'action' => 'webhook_received',
            'metadata' => ['imported' => 5, 'errors' => []],
            'created_at' => now()->subHours(2),
        ]);

        // Dummy Import History (Simulating previous CSV uploads)
        \App\Models\OpnameImport::create([
            'opname_session_id' => $session1->id,
            'file_name' => 'lotte_stock_feb18.csv',
            'file_path' => 'imports/dummy_lotte.csv',
            'total_rows' => 5,
            'imported_rows' => 5,
            'failed_rows' => 0,
            'status' => 'completed',
            'uploaded_by' => $admin->id,
            'created_at' => now()->subHours(3),
        ]);

        \App\Models\OpnameImport::create([
            'opname_session_id' => $session2->id,
            'file_name' => 'bei_stock_feb18.xlsx',
            'file_path' => 'imports/dummy_bei.xlsx',
            'total_rows' => 5,
            'imported_rows' => 5,
            'failed_rows' => 0,
            'status' => 'completed',
            'uploaded_by' => $admin->id,
            'created_at' => now()->subHours(2),
        ]);

        $this->command->info('Seeded: users, warehouses, items, 2 sessions, 10 entries, variance reviews, activity logs, import history.');
    }
}
