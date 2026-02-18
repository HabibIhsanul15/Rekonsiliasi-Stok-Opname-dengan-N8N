<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Item;
use App\Models\ItemUnitConversion;
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
        // ────────────────────────────────────────────
        // Admin user
        // ────────────────────────────────────────────
        $admin = User::updateOrCreate(
            ['email' => 'admin@stockopname.com'],
            [
                'name' => 'Admin Stock Opname',
                'password' => Hash::make('password123'),
            ]
        );

        // ────────────────────────────────────────────
        // Items — sesuai data Accurate (tanpa system_stock)
        // Stok sistem ada di Accurate, bukan di website
        // ────────────────────────────────────────────
        $items = [
            [
                'item_code'      => '100001',
                'name'           => 'Paper Bag',
                'jenis_barang'   => 'Persediaan',
                'kategori_barang'=> 'Umum',
                'unit'           => 'PCS',
            ],
            [
                'item_code'      => '100002',
                'name'           => 'Cup Paper',
                'jenis_barang'   => 'Persediaan',
                'kategori_barang'=> 'Umum',
                'unit'           => 'PCS',
            ],
            [
                'item_code'      => '100003',
                'name'           => 'whipping cream',
                'jenis_barang'   => 'Persediaan',
                'kategori_barang'=> 'Umum',
                'unit'           => 'ml',
            ],
            [
                'item_code'      => '100004',
                'name'           => 'Bean Coffee',
                'jenis_barang'   => 'Persediaan',
                'kategori_barang'=> 'Umum',
                'unit'           => 'gr',
            ],
            [
                'item_code'      => '100005',
                'name'           => 'Cheese blueberry',
                'jenis_barang'   => 'Persediaan',
                'kategori_barang'=> 'Umum',
                'unit'           => 'PCS',
            ],
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(['item_code' => $item['item_code']], $item);
        }

        // ────────────────────────────────────────────
        // Konversi Satuan — sesuai Accurate
        // ────────────────────────────────────────────
        $conversions = [
            '100001' => [
                ['unit_name' => 'Pack', 'conversion_qty' => 25],
            ],
            '100003' => [
                ['unit_name' => 'liter', 'conversion_qty' => 1000],
                ['unit_name' => 'ctn', 'conversion_qty' => 12000],
            ],
            '100004' => [
                ['unit_name' => 'pouch', 'conversion_qty' => 545],
            ],
        ];

        foreach ($conversions as $itemCode => $units) {
            $item = Item::where('item_code', $itemCode)->first();
            if ($item) {
                foreach ($units as $unit) {
                    ItemUnitConversion::updateOrCreate(
                        ['item_id' => $item->id, 'unit_name' => $unit['unit_name']],
                        ['conversion_qty' => $unit['conversion_qty']]
                    );
                }
            }
        }

        // ────────────────────────────────────────────
        // Dummy Opname Sessions + Entries + Reviews
        // Simulasi: N8N sudah push hasil rekonsiliasi
        // system_qty diambil dari Accurate oleh N8N
        // ────────────────────────────────────────────

        // Session 1: Completed — Opname Pagi
        $session1 = OpnameSession::updateOrCreate(
            ['session_code' => 'SO-20260218-001'],
            [
                'conducted_by' => $admin->id,
                'status' => 'completed',
                'started_at' => now()->subHours(5),
                'completed_at' => now()->subHours(3),
                'notes' => 'Opname rutin pagi — hasil dari N8N',
            ]
        );

        // Session 2: Completed — Opname Sore
        $session2 = OpnameSession::updateOrCreate(
            ['session_code' => 'SO-20260218-002'],
            [
                'conducted_by' => $admin->id,
                'status' => 'completed',
                'started_at' => now()->subHours(4),
                'completed_at' => now()->subHours(2),
                'notes' => 'Opname rutin sore — hasil dari N8N',
            ]
        );

        // Entries Session 1 — system_qty dari Accurate (via N8N)
        $session1Data = [
            // [item_code, counted_qty, system_qty_dari_accurate]
            ['100001', 128, 130],     // Paper Bag: -2
            ['100002', 1100, 1111],   // Cup Paper: -11
            ['100004', 12000, 12060], // Bean Coffee: -60 gr
        ];

        foreach ($session1Data as [$code, $counted, $systemQty]) {
            $item = Item::where('item_code', $code)->first();
            if (!$item) continue;

            $variance = $counted - $systemQty;
            $variancePct = $systemQty != 0
                ? round($variance / $systemQty * 100, 2)
                : 0;

            $entry = OpnameEntry::updateOrCreate(
                ['opname_session_id' => $session1->id, 'item_id' => $item->id],
                [
                    'system_qty' => $systemQty,
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
                    'reviewed_at' => $status === 'auto_approved' ? now()->subHours(3) : null,
                ]
            );
        }

        // Entries Session 2 — system_qty dari Accurate (via N8N)
        $session2Data = [
            ['100003', 10800, 11000], // whipping cream: -200 ml
            ['100005', 35, 35],       // Cheese blueberry: 0 (sesuai)
        ];

        foreach ($session2Data as [$code, $counted, $systemQty]) {
            $item = Item::where('item_code', $code)->first();
            if (!$item) continue;

            $variance = $counted - $systemQty;
            $variancePct = $systemQty != 0
                ? round($variance / $systemQty * 100, 2)
                : 0;

            $entry = OpnameEntry::updateOrCreate(
                ['opname_session_id' => $session2->id, 'item_id' => $item->id],
                [
                    'system_qty' => $systemQty,
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
            'metadata' => ['imported' => 3, 'errors' => []],
            'created_at' => now()->subHours(3),
        ]);
        ActivityLog::create([
            'loggable_type' => OpnameSession::class,
            'loggable_id' => $session2->id,
            'action' => 'webhook_received',
            'metadata' => ['imported' => 2, 'errors' => []],
            'created_at' => now()->subHours(2),
        ]);

        // Dummy Import History
        \App\Models\OpnameImport::create([
            'opname_session_id' => $session1->id,
            'file_name' => 'stock_feb18_pagi.csv',
            'file_path' => 'imports/dummy_1.csv',
            'total_rows' => 3,
            'imported_rows' => 3,
            'failed_rows' => 0,
            'status' => 'completed',
            'uploaded_by' => $admin->id,
            'created_at' => now()->subHours(3),
        ]);

        \App\Models\OpnameImport::create([
            'opname_session_id' => $session2->id,
            'file_name' => 'stock_feb18_sore.xlsx',
            'file_path' => 'imports/dummy_2.xlsx',
            'total_rows' => 2,
            'imported_rows' => 2,
            'failed_rows' => 0,
            'status' => 'completed',
            'uploaded_by' => $admin->id,
            'created_at' => now()->subHours(2),
        ]);

        $this->command->info('✅ Seeded: admin, 5 items (Accurate), konversi satuan, 2 sessions, 5 entries, reviews, logs.');
    }
}
