<x-filament-panels::page>
    <form wire:submit="save" class="max-w-3xl space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="mb-6 flex flex-col items-center text-center">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Profil Saya</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Username dan email dikunci. Data yang bisa diubah: foto profil, nama lengkap, WhatsApp, dan password.
                </p>
            </div>

            <div class="mb-6">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">Foto Profil</label>

                <div class="mb-3 flex flex-col items-center gap-3">
                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview foto profil" class="h-24 w-24 rounded-full object-cover">
                    @elseif ($profile_photo_path)
                        <img src="{{ app(\App\Services\AppFileStorageService::class)->url($profile_photo_path, auth()->user()?->profile_photo_disk) }}" alt="Foto profil" class="h-24 w-24 rounded-full object-cover">
                    @else
                        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-gray-200 text-2xl font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            {{ strtoupper(substr((string) $name, 0, 1)) }}
                        </div>
                    @endif

                    <div class="max-w-md text-sm text-gray-600 dark:text-gray-400">
                        Foto ini akan tampil di avatar pojok kanan atas setelah profil disimpan. Format gambar JPG/PNG/WebP, maksimal 2 MB.
                    </div>
                </div>

                <input type="file" wire:model="photo" accept="image/*" class="mx-auto block w-full max-w-sm text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-primary-700 dark:text-gray-300">
                @error('photo')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Username</label>
                    <input type="text" value="{{ $username }}" disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Email</label>
                    <input type="email" value="{{ $email }}" disabled class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Nama Lengkap</label>
                    <input type="text" wire:model="name" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    @error('name')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">WhatsApp</label>
                    <input type="text" wire:model="whatsapp" placeholder="Contoh: 081234567890" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    @error('whatsapp')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2 border-t border-gray-200 pt-5 dark:border-gray-700">
                    <h3 class="mb-1 text-sm font-semibold text-gray-950 dark:text-white">Ganti Password</h3>
                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Kosongkan jika tidak ingin mengganti password. Minimal 8 karakter.</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Password Baru</label>
                    <input type="password" wire:model="password" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    @error('password')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Konfirmasi Password Baru</label>
                    <input type="password" wire:model="password_confirmation" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                    Simpan Profil
                </button>
            </div>
        </div>
    </form>
</x-filament-panels::page>
