<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpjs', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->foreignId('lpj_type_id')->constrained('lpj_types')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('person_in_charge_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 40)->default('draft')->index();
            $table->string('completeness_status', 40)->default('belum_lengkap')->index();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('location')->nullable();
            $table->string('funding_source')->nullable();
            $table->string('assignment_letter_number')->nullable();
            $table->string('period_label')->nullable();
            $table->string('external_organizer')->nullable();
            $table->text('organization_role')->nullable();
            $table->decimal('total_funds_received', 15, 2)->default(0);
            $table->decimal('total_valid_expense', 15, 2)->default(0);
            $table->decimal('total_remaining_fund', 15, 2)->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpjs');
    }
};
