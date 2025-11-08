<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * AdImageService
 * 
 * Handles image upload, cropping, validation, and variant generation for ads
 */
class AdImageService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }
    /**
     * Image dimension constraints
     */
    public const MIN_WIDTH = 800;
    public const MIN_HEIGHT = 600;
    public const MAX_WIDTH = 4000;
    public const MAX_HEIGHT = 3000;
    public const MAX_FILE_SIZE = 5120; // 5MB in KB

    /**
     * Allowed image types
     */
    public const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
    ];

    /**
     * Image variant sizes for responsive display
     */
    public const VARIANTS = [
        'thumbnail' => ['width' => 300, 'height' => 200],
        'small' => ['width' => 600, 'height' => 400],
        'medium' => ['width' => 1200, 'height' => 800],
        'large' => ['width' => 1920, 'height' => 1280],
    ];

    /**
     * Validate uploaded image
     */
    public function validateImage(UploadedFile $file): array
    {
        $errors = [];

        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE * 1024) {
            $errors[] = 'Image file size must not exceed ' . self::MAX_FILE_SIZE / 1024 . 'MB';
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES)) {
            $errors[] = 'Image must be JPEG, PNG, or WebP format';
        }

        // Check image dimensions
        try {
            $image = $this->manager->read($file->getRealPath());
            $width = $image->width();
            $height = $image->height();

            if ($width < self::MIN_WIDTH || $height < self::MIN_HEIGHT) {
                $errors[] = "Image dimensions must be at least " . self::MIN_WIDTH . "x" . self::MIN_HEIGHT . "px";
            }

            if ($width > self::MAX_WIDTH || $height > self::MAX_HEIGHT) {
                $errors[] = "Image dimensions must not exceed " . self::MAX_WIDTH . "x" . self::MAX_HEIGHT . "px";
            }

            // Check aspect ratio (should be reasonable, e.g., not too narrow or too wide)
            $aspectRatio = $width / $height;
            if ($aspectRatio < 0.5 || $aspectRatio > 3.0) {
                $errors[] = "Image aspect ratio is invalid. Please use a landscape or portrait image.";
            }
        } catch (\Exception $e) {
            $errors[] = 'Invalid image file';
        }

        return $errors;
    }

    /**
     * Upload and process image
     */
    public function uploadImage(UploadedFile $file, int $ownerId): array
    {
        // Validate image
        $errors = $this->validateImage($file);
        if (!empty($errors)) {
            throw new \InvalidArgumentException(implode(', ', $errors));
        }

        // Generate unique filename
        $filename = $this->generateFilename($file);
        $basePath = "ads/{$ownerId}";

        // Load image
        $image = $this->manager->read($file->getRealPath());

        // Store original image
        $originalPath = "{$basePath}/original/{$filename}";
        Storage::disk('public')->put($originalPath, (string) $image->encode());

        // Generate variants
        $variants = $this->generateVariants($image, $basePath, $filename);

        return [
            'original_path' => $originalPath,
            'variants' => $variants,
            'dimensions' => [
                'width' => $image->width(),
                'height' => $image->height(),
            ],
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Crop image with server-side processing
     */
    public function cropImage(string $imagePath, array $cropData): array
    {
        if (!Storage::disk('public')->exists($imagePath)) {
            throw new \InvalidArgumentException('Image not found');
        }

        // Validate crop data
        $this->validateCropData($cropData);

        // Load image
        $fullPath = Storage::disk('public')->path($imagePath);
        $image = $this->manager->read($fullPath);

        // Apply crop
        $image->crop(
            (int) $cropData['width'],
            (int) $cropData['height'],
            (int) $cropData['x'],
            (int) $cropData['y']
        );

        // Generate new filename
        $pathInfo = pathinfo($imagePath);
        $croppedFilename = $pathInfo['filename'] . '_cropped_' . time() . '.' . $pathInfo['extension'];
        $basePath = dirname(dirname($imagePath)); // Go up two levels from original/filename
        $croppedPath = "{$basePath}/cropped/{$croppedFilename}";

        // Store cropped image
        Storage::disk('public')->put($croppedPath, (string) $image->encode());

        // Generate variants for cropped image
        $variants = $this->generateVariants($image, $basePath, $croppedFilename);

        return [
            'cropped_path' => $croppedPath,
            'variants' => $variants,
            'dimensions' => [
                'width' => $image->width(),
                'height' => $image->height(),
            ],
        ];
    }

    /**
     * Generate responsive image variants
     */
    protected function generateVariants($image, string $basePath, string $filename): array
    {
        $variants = [];
        $pathInfo = pathinfo($filename);
        $baseName = $pathInfo['filename'];
        $extension = $pathInfo['extension'];

        foreach (self::VARIANTS as $variant => $dimensions) {
            // Clone by re-reading the image data
            $variantImage = $this->manager->read((string) $image->encode());
            
            // Resize image to fit within variant dimensions while maintaining aspect ratio
            $variantImage->scale(width: $dimensions['width'], height: $dimensions['height']);

            // Generate variant filename
            $variantFilename = "{$baseName}_{$variant}.{$extension}";
            $variantPath = "{$basePath}/variants/{$variantFilename}";

            // Store variant
            Storage::disk('public')->put($variantPath, (string) $variantImage->encode());

            $variants[$variant] = $variantPath;
        }

        return $variants;
    }

    /**
     * Delete image and all its variants
     */
    public function deleteImage(string $imagePath, ?array $variants = null): void
    {
        // Delete main image
        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        // Delete variants
        if ($variants) {
            foreach ($variants as $variantPath) {
                if (Storage::disk('public')->exists($variantPath)) {
                    Storage::disk('public')->delete($variantPath);
                }
            }
        }
    }

    /**
     * Validate crop data
     */
    protected function validateCropData(array $cropData): void
    {
        $required = ['x', 'y', 'width', 'height'];
        
        foreach ($required as $field) {
            if (!isset($cropData[$field])) {
                throw new \InvalidArgumentException("Missing required crop field: {$field}");
            }
            
            if (!is_numeric($cropData[$field]) || $cropData[$field] < 0) {
                throw new \InvalidArgumentException("Invalid crop value for {$field}");
            }
        }

        // Validate minimum crop dimensions
        if ($cropData['width'] < 400 || $cropData['height'] < 300) {
            throw new \InvalidArgumentException("Crop area is too small. Minimum size is 400x300px");
        }
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $hash = Str::random(40);
        
        return "{$hash}.{$extension}";
    }

    /**
     * Get image dimensions
     */
    public function getImageDimensions(string $imagePath): array
    {
        if (!Storage::disk('public')->exists($imagePath)) {
            throw new \InvalidArgumentException('Image not found');
        }

        $fullPath = Storage::disk('public')->path($imagePath);
        $image = Image::make($fullPath);

        return [
            'width' => $image->width(),
            'height' => $image->height(),
        ];
    }

    /**
     * Validate CTA position (0.0 to 1.0)
     */
    public function validateCtaPosition(float $position): bool
    {
        return $position >= 0.0 && $position <= 1.0;
    }

    /**
     * Validate CTA text (safe content, length)
     */
    public function validateCtaText(?string $text): array
    {
        $errors = [];

        if ($text === null || trim($text) === '') {
            return $errors; // CTA text is optional
        }

        // Length validation
        if (strlen($text) > 100) {
            $errors[] = 'CTA text must not exceed 100 characters';
        }

        if (strlen($text) < 2) {
            $errors[] = 'CTA text must be at least 2 characters';
        }

        // Content validation - check for unsafe content
        $disallowedPatterns = [
            '/<script/i',
            '/<iframe/i',
            '/javascript:/i',
            '/on\w+\s*=/i', // onclick, onload, etc.
        ];

        foreach ($disallowedPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                $errors[] = 'CTA text contains disallowed content';
                break;
            }
        }

        return $errors;
    }

    /**
     * Sanitize CTA text
     */
    public function sanitizeCtaText(string $text): string
    {
        // Strip HTML tags
        $text = strip_tags($text);
        
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Trim
        $text = trim($text);
        
        return $text;
    }
}
