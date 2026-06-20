<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('description')->nullable();
            $table->boolean('include_in_report')->default(false);
            $table->timestamps();

            $table->index(['lpj_id', 'title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_attachments');
    }
};
