<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpj_fund_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source_name');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('received_at');
            $table->timestamps();

            $table->index(['lpj_id', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpj_fund_receipts');
    }
};
