<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('activity_participants', 'whatsapp')) {
            Schema::table('activity_participants', function (Blueprint $table): void {
                $table->string('whatsapp')->nullable()->after('participant_number');

                $table->string('photo_path')->nullable()->after('whatsapp');
                $table->string('photo_disk')->nullable()->after('photo_path');
                $table->string('photo_original_name')->nullable()->after('photo_disk');
                $table->string('photo_mime_type')->nullable()->after('photo_original_name');
                $table->unsignedBigInteger('photo_size')->nullable()->after('photo_mime_type');

                $table->string('selection_registration_status')->default('belum_registrasi')->after('result_status');
                $table->string('selection_status')->default('belum_mulai')->after('selection_registration_status');

                $table->foreignId('registered_by')->nullable()->after('selection_status')->constrained('users')->nullOnDelete();
                $table->timestamp('registered_at')->nullable()->after('registered_by');

                $table->foreignId('current_selection_stage_id')->nullable()->after('registered_at')->constrained('activity_selection_stages')->nullOnDelete();
                $table->foreignId('eliminated_selection_stage_id')->nullable()->after('current_selection_stage_id')->constrained('activity_selection_stages')->nullOnDelete();
                $table->foreignId('eliminated_selection_test_id')->nullable()->after('eliminated_selection_stage_id')->constrained('activity_selection_tests')->nullOnDelete();

                $table->index(['lpj_id', 'selection_registration_status'], 'participants_registration_status_idx');
                $table->index(['lpj_id', 'selection_status'], 'participants_selection_status_idx');
                $table->index(['lpj_id', 'participant_number'], 'participants_number_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('activity_participants', 'whatsapp')) {
            Schema::table('activity_participants', function (Blueprint $table): void {
                $table->dropIndex('participants_registration_status_idx');
                $table->dropIndex('participants_selection_status_idx');
                $table->dropIndex('participants_number_idx');

                $table->dropConstrainedForeignId('registered_by');
                $table->dropConstrainedForeignId('current_selection_stage_id');
                $table->dropConstrainedForeignId('eliminated_selection_stage_id');
                $table->dropConstrainedForeignId('eliminated_selection_test_id');

                $table->dropColumn([
                    'whatsapp',
                    'photo_path',
                    'photo_disk',
                    'photo_original_name',
                    'photo_mime_type',
                    'photo_size',
                    'selection_registration_status',
                    'selection_status',
                    'registered_at',
                ]);
            });
        }
    }
};