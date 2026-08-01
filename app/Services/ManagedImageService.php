<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ManagedImageService
{
    public function store(UploadedFile $file, string $directory, int $maxWidth = 1920, int $maxHeight = 1920): string
    {
        $disk = Storage::disk('public');
        $directory = trim($directory, '/');
        if (function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
            $source = @imagecreatefromstring($file->getContent());
            if ($source !== false) {
                $width = imagesx($source);
                $height = imagesy($source);
                $ratio = min($maxWidth / $width, $maxHeight / $height, 1);
                $targetWidth = max(1, (int) round($width * $ratio));
                $targetHeight = max(1, (int) round($height * $ratio));
                $target = imagecreatetruecolor($targetWidth, $targetHeight);
                imagealphablending($target, false);
                imagesavealpha($target, true);
                imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
                ob_start();
                imagewebp($target, null, 82);
                $contents = ob_get_clean();
                imagedestroy($source);
                imagedestroy($target);
                if (is_string($contents)) {
                    $path = $directory.'/'.Str::uuid().'.webp';
                    if ($disk->put($path, $contents)) {
                        return $path;
                    }
                }
            }
        }
        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());
        $path = $file->storeAs($directory, Str::uuid().'.'.$extension, 'public');
        if (! is_string($path)) {
            throw new RuntimeException('The image could not be stored.');
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path && (str_starts_with($path, 'products/') || str_starts_with($path, 'services/') || str_starts_with($path, 'portfolio/') || str_starts_with($path, 'blog/') || str_starts_with($path, 'branding/') || str_starts_with($path, 'seo/') || str_starts_with($path, 'cms/') || str_starts_with($path, 'platforms/') || str_starts_with($path, 'profiles/'))) {
            Storage::disk('public')->delete($path);
        }
    }
}
