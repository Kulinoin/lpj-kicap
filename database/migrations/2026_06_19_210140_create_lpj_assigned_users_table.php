<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpj_assigned_users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lpj_id')->constrained('lpjs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_label')->nullable();
            $table->boolean('can_input_transaction')->default(true);
            $table->boolean('can_upload_documentation')->default(true);
            $table->boolean('can_edit_activity_data')->default(true);
            $table->timestamps();

            $table->unique(['lpj_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpj_assigned_users');
    }
};
