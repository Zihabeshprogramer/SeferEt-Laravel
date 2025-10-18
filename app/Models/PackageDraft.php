<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PackageDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug', 
        'draft_data',
        'current_step',
        'step_status',
        'last_accessed_at',
        'expires_at',
        'is_expired'
    ];

    protected $casts = [
        'draft_data' => 'array',
        'step_status' => 'array',
        'last_accessed_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_expired' => 'boolean',
    ];

    /**
     * Get the user that owns this draft
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update last accessed timestamp
     */
    public function touch($attribute = null)
    {
        $this->last_accessed_at = now();
        return parent::touch($attribute);
    }

    /**
     * Mark draft as expired
     */
    public function markExpired()
    {
        $this->update([
            'is_expired' => true,
            'expires_at' => now()
        ]);
    }

    /**
     * Check if draft is expired
     */
    public function isExpired(): bool
    {
        return $this->is_expired || ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Scope for active drafts only
     */
    public function scopeActive($query)
    {
        return $query->where('is_expired', false)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope for expired drafts
     */
    public function scopeExpired($query)
    {
        return $query->where('is_expired', true)
                    ->orWhere('expires_at', '<=', now());
    }

    /**
     * Scope for user's drafts
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage(): int
    {
        // Calculate progress based on current step
        $totalSteps = 5; // Package creation has 5 steps
        $currentStep = max(1, $this->current_step); // Ensure minimum is 1
        
        // Calculate percentage based on current step (steps 1-5)
        $percentage = (($currentStep - 1) / ($totalSteps - 1)) * 100;
        
        return min(100, max(0, round($percentage)));
    }

    /**
     * Get the current step name
     */
    public function getCurrentStepName(): string
    {
        $steps = [
            1 => 'Basic Information',
            2 => 'Itinerary Builder', 
            3 => 'Provider Selection',
            4 => 'Pricing Configuration',
            5 => 'Review & Submit'
        ];

        return $steps[$this->current_step] ?? 'Unknown Step';
    }

    /**
     * Auto-expire old drafts (run via scheduler)
     */
    public static function expireOldDrafts()
    {
        $expiredCount = static::where('is_expired', false)
            ->where(function($query) {
                // Expire drafts that haven't been accessed for 7 days
                $query->where('last_accessed_at', '<', now()->subDays(7))
                      ->orWhere('created_at', '<', now()->subDays(7));
            })
            ->update([
                'is_expired' => true,
                'expires_at' => now()
            ]);

        return $expiredCount;
    }
    
    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($draft) {
            // Clean up associated images when draft is deleted
            try {
                $imageService = app(\App\Services\PackageImageService::class);
                $imageService->cleanupOrphanedDraftImages($draft);
            } catch (\Exception $e) {
                \Log::warning('Failed to cleanup images for draft ' . $draft->id . ': ' . $e->getMessage());
            }
        });
    }
}
