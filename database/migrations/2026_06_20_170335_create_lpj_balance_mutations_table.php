<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpj_balance_mutations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('related_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('financial_transaction_id')->nullable()->constrained('lpj_financial_transactions')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 40);
            $table->string('direction', 10);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->string('transfer_reference')->nullable()->index();
            $table->text('note')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['lpj_id', 'user_id', 'occurred_at']);
            $table->index(['type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpj_balance_mutations');
    }
};
