<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackageDraft;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PackageImageService
{
    /**
     * Clean up orphaned images from deleted drafts
     */
    public function cleanupOrphanedDraftImages(PackageDraft $draft): int
    {
        $deletedCount = 0;
        
        if (!isset($draft->draft_data['images']) || empty($draft->draft_data['images'])) {
            return $deletedCount;
        }

        foreach ($draft->draft_data['images'] as $image) {
            if ($this->deleteImageFiles($image, 'package-drafts')) {
                $deletedCount++;
            }
        }

        Log::info("Cleaned up {$deletedCount} orphaned images from draft {$draft->id}");
        return $deletedCount;
    }

    /**
     * Clean up images from deleted packages
     */
    public function cleanupPackageImages(Package $package): int
    {
        $deletedCount = 0;
        
        if (!$package->hasImages()) {
            return $deletedCount;
        }

        foreach ($package->images as $image) {
            if ($this->deleteImageFiles($image, 'packages')) {
                $deletedCount++;
            }
        }

        Log::info("Cleaned up {$deletedCount} images from package {$package->id}");
        return $deletedCount;
    }

    /**
     * Transfer images from draft to package folder
     */
    public function transferImagesFromDraft(array $draftImages): array
    {
        $transferredImages = [];
        
        foreach ($draftImages as $image) {
            $transferredSizes = [];
            
            foreach ($image['sizes'] as $sizeKey => $draftPath) {
                // Create new path in packages folder
                $fileName = basename($draftPath);
                $packagePath = "images/packages/" . date('Y/m') . "/" . $fileName;
                
                // Ensure directory exists
                $fullPackagePath = storage_path("app/public/images/packages/" . date('Y/m'));
                if (!file_exists($fullPackagePath)) {
                    mkdir($fullPackagePath, 0755, true);
                }
                
                $fullDraftPath = storage_path("app/public/{$draftPath}");
                $fullNewPath = storage_path("app/public/{$packagePath}");
                
                // Copy file from draft to package location
                if (file_exists($fullDraftPath)) {
                    if (copy($fullDraftPath, $fullNewPath)) {
                        $transferredSizes[$sizeKey] = $packagePath;
                        
                        // Delete original draft file
                        unlink($fullDraftPath);
                    } else {
                        Log::warning("Failed to transfer image from {$fullDraftPath} to {$fullNewPath}");
                    }
                }
            }
            
            // Add transferred image data
            if (!empty($transferredSizes)) {
                $transferredImages[] = array_merge($image, [
                    'sizes' => $transferredSizes
                ]);
            }
        }
        
        return $transferredImages;
    }

    /**
     * Delete image files from storage
     */
    public function deleteImageFiles(array $imageData, string $folder): bool
    {
        $allDeleted = true;
        
        foreach ($imageData['sizes'] ?? [] as $size => $path) {
            $fullPath = storage_path("app/public/{$path}");
            if (file_exists($fullPath)) {
                if (!unlink($fullPath)) {
                    Log::warning("Failed to delete image file: {$fullPath}");
                    $allDeleted = false;
                }
            }
        }
        
        return $allDeleted;
    }

    /**
     * Clean up expired draft images (can be run via scheduler)
     */
    public function cleanupExpiredDraftImages(): int
    {
        $totalDeleted = 0;
        
        // Get all expired drafts
        $expiredDrafts = PackageDraft::expired()
            ->whereNotNull('draft_data')
            ->get();

        foreach ($expiredDrafts as $draft) {
            $deletedCount = $this->cleanupOrphanedDraftImages($draft);
            $totalDeleted += $deletedCount;
        }

        Log::info("Cleanup completed. Deleted {$totalDeleted} orphaned images from {$expiredDrafts->count()} expired drafts.");
        return $totalDeleted;
    }

    /**
     * Get disk usage statistics for package images
     */
    public function getImageStorageStats(): array
    {
        $draftPath = storage_path('app/public/images/package-drafts');
        $packagePath = storage_path('app/public/images/packages');
        
        $draftSize = $this->getDirectorySize($draftPath);
        $packageSize = $this->getDirectorySize($packagePath);
        
        $draftCount = $this->countFilesInDirectory($draftPath);
        $packageCount = $this->countFilesInDirectory($packagePath);
        
        return [
            'draft_images' => [
                'count' => $draftCount,
                'size_mb' => round($draftSize / 1024 / 1024, 2),
                'size_bytes' => $draftSize
            ],
            'package_images' => [
                'count' => $packageCount,
                'size_mb' => round($packageSize / 1024 / 1024, 2),
                'size_bytes' => $packageSize
            ],
            'total' => [
                'count' => $draftCount + $packageCount,
                'size_mb' => round(($draftSize + $packageSize) / 1024 / 1024, 2),
                'size_bytes' => $draftSize + $packageSize
            ]
        ];
    }

    /**
     * Get the size of a directory
     */
    private function getDirectorySize(string $path): int
    {
        if (!is_dir($path)) {
            return 0;
        }

        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Count files in a directory recursively
     */
    private function countFilesInDirectory(string $path): int
    {
        if (!is_dir($path)) {
            return 0;
        }

        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Optimize image file sizes (can be run in background)
     */
    public function optimizeImages(array $imagePaths): array
    {
        $optimized = [];
        
        foreach ($imagePaths as $path) {
            $fullPath = storage_path("app/public/{$path}");
            
            if (!file_exists($fullPath)) {
                continue;
            }
            
            $originalSize = filesize($fullPath);
            
            // Basic optimization using image manipulation
            try {
                $this->optimizeImageFile($fullPath);
                $newSize = filesize($fullPath);
                
                $optimized[] = [
                    'path' => $path,
                    'original_size' => $originalSize,
                    'new_size' => $newSize,
                    'saved_bytes' => $originalSize - $newSize,
                    'saved_percent' => round((($originalSize - $newSize) / $originalSize) * 100, 2)
                ];
            } catch (\Exception $e) {
                Log::warning("Failed to optimize image {$path}: " . $e->getMessage());
            }
        }
        
        return $optimized;
    }

    /**
     * Optimize a single image file
     */
    private function optimizeImageFile(string $filePath): void
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($filePath);
                if ($image) {
                    imagejpeg($image, $filePath, 85); // 85% quality
                    imagedestroy($image);
                }
                break;
                
            case 'png':
                $image = imagecreatefrompng($filePath);
                if ($image) {
                    imagepng($image, $filePath, 6); // Compression level 6
                    imagedestroy($image);
                }
                break;
                
            case 'webp':
                $image = imagecreatefromwebp($filePath);
                if ($image) {
                    imagewebp($image, $filePath, 85); // 85% quality
                    imagedestroy($image);
                }
                break;
        }
    }
}