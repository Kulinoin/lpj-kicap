<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_selection_stages')) {
            Schema::create('activity_selection_stages', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnDelete();
                $table->string('name');
                $table->string('slug');
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_elimination')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['lpj_id', 'slug']);
                $table->index(['lpj_id', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_selection_stages');
    }
};