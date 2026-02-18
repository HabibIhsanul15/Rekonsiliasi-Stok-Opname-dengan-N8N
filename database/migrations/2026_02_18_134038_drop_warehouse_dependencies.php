<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove warehouse_id from items
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });

        // Remove warehouse_id from opname_sessions
        Schema::table('opname_sessions', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });

        // Drop warehouses table entirely
        Schema::dropIfExists('warehouses');
    }

    public function down(): void
    {
        // Recreate warehouses table
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('opname_sessions', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
