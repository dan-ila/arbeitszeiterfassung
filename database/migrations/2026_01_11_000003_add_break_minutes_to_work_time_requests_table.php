<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_time_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('work_time_requests', 'requested_break_minutes')) {
                $table->unsignedInteger('requested_break_minutes')->default(0)->after('requested_clock_out');
            }
        });
    }

    public function down(): void
    {
        Schema::table('work_time_requests', function (Blueprint $table) {
            if (Schema::hasColumn('work_time_requests', 'requested_break_minutes')) {
                $table->dropColumn('requested_break_minutes');
            }
        });
    }
};
