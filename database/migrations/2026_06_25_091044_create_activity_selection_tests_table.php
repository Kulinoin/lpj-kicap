<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_selection_tests')) {
            Schema::create('activity_selection_tests', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnDelete();
                $table->foreignId('activity_selection_stage_id')->constrained('activity_selection_stages')->cascadeOnDelete();

                $table->string('name');
                $table->string('slug');
                $table->unsignedInteger('sort_order')->default(0);

                $table->boolean('is_required')->default(true);
                $table->boolean('is_elimination')->default(true);

                $table->boolean('requires_reason_on_fail')->default(true);
                $table->boolean('requires_attachment_on_fail')->default(false);

                $table->boolean('allows_pass_with_note')->default(false);
                $table->boolean('requires_attachment_on_pass_with_note')->default(false);

                $table->string('result_label')->nullable();
                $table->string('note_label')->nullable();

                $table->timestamps();

                $table->unique(['lpj_id', 'activity_selection_stage_id', 'slug'], 'activity_selection_tests_unique_slug');
                $table->index(['lpj_id', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_selection_tests');
    }
};