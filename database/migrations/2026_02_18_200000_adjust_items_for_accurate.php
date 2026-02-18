<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('jenis_barang')->default('Persediaan')->after('category');
            $table->string('kategori_barang')->nullable()->after('jenis_barang');
        });

        // Migrate existing category data to kategori_barang
        \DB::table('items')->whereNotNull('category')->update([
            'kategori_barang' => \DB::raw('category'),
        ]);

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
        });

        \DB::table('items')->whereNotNull('kategori_barang')->update([
            'category' => \DB::raw('kategori_barang'),
        ]);

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['jenis_barang', 'kategori_barang']);
        });
    }
};
