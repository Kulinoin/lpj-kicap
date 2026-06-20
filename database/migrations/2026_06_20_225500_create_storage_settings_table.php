<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 40)->default('local');
            $table->string('r2_account_id')->nullable();
            $table->string('r2_access_key_id')->nullable();
            $table->text('r2_secret_access_key')->nullable();
            $table->string('r2_bucket')->nullable();
            $table->string('r2_endpoint')->nullable();
            $table->string('r2_public_url')->nullable();
            $table->string('root_prefix')->nullable();
            $table->boolean('auto_webp_enabled')->default(true);
            $table->unsignedTinyInteger('webp_quality')->default(78);
            $table->unsignedInteger('max_image_width')->default(1800);
            $table->timestamps();
        });

        Schema::table('activity_documentations', function (Blueprint $table): void {
            $table->string('file_disk', 40)->default('public')->after('file_path');
        });

        Schema::table('activity_attachments', function (Blueprint $table): void {
            $table->string('file_disk', 40)->default('public')->after('file_path');
        });

        Schema::table('lpj_financial_transactions', function (Blueprint $table): void {
            $table->string('proof_disk', 40)->nullable()->after('proof_path');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('profile_photo_disk', 40)->nullable()->after('profile_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'profile_photo_disk')) {
                $table->dropColumn('profile_photo_disk');
            }
        });

        Schema::table('lpj_financial_transactions', function (Blueprint $table): void {
            if (Schema::hasColumn('lpj_financial_transactions', 'proof_disk')) {
                $table->dropColumn('proof_disk');
            }
        });

        Schema::table('activity_attachments', function (Blueprint $table): void {
            if (Schema::hasColumn('activity_attachments', 'file_disk')) {
                $table->dropColumn('file_disk');
            }
        });

        Schema::table('activity_documentations', function (Blueprint $table): void {
            if (Schema::hasColumn('activity_documentations', 'file_disk')) {
                $table->dropColumn('file_disk');
            }
        });

        Schema::dropIfExists('storage_settings');
    }
};
