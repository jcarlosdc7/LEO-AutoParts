<?php

namespace App\Services\Media;

use Illuminate\Support\Facades\Storage;
use Imagick;
use ImagickPixel;

class ProductImageService
{
    public function generate(string $relativePath): array
    {
        $disk = Storage::disk('public');
        $source = $disk->path($relativePath);

        if (! is_file($source)) {
            return [];
        }

        $basename = pathinfo($relativePath, PATHINFO_FILENAME);
        $displayPath = "productImages/optimized/{$basename}.webp";
        $backdropPath = "productImages/backdrops/{$basename}.webp";

        foreach ([dirname($disk->path($displayPath)), dirname($disk->path($backdropPath))] as $directory) {
            if (! is_dir($directory)) {
                mkdir($directory, 0775, true);
            }
        }

        $original = new Imagick($source);
        $original->autoOrient();
        $original->setImageColorspace(Imagick::COLORSPACE_SRGB);

        $resized = clone $original;
        $resized->thumbnailImage(456, 456, true);

        $display = new Imagick;
        $display->newImage(512, 512, new ImagickPixel('transparent'), 'webp');
        $display->setImageColorspace(Imagick::COLORSPACE_SRGB);
        $display->compositeImage(
            $resized,
            Imagick::COMPOSITE_OVER,
            (int) ((512 - $resized->getImageWidth()) / 2),
            (int) ((512 - $resized->getImageHeight()) / 2)
        );
        $display->setImageFormat('webp');
        $display->setImageCompressionQuality(78);
        $display->stripImage();
        $display->writeImage($disk->path($displayPath));

        $backdrop = clone $original;
        $backdrop->cropThumbnailImage(160, 120);
        $backdrop->gaussianBlurImage(0, 5);
        $backdrop->modulateImage(100, 125, 100);
        $backdrop->setImageFormat('webp');
        $backdrop->setImageCompressionQuality(48);
        $backdrop->stripImage();
        $backdrop->writeImage($disk->path($backdropPath));

        $original->clear();
        $resized->clear();
        $display->clear();
        $backdrop->clear();

        return ['display' => $displayPath, 'backdrop' => $backdropPath];
    }

    public function generateAll(): int
    {
        $files = Storage::disk('public')->files('productImages');
        $images = array_filter($files, fn (string $file) => in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']));

        foreach ($images as $image) {
            $this->generate($image);
        }

        return count($images);
    }
}
