<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpj_narratives', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('section', 60);
            $table->text('content')->nullable();
            $table->foreignId('generated_from_template_id')->nullable()->constrained('narrative_templates')->nullOnDelete();
            $table->boolean('is_edited')->default(false)->index();
            $table->timestamps();

            $table->unique(['lpj_id', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpj_narratives');
    }
};
