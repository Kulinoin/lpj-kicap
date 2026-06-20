<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpj_report_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnDelete();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version');
            $table->string('snapshot_number')->unique();
            $table->string('source', 40);
            $table->string('generated_by_role', 40)->nullable();
            $table->decimal('total_funds_received', 15, 2)->default(0);
            $table->decimal('total_valid_expense', 15, 2)->default(0);
            $table->decimal('total_remaining_fund', 15, 2)->default(0);
            $table->longText('snapshot_html');
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->unique(['lpj_id', 'version']);
            $table->index(['source', 'generated_at']);
            $table->index(['lpj_id', 'generated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpj_report_snapshots');
    }
};
