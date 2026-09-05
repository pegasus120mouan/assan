<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageUploader
{
    public function store(UploadedFile $file, string $directory, ?string $previousPath = null): string
    {
        $this->delete($previousPath);

        $path = $file->store($directory, 'public');
        $this->optimize(Storage::disk('public')->path($path));

        return $path;
    }

    public function delete(?string $path): void
    {
        if (filled($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function optimize(string $fullPath): void
    {
        if (! is_file($fullPath) || ! function_exists('imagecreatetruecolor')) {
            return;
        }

        $info = @getimagesize($fullPath);

        if ($info === false) {
            return;
        }

        [$width, $height, $type] = $info;
        $maxWidth = 1600;

        if ($width <= $maxWidth && $type !== IMAGETYPE_JPEG) {
            return;
        }

        $source = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($fullPath),
            IMAGETYPE_PNG => @imagecreatefrompng($fullPath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($fullPath) : false,
            default => false,
        };

        if ($source === false) {
            return;
        }

        if ($width > $maxWidth) {
            $newHeight = (int) round($height * ($maxWidth / $width));
            $canvas = imagecreatetruecolor($maxWidth, $newHeight);

            if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
            }

            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
            imagedestroy($source);
            $source = $canvas;
        }

        match ($type) {
            IMAGETYPE_JPEG => imagejpeg($source, $fullPath, 82),
            IMAGETYPE_PNG => imagepng($source, $fullPath, 6),
            IMAGETYPE_WEBP => function_exists('imagewebp') && imagewebp($source, $fullPath, 82),
            default => null,
        };

        imagedestroy($source);
    }
}
