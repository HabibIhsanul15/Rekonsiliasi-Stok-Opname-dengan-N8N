<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opname_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_code')->unique();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('conducted_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['draft', 'in_progress', 'completed', 'closed'])->default('draft');
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opname_sessions');
    }
};
