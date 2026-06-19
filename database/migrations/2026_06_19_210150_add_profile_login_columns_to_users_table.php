<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }

            if (! Schema::hasColumn('users', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'profile_photo_path')) {
                $table->string('profile_photo_path')->nullable()->after('whatsapp');
            }
        });

        User::query()
            ->whereNull('username')
            ->orWhere('username', '')
            ->get()
            ->each(function (User $user): void {
                $base = Str::slug(Str::before($user->email, '@')) ?: 'user-'.$user->id;
                $username = $base;
                $counter = 1;

                while (User::query()->where('username', $username)->whereKeyNot($user->id)->exists()) {
                    $username = $base.'-'.$counter;
                    $counter++;
                }

                $user->forceFill(['username' => $username])->save();
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            foreach (['profile_photo_path', 'whatsapp', 'username'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
