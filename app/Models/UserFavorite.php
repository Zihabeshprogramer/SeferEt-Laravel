<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class UserFavorite extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'user_favorites';

    /**
     * Favorite item types
     */
    const TYPE_FLIGHT = 'flight';
    const TYPE_HOTEL = 'hotel';
    const TYPE_PACKAGE = 'package';

    const TYPES = [
        self::TYPE_FLIGHT,
        self::TYPE_HOTEL,
        self::TYPE_PACKAGE,
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'type',
        'item_data',
        'reference_id',
        'reference_table',
        'title',
        'notes',
        'is_available',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'item_data' => 'array',
        'is_available' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the favorite.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by availability
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope to order by most recent
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Check if a favorite exists for a user and item
     */
    public static function existsForUser(int $userId, string $type, int $referenceId = null): bool
    {
        $query = static::where('user_id', $userId)->where('type', $type);
        
        if ($referenceId) {
            $query->where('reference_id', $referenceId);
        }
        
        return $query->exists();
    }

    /**
     * Add a favorite for a user
     */
    public static function addFavorite(int $userId, string $type, array $itemData, int $referenceId = null, string $title = null, string $notes = null): ?self
    {
        // Check if favorite already exists
        if (self::existsForUser($userId, $type, $referenceId)) {
            return null; // Already exists
        }

        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'item_data' => $itemData,
            'reference_id' => $referenceId,
            'reference_table' => self::getTableNameForType($type),
            'title' => $title ?? self::generateTitleFromData($type, $itemData),
            'notes' => $notes,
            'is_available' => true,
        ]);
    }

    /**
     * Remove a favorite
     */
    public static function removeFavorite(int $userId, string $type, int $referenceId = null): bool
    {
        $query = static::where('user_id', $userId)->where('type', $type);
        
        if ($referenceId) {
            $query->where('reference_id', $referenceId);
        }
        
        return $query->delete() > 0;
    }

    /**
     * Get table name for a given type
     */
    private static function getTableNameForType(string $type): ?string
    {
        return match($type) {
            self::TYPE_FLIGHT => 'flights',
            self::TYPE_HOTEL => 'hotels',
            self::TYPE_PACKAGE => 'packages',
            default => null,
        };
    }

    /**
     * Generate a title from item data
     */
    private static function generateTitleFromData(string $type, array $itemData): string
    {
        return match($type) {
            self::TYPE_FLIGHT => self::generateFlightTitle($itemData),
            self::TYPE_HOTEL => self::generateHotelTitle($itemData),
            self::TYPE_PACKAGE => self::generatePackageTitle($itemData),
            default => 'Favorite Item',
        };
    }

    /**
     * Generate flight title from data
     */
    private static function generateFlightTitle(array $data): string
    {
        $from = $data['from'] ?? $data['departure_city'] ?? 'Unknown';
        $to = $data['to'] ?? $data['arrival_city'] ?? 'Unknown';
        $airline = $data['airline'] ?? 'Unknown Airline';
        
        return "{$airline} - {$from} to {$to}";
    }

    /**
     * Generate hotel title from data
     */
    private static function generateHotelTitle(array $data): string
    {
        $name = $data['name'] ?? $data['hotel_name'] ?? 'Unknown Hotel';
        $city = $data['city'] ?? $data['location'] ?? '';
        
        return $city ? "{$name} - {$city}" : $name;
    }

    /**
     * Generate package title from data
     */
    private static function generatePackageTitle(array $data): string
    {
        $name = $data['name'] ?? $data['package_name'] ?? $data['title'] ?? 'Unknown Package';
        $destination = $data['destination'] ?? $data['location'] ?? '';
        
        return $destination ? "{$name} - {$destination}" : $name;
    }

    /**
     * Get formatted item data with additional computed fields
     */
    public function getFormattedItemDataAttribute(): array
    {
        $data = $this->item_data;
        
        // Add common computed fields
        $data['favorite_id'] = $this->id;
        $data['added_date'] = $this->created_at->toDateString();
        $data['added_time_ago'] = $this->created_at->diffForHumans();
        $data['is_available'] = $this->is_available;
        $data['notes'] = $this->notes;
        
        return $data;
    }

    /**
     * Mark favorite as unavailable
     */
    public function markUnavailable(): bool
    {
        return $this->update(['is_available' => false]);
    }

    /**
     * Mark favorite as available
     */
    public function markAvailable(): bool
    {
        return $this->update(['is_available' => true]);
    }

    /**
     * Update notes for the favorite
     */
    public function updateNotes(string $notes): bool
    {
        return $this->update(['notes' => $notes]);
    }
}