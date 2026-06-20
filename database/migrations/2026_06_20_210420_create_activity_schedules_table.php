<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->string('activity_name');
            $table->string('responsible_person')->nullable();
            $table->text('note')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['lpj_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_schedules');
    }
};
