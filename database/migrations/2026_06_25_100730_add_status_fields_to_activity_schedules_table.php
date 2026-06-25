<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('activity_schedules', 'status')) {
            Schema::table('activity_schedules', function (Blueprint $table): void {
                $table->string('status')->default('belum_mulai')->after('sort_order');
                $table->text('status_note')->nullable()->after('status');
                $table->foreignId('status_updated_by')->nullable()->after('status_note')->constrained('users')->nullOnDelete();
                $table->timestamp('status_updated_at')->nullable()->after('status_updated_by');

                $table->index(['lpj_id', 'status'], 'activity_schedules_lpj_status_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('activity_schedules', 'status')) {
            Schema::table('activity_schedules', function (Blueprint $table): void {
                $table->dropIndex('activity_schedules_lpj_status_idx');
                $table->dropConstrainedForeignId('status_updated_by');
                $table->dropColumn(['status', 'status_note', 'status_updated_at']);
            });
        }
    }
};