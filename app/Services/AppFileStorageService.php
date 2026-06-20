<?php

namespace App\Services;

use App\Models\StorageSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AppFileStorageService
{
    public function settings(): StorageSetting
    {
        return StorageSetting::active();
    }

    public function activeDisk(): string
    {
        $settings = $this->settings();

        if ($settings->isR2Configured()) {
            $this->configureR2($settings);

            return 'r2';
        }

        return 'public';
    }

    public function store(UploadedFile $file, string $directory, bool $convertImages = true): array
    {
        $settings = $this->settings();
        $disk = $this->activeDisk();
        $directory = $this->prefixDirectory($directory, $settings);

        if ($convertImages && $this->shouldConvertToWebp($file, $settings)) {
            $webp = $this->toWebp($file, $settings);

            if ($webp !== null) {
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $filename = Str::slug($filename) ?: 'image';
                $path = trim($directory, '/').'/'.$filename.'-'.Str::random(10).'.webp';

                Storage::disk($disk)->put($path, $webp, ['visibility' => 'public']);

                return [
                    'disk' => $disk,
                    'path' => $path,
                    'mime_type' => 'image/webp',
                    'size' => strlen($webp),
                    'converted_to_webp' => true,
                ];
            }
        }

        $path = $file->store($directory, $disk);

        return [
            'disk' => $disk,
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'converted_to_webp' => false,
        ];
    }

    public function delete(?string $path, ?string $disk = null): void
    {
        if (blank($path)) {
            return;
        }

        Storage::disk($this->normalizeDisk($disk))->delete($path);
    }

    public function url(?string $path, ?string $disk = null): ?string
    {
        if (blank($path)) {
            return null;
        }

        $disk = $this->normalizeDisk($disk);

        if ($disk === 'r2') {
            $this->configureR2($this->settings());
        }

        return Storage::disk($disk)->url($path);
    }

    public function path(?string $path, ?string $disk = null): ?string
    {
        if (blank($path)) {
            return null;
        }

        $disk = $this->normalizeDisk($disk);

        if ($disk !== 'public') {
            return null;
        }

        $absolute = Storage::disk('public')->path($path);

        return file_exists($absolute) ? $absolute : null;
    }

    public function webpEngineStatus(): array
    {
        return [
            'gd_loaded' => extension_loaded('gd'),
            'gd_webp' => function_exists('imagewebp'),
        ];
    }

    private function configureR2(StorageSetting $settings): void
    {
        config([
            'filesystems.disks.r2' => [
                'driver' => 's3',
                'key' => $settings->r2_access_key_id,
                'secret' => $settings->r2_secret_access_key,
                'region' => 'auto',
                'bucket' => $settings->r2_bucket,
                'url' => $settings->r2_public_url,
                'endpoint' => $settings->r2Endpoint(),
                'use_path_style_endpoint' => true,
                'visibility' => 'public',
                'throw' => false,
            ],
        ]);
    }

    private function normalizeDisk(?string $disk): string
    {
        $disk = $disk ?: 'public';

        if ($disk === 'r2') {
            $this->configureR2($this->settings());
        }

        return $disk;
    }

    private function prefixDirectory(string $directory, StorageSetting $settings): string
    {
        $directory = trim($directory, '/');
        $prefix = trim((string) $settings->root_prefix, '/');

        return $prefix !== '' ? $prefix.'/'.$directory : $directory;
    }

    private function shouldConvertToWebp(UploadedFile $file, StorageSetting $settings): bool
    {
        return $settings->auto_webp_enabled
            && function_exists('imagewebp')
            && in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true);
    }

    private function toWebp(UploadedFile $file, StorageSetting $settings): ?string
    {
        $source = match ($file->getMimeType()) {
            'image/jpeg' => @imagecreatefromjpeg($file->getRealPath()),
            'image/png' => @imagecreatefrompng($file->getRealPath()),
            'image/webp' => @imagecreatefromwebp($file->getRealPath()),
            default => false,
        };

        if (! $source) {
            return null;
        }

        imagepalettetotruecolor($source);
        imagealphablending($source, true);
        imagesavealpha($source, true);

        $image = $this->resizeIfNeeded($source, max(1, $settings->max_image_width));
        $quality = min(100, max(1, $settings->webp_quality));

        ob_start();
        $ok = imagewebp($image, null, $quality);
        $binary = ob_get_clean();

        if ($image !== $source) {
            imagedestroy($image);
        }

        imagedestroy($source);

        return $ok ? $binary : null;
    }

    private function resizeIfNeeded(\GdImage $source, int $maxWidth): \GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);

        if ($width <= $maxWidth) {
            return $source;
        }

        $newHeight = (int) round($height * ($maxWidth / $width));

        return imagescale($source, $maxWidth, max(1, $newHeight));
    }
}
