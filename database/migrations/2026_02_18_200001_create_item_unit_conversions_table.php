<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->string('unit_name');        // e.g. Pack, pouch, liter, ctn
            $table->decimal('conversion_qty', 15, 4); // 1 unit_name = conversion_qty × satuan dasar
            $table->timestamps();

            $table->unique(['item_id', 'unit_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_unit_conversions');
    }
};
