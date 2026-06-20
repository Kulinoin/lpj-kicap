<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_committees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('role');
            $table->text('task')->nullable();
            $table->string('contact')->nullable();
            $table->timestamps();

            $table->index(['lpj_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_committees');
    }
};
