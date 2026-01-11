<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('work_logs', 'source')) {
                $table->string('source')->default('terminal')->after('clock_out');
                $table->index('source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            if (Schema::hasColumn('work_logs', 'source')) {
                $table->dropIndex(['source']);
                $table->dropColumn('source');
            }
        });
    }
};
