<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opname_sessions', function (Blueprint $table) {
            $table->date('opname_date')->default(now()->toDateString())->after('session_code');
        });
    }

    public function down(): void
    {
        Schema::table('opname_sessions', function (Blueprint $table) {
            $table->dropColumn('opname_date');
        });
    }
};
