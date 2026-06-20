<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('origin')->nullable();
            $table->string('participant_number')->nullable();
            $table->string('attendance_status')->default('hadir');
            $table->string('result_status')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['lpj_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_participants');
    }
};
