<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpj_user_balances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['lpj_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpj_user_balances');
    }
};
