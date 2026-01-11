<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            if (!Schema::hasColumn('devices', 'enabled')) {
                $table->boolean('enabled')->default(true)->after('api_token');
                $table->index('enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            if (Schema::hasColumn('devices', 'enabled')) {
                $table->dropIndex(['enabled']);
                $table->dropColumn('enabled');
            }
        });
    }
};
