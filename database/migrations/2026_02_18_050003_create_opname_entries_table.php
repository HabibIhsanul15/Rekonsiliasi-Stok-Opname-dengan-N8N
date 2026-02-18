<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opname_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opname_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('system_qty', 15, 2)->default(0);
            $table->decimal('counted_qty', 15, 2)->default(0);
            $table->decimal('variance', 15, 2)->default(0);
            $table->decimal('variance_pct', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['opname_session_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opname_entries');
    }
};
