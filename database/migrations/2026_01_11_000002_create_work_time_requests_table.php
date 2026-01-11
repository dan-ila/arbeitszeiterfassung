<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_time_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_log_id')->nullable()->constrained('work_logs')->nullOnDelete();

            $table->string('type');   // add | edit
            $table->string('status')->default('pending'); // pending | approved | rejected

            $table->dateTime('requested_clock_in');
            $table->dateTime('requested_clock_out');

            $table->text('reason')->nullable();
            $table->text('admin_comment')->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'type']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_time_requests');
    }
};
