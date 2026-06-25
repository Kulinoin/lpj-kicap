<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_participant_test_results')) {
            Schema::create('activity_participant_test_results', function (Blueprint $table): void {
                $table->id();

                $table->unsignedBigInteger('lpj_id');
                $table->unsignedBigInteger('activity_participant_id');
                $table->unsignedBigInteger('activity_selection_stage_id');
                $table->unsignedBigInteger('activity_selection_test_id');

                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();

                $table->string('status')->default('belum_tes');
                $table->string('result_value')->nullable();
                $table->text('note')->nullable();

                $table->string('file_path')->nullable();
                $table->string('file_disk')->nullable();
                $table->string('original_name')->nullable();
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();

                $table->timestamp('assessed_at')->nullable();
                $table->timestamps();

                $table->foreign('lpj_id', 'aptr_lpj_fk')
                    ->references('id')->on('lpjs')
                    ->cascadeOnDelete();

                $table->foreign('activity_participant_id', 'aptr_participant_fk')
                    ->references('id')->on('activity_participants')
                    ->cascadeOnDelete();

                $table->foreign('activity_selection_stage_id', 'aptr_stage_fk')
                    ->references('id')->on('activity_selection_stages')
                    ->cascadeOnDelete();

                $table->foreign('activity_selection_test_id', 'aptr_test_fk')
                    ->references('id')->on('activity_selection_tests')
                    ->cascadeOnDelete();

                $table->foreign('created_by', 'aptr_created_by_fk')
                    ->references('id')->on('users')
                    ->nullOnDelete();

                $table->foreign('updated_by', 'aptr_updated_by_fk')
                    ->references('id')->on('users')
                    ->nullOnDelete();

                $table->unique(['activity_participant_id', 'activity_selection_test_id'], 'aptr_participant_test_unique');
                $table->index(['lpj_id', 'status'], 'aptr_lpj_status_idx');
                $table->index(['lpj_id', 'activity_selection_stage_id'], 'aptr_lpj_stage_idx');
                $table->index(['lpj_id', 'activity_selection_test_id'], 'aptr_lpj_test_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_participant_test_results');
    }
};
