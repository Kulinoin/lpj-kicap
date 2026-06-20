<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_documentations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category')->default('pelaksanaan');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('caption')->nullable();
            $table->boolean('include_in_report')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['lpj_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_documentations');
    }
};
