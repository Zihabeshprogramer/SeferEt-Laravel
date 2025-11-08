<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdClick extends Model
{
    use HasFactory;

    /**
     * Disable updated_at timestamp (we only need created_at)
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ad_id',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'page_url',
        'referrer',
        'device_type',
        'placement',
        'destination_url',
        'converted',
        'converted_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'converted' => 'boolean',
        'converted_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    // ===========================================
    // RELATIONSHIPS
    // ===========================================

    /**
     * Get the ad that was clicked
     */
    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    /**
     * Get the user who clicked the ad (if logged in)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ===========================================
    // HELPER METHODS
    // ===========================================

    /**
     * Create click record and increment ad counter
     */
    public static function record(
        int $adId,
        ?string $destinationUrl = null,
        ?int $userId = null,
        ?string $sessionId = null,
        ?string $deviceType = null,
        ?string $placement = null
    ): self {
        $click = static::create([
            'ad_id' => $adId,
            'user_id' => $userId ?? auth()->id(),
            'session_id' => $sessionId ?? session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'page_url' => request()->fullUrl(),
            'referrer' => request()->header('referer'),
            'device_type' => $deviceType,
            'placement' => $placement,
            'destination_url' => $destinationUrl,
            'metadata' => [
                'timestamp' => now()->toISOString(),
            ],
        ]);

        // Increment ad click count
        $ad = Ad::find($adId);
        if ($ad) {
            $ad->recordClick();
        }

        return $click;
    }

    /**
     * Mark this click as converted
     */
    public function markConverted(): bool
    {
        $this->converted = true;
        $this->converted_at = now();
        return $this->save();
    }

    /**
     * Check if click already exists for this session/ad combination
     * (to prevent duplicate counting in short time window)
     */
    public static function recentlyTracked(int $adId, string $sessionId, int $minutes = 5): bool
    {
        return static::where('ad_id', $adId)
            ->where('session_id', $sessionId)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->exists();
    }
}
