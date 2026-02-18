<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opname_entry_id')->unique()->constrained()->onDelete('cascade');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->enum('status', ['auto_approved', 'pending', 'approved', 'rejected', 'escalated'])->default('pending');
            $table->boolean('auto_resolved')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('reviewed_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->boolean('adjustment_pushed')->default(false);
            $table->datetime('pushed_at')->nullable();
            $table->json('push_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variance_reviews');
    }
};
