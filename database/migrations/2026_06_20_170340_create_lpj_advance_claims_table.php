<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpj_advance_claims', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('financial_transaction_id')->unique()->constrained('lpj_financial_transactions')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('status', 40)->default('diajukan')->index();
            $table->text('admin_note')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['lpj_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpj_advance_claims');
    }
};
