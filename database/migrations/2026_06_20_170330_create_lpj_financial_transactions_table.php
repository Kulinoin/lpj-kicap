<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpj_financial_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('source_type', 40);
            $table->string('status', 40)->index();
            $table->string('category');
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->date('spent_at');
            $table->string('proof_path')->nullable();
            $table->text('no_proof_reason')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['lpj_id', 'user_id', 'spent_at']);
            $table->index(['lpj_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpj_financial_transactions');
    }
};
