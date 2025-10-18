<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * FlightCollaboration Model
 * 
 * Manages collaboration between travel agents on group flight bookings
 * 
 * @property int $id
 * @property int $flight_id
 * @property int $owner_agent_id
 * @property int $collaborator_agent_id
 * @property string $status
 * @property decimal $commission_percentage
 * @property int $allocated_seats
 * @property int $booked_seats
 * @property decimal $total_commission_earned
 * @property string $invitation_message
 * @property string $response_message
 * @property datetime $invited_at
 * @property datetime $responded_at
 * @property datetime $expires_at
 * @property array $terms_agreed
 */
class FlightCollaboration extends Model
{
    use HasFactory;
    
    /**
     * Collaboration statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    
    /**
     * Available statuses
     */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACCEPTED,
        self::STATUS_DECLINED,
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'flight_id',
        'owner_agent_id',
        'collaborator_agent_id',
        'status',
        'commission_percentage',
        'allocated_seats',
        'booked_seats',
        'total_commission_earned',
        'invitation_message',
        'response_message',
        'invited_at',
        'responded_at',
        'expires_at',
        'terms_agreed',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'commission_percentage' => 'decimal:2',
        'total_commission_earned' => 'decimal:2',
        'allocated_seats' => 'integer',
        'booked_seats' => 'integer',
        'invited_at' => 'datetime',
        'responded_at' => 'datetime',
        'expires_at' => 'datetime',
        'terms_agreed' => 'array',
    ];
    
    /**
     * Get the flight
     */
    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }
    
    /**
     * Get the owner agent (flight creator)
     */
    public function ownerAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_agent_id');
    }
    
    /**
     * Get the collaborator agent
     */
    public function collaboratorAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collaborator_agent_id');
    }
    
    /**
     * Scope for active collaborations
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
    
    /**
     * Scope for pending collaborations
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
    
    /**
     * Scope for expired invitations
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where('expires_at', '<', now());
    }
    
    /**
     * Scope for collaborations by agent
     */
    public function scopeForAgent($query, int $agentId)
    {
        return $query->where(function($q) use ($agentId) {
            $q->where('owner_agent_id', $agentId)
              ->orWhere('collaborator_agent_id', $agentId);
        });
    }
    
    /**
     * Check if collaboration is expired
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_PENDING && 
               $this->expires_at && 
               $this->expires_at->isPast();
    }
    
    /**
     * Check if collaboration can be accepted
     */
    public function canBeAccepted(): bool
    {
        return $this->status === self::STATUS_PENDING && 
               !$this->isExpired() && 
               $this->flight->available_seats >= $this->allocated_seats;
    }
    
    /**
     * Accept the collaboration
     */
    public function accept(string $responseMessage = null): bool
    {
        if (!$this->canBeAccepted()) {
            return false;
        }
        
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'responded_at' => now(),
            'response_message' => $responseMessage,
        ]);
        
        return true;
    }
    
    /**
     * Decline the collaboration
     */
    public function decline(string $responseMessage = null): void
    {
        $this->update([
            'status' => self::STATUS_DECLINED,
            'responded_at' => now(),
            'response_message' => $responseMessage,
        ]);
    }
    
    /**
     * Activate the collaboration (start booking phase)
     */
    public function activate(): void
    {
        if ($this->status === self::STATUS_ACCEPTED) {
            $this->update(['status' => self::STATUS_ACTIVE]);
        }
    }
    
    /**
     * Complete the collaboration
     */
    public function complete(): void
    {
        if ($this->status === self::STATUS_ACTIVE) {
            $this->update(['status' => self::STATUS_COMPLETED]);
        }
    }
    
    /**
     * Calculate commission for booked seats
     */
    public function calculateCommission(int $bookedSeats, float $pricePerSeat): float
    {
        $totalRevenue = $bookedSeats * $pricePerSeat;
        return ($totalRevenue * $this->commission_percentage) / 100;
    }
    
    /**
     * Record booking and commission
     */
    public function recordBooking(int $seatsBooked, float $commissionAmount): void
    {
        $this->increment('booked_seats', $seatsBooked);
        $this->increment('total_commission_earned', $commissionAmount);
    }
    
    /**
     * Get remaining allocated seats
     */
    public function getRemainingSeats(): int
    {
        return max(0, $this->allocated_seats - $this->booked_seats);
    }
}
