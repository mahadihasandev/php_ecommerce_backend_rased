<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\ImageUploadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class OptimizeStoredImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:optimize {--quality=85 : Target crispness quality percentage (1-100)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compress and optimize all currently stored images in storage/app/public/ to crisp WebP format';

    public function handle(ImageUploadService $optimizer): int
    {
        $quality = (int) $this->option('quality');
        if ($quality < 1 || $quality > 100) {
            $quality = 85;
        }

        $publicStoragePath = storage_path('app/public');

        if (!File::exists($publicStoragePath)) {
            $this->warn("Storage directory [{$publicStoragePath}] does not exist yet.");
            return Command::SUCCESS;
        }

        $this->info("Scanning storage/app/public for images to optimize (Quality: {$quality}%)...");

        $allFiles = File::allFiles($publicStoragePath);
        $imageExtensions = ['jpg', 'jpeg', 'png', 'bmp', 'webp'];
        $imageFiles = array_filter($allFiles, function ($file) use ($imageExtensions) {
            return in_array(strtolower($file->getExtension()), $imageExtensions, true);
        });

        if (empty($imageFiles)) {
            $this->info("No image files currently found in storage/app/public.");
            $this->info("All future images uploaded via the Dashboard will automatically be compressed to crisp {$quality}% WebP.");
            return Command::SUCCESS;
        }

        $totalOriginalBytes = 0;
        $totalOptimizedBytes = 0;
        $processedCount = 0;
        $tableRows = [];

        foreach ($imageFiles as $file) {
            $sourcePath = $file->getRealPath();
            $relPath = str_replace('\\', '/', $file->getRelativePathname());
            $origSize = $file->getSize();
            $totalOriginalBytes += $origSize;

            $folder = dirname($relPath);
            $ext = strtolower($file->getExtension());
            $baseName = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            // Determine dimensions based on folder
            $maxWidth = str_contains($folder, 'banner') ? 1920 : (str_contains($folder, 'brand') ? 800 : 1200);
            $maxHeight = str_contains($folder, 'banner') ? 1080 : (str_contains($folder, 'brand') ? 800 : 1200);

            $targetWebpPath = $file->getPath() . DIRECTORY_SEPARATOR . $baseName . '.webp';
            $oldUrl = '/storage/' . $relPath;
            $newUrl = '/storage/' . ($folder !== '.' ? $folder . '/' : '') . $baseName . '.webp';

            // Optimize
            $tempOptimized = $file->getPath() . DIRECTORY_SEPARATOR . $baseName . '_tmp.webp';
            $success = $optimizer->compressAndResize($sourcePath, $tempOptimized, $maxWidth, $maxHeight, $quality);

            if ($success && file_exists($tempOptimized)) {
                $newSize = filesize($tempOptimized);

                // If converted from another extension, remove old file and rename
                if ($ext !== 'webp') {
                    @unlink($sourcePath);
                    rename($tempOptimized, $targetWebpPath);
                    $this->updateDatabaseReferences($oldUrl, $newUrl);
                } else {
                    // Overwrite existing webp
                    @unlink($sourcePath);
                    rename($tempOptimized, $targetWebpPath);
                }

                $totalOptimizedBytes += $newSize;
                $processedCount++;

                $savedBytes = max(0, $origSize - $newSize);
                $percentSaved = $origSize > 0 ? round(($savedBytes / $origSize) * 100, 1) : 0;

                $tableRows[] = [
                    $relPath,
                    $this->formatBytes($origSize),
                    $this->formatBytes($newSize),
                    "{$percentSaved}% ({$this->formatBytes($savedBytes)})",
                ];
            } else {
                $totalOptimizedBytes += $origSize;
                if (file_exists($tempOptimized)) {
                    @unlink($tempOptimized);
                }
            }
        }

        $this->table(['File', 'Original Size', 'Optimized Size', 'Savings'], $tableRows);

        $totalSaved = max(0, $totalOriginalBytes - $totalOptimizedBytes);
        $totalPercent = $totalOriginalBytes > 0 ? round(($totalSaved / $totalOriginalBytes) * 100, 1) : 0;

        $this->newLine();
        $this->info(" Optimization complete!");
        $this->info("Images processed: {$processedCount}");
        $this->info("Original total:   " . $this->formatBytes($totalOriginalBytes));
        $this->info("Optimized total:  " . $this->formatBytes($totalOptimizedBytes));
        $this->info("Total reduction:  {$totalPercent}% saved (" . $this->formatBytes($totalSaved) . ")");

        return Command::SUCCESS;
    }

    /**
     * Update database columns when a stored file extension is converted to .webp.
     */
    protected function updateDatabaseReferences(string $oldUrl, string $newUrl): void
    {
        // 1. Banners
        Banner::where('image', $oldUrl)->update(['image' => $newUrl]);

        // 2. Categories
        Category::where('image', $oldUrl)->update(['image' => $newUrl]);

        // 3. Brands
        Brand::where('image', $oldUrl)->update(['image' => $newUrl]);

        // 4. Users
        User::where('avatar', $oldUrl)->update(['avatar' => $newUrl]);

        // 5. Products (image URLs stored in JSON array)
        Product::all()->each(function (Product $prod) use ($oldUrl, $newUrl) {
            $images = $prod->images;
            if (is_array($images) && in_array($oldUrl, $images, true)) {
                $updated = array_map(fn($img) => $img === $oldUrl ? $newUrl : $img, $images);
                $prod->images = $updated;
                $prod->save();
            }
        });
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
