<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('narrative_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lpj_type_id')->constrained('lpj_types')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('section', 60);
            $table->string('title');
            $table->text('content');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['lpj_type_id', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('narrative_templates');
    }
};
