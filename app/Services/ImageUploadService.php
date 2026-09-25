<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service to handle image uploads, optimization, compression, and deletions.
 * Optimizes images with crisp 85% high-quality WebP encoding and smart proportional resizing.
 */
class ImageUploadService
{
    /**
     * Default high-fidelity quality factor (85% gives crisp e-commerce quality without bloat).
     */
    public const DEFAULT_QUALITY = 85;

    /**
     * Upload and optimize a single image file.
     *
     * @param UploadedFile $file
     * @param string $folder Directory inside storage/app/public/ (e.g. 'banners', 'products', 'categories', 'brands')
     * @param int $quality Compression quality (1-100, default 85 for crispness)
     * @return string Public web URL path (e.g. '/storage/products/xyz.webp')
     */
    public function upload(UploadedFile $file, string $folder = 'uploads', int $quality = self::DEFAULT_QUALITY): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $mime = $file->getMimeType() ?: '';

        // If SVG or animated GIF, preserve original format without raster loss
        if ($extension === 'svg' || str_contains($mime, 'svg') || $extension === 'gif' || str_contains($mime, 'gif')) {
            $filename = Str::random(24) . '.' . $extension;
            $path = $file->storeAs($folder, $filename, 'public');
            return '/storage/' . $path;
        }

        // Determine dimension caps based on folder purpose
        [$maxWidth, $maxHeight] = $this->getDimensionCaps($folder);

        // Attempt crisp optimization via GD
        $optimizedWebpName = Str::random(24) . '.webp';
        $relativeStoragePath = $folder . '/' . $optimizedWebpName;
        $absoluteStorageDir = storage_path('app/public/' . $folder);

        if (!file_exists($absoluteStorageDir)) {
            mkdir($absoluteStorageDir, 0755, true);
        }

        $absoluteDestination = $absoluteStorageDir . '/' . $optimizedWebpName;
        $success = $this->compressAndResize(
            $file->getRealPath(),
            $absoluteDestination,
            $maxWidth,
            $maxHeight,
            $quality
        );

        if ($success && file_exists($absoluteDestination)) {
            return '/storage/' . $relativeStoragePath;
        }

        // Fallback: standard upload if GD encounters unsupported edge case
        $filename = Str::random(24) . '.' . $extension;
        $path = $file->storeAs($folder, $filename, 'public');
        return '/storage/' . $path;
    }

    /**
     * Upload and optimize multiple image files.
     *
     * @param array<UploadedFile> $files
     * @param string $folder
     * @param int $quality
     * @return array<string> List of uploaded public web URLs
     */
    public function uploadMultiple(array $files, string $folder = 'uploads', int $quality = self::DEFAULT_QUALITY): array
    {
        $urls = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $urls[] = $this->upload($file, $folder, $quality);
            }
        }
        return $urls;
    }

    /**
     * Compress, resize, and convert a raster image to crisp WebP.
     *
     * @param string $sourcePath Local filesystem path to source image
     * @param string $destinationPath Path to save the optimized WebP
     * @param int $maxWidth Maximum width constraint
     * @param int $maxHeight Maximum height constraint
     * @param int $quality Compression quality (85 by default)
     * @return bool
     */
    public function compressAndResize(
        string $sourcePath,
        string $destinationPath,
        int $maxWidth = 1400,
        int $maxHeight = 1400,
        int $quality = self::DEFAULT_QUALITY
    ): bool {
        if (!file_exists($sourcePath) || !is_readable($sourcePath)) {
            return false;
        }

        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        [$origWidth, $origHeight, $imageType] = $imageInfo;

        // Load image according to type
        $sourceImage = match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($sourcePath),
            IMAGETYPE_BMP => @imagecreatefrombmp($sourcePath),
            default => null,
        };

        if (!$sourceImage) {
            return false;
        }

        // Auto-orient mobile camera uploads using EXIF orientation if available
        if ($imageType === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($sourcePath);
            if (!empty($exif['Orientation'])) {
                $sourceImage = match ($exif['Orientation']) {
                    3 => imagerotate($sourceImage, 180, 0),
                    6 => imagerotate($sourceImage, -90, 0),
                    8 => imagerotate($sourceImage, 90, 0),
                    default => $sourceImage,
                };
                $origWidth = imagesx($sourceImage);
                $origHeight = imagesy($sourceImage);
            }
        }

        // Calculate proportional scale - never upscale smaller images
        $scale = min(1.0, min($maxWidth / $origWidth, $maxHeight / $origHeight));
        $newWidth = (int) max(1, round($origWidth * $scale));
        $newHeight = (int) max(1, round($origHeight * $scale));

        // Create high-color truecolor canvas
        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve alpha transparency for PNGs and transparent WebPs
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $transparent);
        imagealphablending($canvas, true);

        // High-quality bicubic resampling
        imagecopyresampled($canvas, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // Save as crisp WebP
        $result = imagewebp($canvas, $destinationPath, $quality);

        imagedestroy($sourceImage);
        imagedestroy($canvas);

        return $result;
    }

    /**
     * Get maximum dimension caps according to folder purpose.
     *
     * @param string $folder
     * @return array{0: int, 1: int} [maxWidth, maxHeight]
     */
    protected function getDimensionCaps(string $folder): array
    {
        return match ($folder) {
            'banners' => [1920, 1080],
            'products' => [1200, 1200],
            'categories' => [1000, 1000],
            'brands' => [800, 800],
            'avatars' => [500, 500],
            default => [1400, 1400],
        };
    }

    /**
     * Delete an existing image file from storage if stored locally.
     *
     * @param string|null $pathOrUrl
     * @return bool
     */
    public function delete(?string $pathOrUrl): bool
    {
        if (empty($pathOrUrl)) {
            return false;
        }

        // Clean any leading '/storage/' or 'storage/' to get relative path within public disk
        $cleanPath = preg_replace('#^/?storage/#', '', $pathOrUrl);
        if ($cleanPath && Storage::disk('public')->exists($cleanPath)) {
            return Storage::disk('public')->delete($cleanPath);
        }

        return false;
    }

    /**
     * Delete multiple image files.
     *
     * @param array<string>|null $pathsOrUrls
     */
    public function deleteMultiple(?array $pathsOrUrls): void
    {
        if (!is_array($pathsOrUrls)) {
            return;
        }

        foreach ($pathsOrUrls as $path) {
            if (is_string($path)) {
                $this->delete($path);
            }
        }
    }
}
