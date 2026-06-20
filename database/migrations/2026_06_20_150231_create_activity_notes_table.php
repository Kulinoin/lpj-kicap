<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('type', 60)->index();
            $table->text('content')->nullable();
            $table->boolean('include_in_report')->default(false);
            $table->timestamps();

            $table->unique(['lpj_id', 'user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_notes');
    }
};
