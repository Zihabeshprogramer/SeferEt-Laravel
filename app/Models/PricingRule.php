<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rule_type',
        'hotel_id',
        'room_category',
        'start_date',
        'end_date',
        'adjustment_type',
        'adjustment_value',
        'min_nights',
        'max_nights',
        'days_of_week',
        'priority',
        'is_active',
        'conditions'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'adjustment_value' => 'decimal:2',
        'is_active' => 'boolean',
        'days_of_week' => 'array',
        'conditions' => 'array'
    ];

    // Relationships
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }


    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForHotel($query, $hotelId)
    {
        return $query->where(function ($q) use ($hotelId) {
            $q->whereNull('hotel_id')
              ->orWhere('hotel_id', $hotelId);
        });
    }

    public function scopeForRoomCategory($query, $category)
    {
        return $query->where(function ($q) use ($category) {
            $q->whereNull('room_category')
              ->orWhere('room_category', $category);
        });
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
    }

    public function scopeByPriority($query, $direction = 'desc')
    {
        return $query->orderBy('priority', $direction);
    }

    // Helper methods
    public function isApplicableForNights($nights)
    {
        if ($this->min_nights && $nights < $this->min_nights) {
            return false;
        }

        if ($this->max_nights && $nights > $this->max_nights) {
            return false;
        }

        return true;
    }

    public function isApplicableForDate($date)
    {
        $dateObj = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        
        if ($dateObj->lt($this->start_date) || $dateObj->gt($this->end_date)) {
            return false;
        }

        // Check day of week if specified
        if ($this->days_of_week && !empty($this->days_of_week)) {
            $dayOfWeek = strtolower($dateObj->format('l'));
            if (!in_array($dayOfWeek, $this->days_of_week)) {
                return false;
            }
        }

        return true;
    }

    public function calculateAdjustment($basePrice)
    {
        switch ($this->adjustment_type) {
            case 'percentage':
                return ($basePrice * $this->adjustment_value) / 100;
            
            case 'fixed':
                return $this->adjustment_value;
            
            case 'multiply':
                return $basePrice * ($this->adjustment_value - 1);
            
            default:
                return 0;
        }
    }

    public function getFormattedAdjustmentAttribute()
    {
        switch ($this->adjustment_type) {
            case 'percentage':
                return $this->adjustment_value . '%';
            
            case 'fixed':
                return '$' . number_format($this->adjustment_value, 2);
            
            case 'multiply':
                return 'x' . $this->adjustment_value;
            
            default:
                return $this->adjustment_value;
        }
    }

    public function getRuleTypeDisplayAttribute()
    {
        $types = [
            'seasonal' => 'Seasonal Pricing',
            'advance_booking' => 'Advance Booking Discount',
            'length_of_stay' => 'Length of Stay Discount',
            'day_of_week' => 'Day of Week Adjustment',
            'occupancy' => 'Occupancy Based Pricing',
            'promotional' => 'Promotional Pricing',
            'blackout' => 'Blackout Dates',
            'minimum_stay' => 'Minimum Stay Requirement'
        ];

        return $types[$this->rule_type] ?? $this->rule_type;
    }
}
