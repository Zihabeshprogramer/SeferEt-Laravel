<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class PackageBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Core booking information
        'package_id', 'customer_id', 'booking_reference', 'booking_source',
        
        // Booking dates
        'departure_date', 'return_date', 'duration_days',
        
        // Participants
        'adults', 'children', 'infants', 'total_participants',
        
        // Customer details
        'primary_contact_name', 'primary_contact_email', 'primary_contact_phone',
        'participant_details', 'special_requirements', 'dietary_requirements',
        
        // Pricing
        'package_price', 'addon_price', 'discount_amount', 'tax_amount',
        'service_fee', 'total_amount', 'paid_amount', 'pending_amount',
        'currency', 'pricing_breakdown',
        
        // Payment information
        'payment_status', 'payment_method', 'payment_details',
        
        // Booking status
        'status', 'cancellation_reason', 'cancellation_policy',
        
        // Additional services
        'selected_addons', 'accommodation_preferences', 'transport_preferences',
        
        // Booking lifecycle timestamps
        'confirmed_at', 'payment_due_date', 'departure_reminder_sent_at',
        'cancelled_at', 'completed_at',
        
        // Notes and communication
        'customer_notes', 'internal_notes', 'communication_log',
        
        // Agent and commission info
        'agent_id', 'agent_commission', 'platform_commission',
        
        // Emergency contact
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
        
        // Documentation and files
        'required_documents', 'uploaded_documents', 'documents_verified',
        
        // Reviews and feedback
        'rating', 'review', 'reviewed_at',
        
        // Metadata and tracking
        'metadata', 'referral_source', 'booking_ip', 'user_agent',
    ];

    protected $casts = [
        // Dates
        'departure_date' => 'date',
        'return_date' => 'date',
        'confirmed_at' => 'datetime',
        'payment_due_date' => 'datetime',
        'departure_reminder_sent_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        
        // Decimals
        'package_price' => 'decimal:2',
        'addon_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
        'agent_commission' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        
        // Arrays/JSON
        'participant_details' => 'array',
        'pricing_breakdown' => 'array',
        'payment_details' => 'array',
        'cancellation_policy' => 'array',
        'selected_addons' => 'array',
        'accommodation_preferences' => 'array',
        'transport_preferences' => 'array',
        'communication_log' => 'array',
        'required_documents' => 'array',
        'uploaded_documents' => 'array',
        'metadata' => 'array',
        
        // Booleans
        'documents_verified' => 'boolean',
        
        // Integers
        'adults' => 'integer',
        'children' => 'integer',
        'infants' => 'integer',
        'total_participants' => 'integer',
        'duration_days' => 'integer',
        'rating' => 'integer',
    ];

    /**
     * Booking statuses
     */
    public const STATUSES = [
        'pending' => 'Pending Confirmation',
        'confirmed' => 'Confirmed',
        'in_progress' => 'Trip In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ];

    /**
     * Payment statuses
     */
    public const PAYMENT_STATUSES = [
        'pending' => 'Payment Pending',
        'partial' => 'Partially Paid',
        'paid' => 'Fully Paid',
        'refunded' => 'Refunded',
        'failed' => 'Payment Failed',
    ];

    /**
     * Payment methods
     */
    public const PAYMENT_METHODS = [
        'card' => 'Credit/Debit Card',
        'bank_transfer' => 'Bank Transfer',
        'cash' => 'Cash Payment',
        'paypal' => 'PayPal',
        'stripe' => 'Stripe',
        'other' => 'Other',
    ];

    /**
     * Boot method to set defaults
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            // Generate unique booking reference
            if (!$booking->booking_reference) {
                $booking->booking_reference = 'PKG-' . strtoupper(uniqid());
            }
            
            // Calculate total participants
            $booking->total_participants = $booking->adults + $booking->children + $booking->infants;
            
            // Calculate pending amount
            $booking->pending_amount = $booking->total_amount - $booking->paid_amount;
            
            // Set payment due date (default to 7 days before departure)
            if (!$booking->payment_due_date && $booking->departure_date) {
                $booking->payment_due_date = Carbon::parse($booking->departure_date)->subDays(7);
            }
        });

        static::updating(function ($booking) {
            // Update pending amount
            $booking->pending_amount = $booking->total_amount - $booking->paid_amount;
            
            // Update payment status based on amounts
            if ($booking->paid_amount <= 0) {
                $booking->payment_status = 'pending';
            } elseif ($booking->paid_amount >= $booking->total_amount) {
                $booking->payment_status = 'paid';
            } else {
                $booking->payment_status = 'partial';
            }
        });
    }

    /**
     * Get the package that owns the booking
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the customer for this booking
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the travel agent for this booking
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get all payments for this booking
     */
    public function payments(): HasMany
    {
        return $this->hasMany(BookingPayment::class);
    }

    /**
     * Scope for active bookings
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['confirmed', 'in_progress']);
    }

    /**
     * Scope for pending bookings
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed bookings
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for cancelled bookings
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for bookings by travel agent
     */
    public function scopeForAgent($query, $agentId)
    {
        return $query->whereHas('package', function ($q) use ($agentId) {
            $q->where('creator_id', $agentId);
        });
    }

    /**
     * Scope for upcoming departures
     */
    public function scopeUpcoming($query)
    {
        return $query->where('departure_date', '>=', now()->toDateString());
    }

    /**
     * Scope for overdue payments
     */
    public function scopeOverduePayments($query)
    {
        return $query->where('payment_status', '!=', 'paid')
                    ->where('payment_due_date', '<', now());
    }

    /**
     * Get formatted booking reference
     */
    public function getFormattedReferenceAttribute()
    {
        return $this->booking_reference;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'confirmed' => 'badge-success',
            'in_progress' => 'badge-info',
            'completed' => 'badge-primary',
            'cancelled' => 'badge-danger',
            'refunded' => 'badge-secondary',
            default => 'badge-light',
        };
    }

    /**
     * Get payment status badge class
     */
    public function getPaymentStatusBadgeClassAttribute()
    {
        return match($this->payment_status) {
            'pending' => 'badge-warning',
            'partial' => 'badge-info',
            'paid' => 'badge-success',
            'refunded' => 'badge-secondary',
            'failed' => 'badge-danger',
            default => 'badge-light',
        };
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled()
    {
        return !in_array($this->status, ['completed', 'cancelled', 'refunded']) &&
               $this->departure_date > now()->addDays(1);
    }

    /**
     * Check if booking can be confirmed
     */
    public function canBeConfirmed()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if booking requires payment
     */
    public function requiresPayment()
    {
        return $this->payment_status !== 'paid' && $this->pending_amount > 0;
    }

    /**
     * Get days until departure
     */
    public function getDaysUntilDepartureAttribute()
    {
        return now()->diffInDays($this->departure_date, false);
    }

    /**
     * Add entry to communication log
     */
    public function addToCommunicationLog($message, $type = 'note', $userId = null)
    {
        $log = $this->communication_log ?? [];
        $log[] = [
            'timestamp' => now()->toISOString(),
            'type' => $type,
            'message' => $message,
            'user_id' => $userId ?? auth()->id(),
            'user_name' => $userId ? User::find($userId)?->name : auth()->user()?->name,
        ];
        
        $this->update(['communication_log' => $log]);
    }
}
