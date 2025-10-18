<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'date',
        'price',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the room that owns the rate
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Scope for active rates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for rates on specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }
}
