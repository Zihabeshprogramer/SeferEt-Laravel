<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * AdAnalyticsDaily Model
 * 
 * Stores aggregated daily analytics for ads.
 * This pre-aggregated data improves query performance for reports.
 */
class AdAnalyticsDaily extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ad_analytics_daily';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'ad_id',
        'date',
        'impressions',
        'clicks',
        'ctr',
        'unique_users',
        'unique_sessions',
        'conversions',
        'conversion_rate',
        'device_breakdown',
        'placement_breakdown',
        'avg_position',
        'total_display_time_seconds',
        'cost',
        'cpc',
        'cpm',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date' => 'date',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'ctr' => 'decimal:2',
        'unique_users' => 'integer',
        'unique_sessions' => 'integer',
        'conversions' => 'integer',
        'conversion_rate' => 'decimal:2',
        'device_breakdown' => 'array',
        'placement_breakdown' => 'array',
        'avg_position' => 'decimal:2',
        'total_display_time_seconds' => 'integer',
        'cost' => 'decimal:2',
        'cpc' => 'decimal:2',
        'cpm' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ===========================================
    // RELATIONSHIPS
    // ===========================================

    /**
     * Get the ad for this analytics record
     */
    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    // ===========================================
    // AGGREGATION METHODS
    // ===========================================

    /**
     * Aggregate analytics for a specific ad and date
     */
    public static function aggregateForDate(int $adId, string $date): self
    {
        $startOfDay = $date . ' 00:00:00';
        $endOfDay = $date . ' 23:59:59';

        // Count impressions
        $impressions = AdImpression::where('ad_id', $adId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->count();

        // Count unique users and sessions for impressions
        $uniqueUsers = AdImpression::where('ad_id', $adId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        $uniqueSessions = AdImpression::where('ad_id', $adId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->whereNotNull('session_id')
            ->distinct('session_id')
            ->count('session_id');

        // Count clicks
        $clicks = AdClick::where('ad_id', $adId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->count();

        // Count conversions
        $conversions = AdClick::where('ad_id', $adId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('converted', true)
            ->count();

        // Calculate CTR
        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;

        // Calculate conversion rate
        $conversionRate = $clicks > 0 ? round(($conversions / $clicks) * 100, 2) : 0;

        // Device breakdown
        $deviceBreakdown = AdImpression::where('ad_id', $adId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->whereNotNull('device_type')
            ->select('device_type', DB::raw('count(*) as count'))
            ->groupBy('device_type')
            ->pluck('count', 'device_type')
            ->toArray();

        // Placement breakdown
        $placementBreakdown = AdImpression::where('ad_id', $adId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->whereNotNull('placement')
            ->select('placement', DB::raw('count(*) as count'))
            ->groupBy('placement')
            ->pluck('count', 'placement')
            ->toArray();

        // Calculate cost metrics (if ad has budget/cost tracking)
        $ad = Ad::find($adId);
        $cost = 0;
        $cpc = 0;
        $cpm = 0;

        if ($ad && $ad->budget && $ad->spent) {
            // Simple cost allocation for the day based on impressions
            $totalImpressions = $ad->impressions_count;
            if ($totalImpressions > 0) {
                $cost = ($ad->spent / $totalImpressions) * $impressions;
                $cpc = $clicks > 0 ? round($cost / $clicks, 2) : 0;
                $cpm = $impressions > 0 ? round(($cost / $impressions) * 1000, 2) : 0;
            }
        }

        // Create or update the analytics record
        return static::updateOrCreate(
            [
                'ad_id' => $adId,
                'date' => $date,
            ],
            [
                'impressions' => $impressions,
                'clicks' => $clicks,
                'ctr' => $ctr,
                'unique_users' => $uniqueUsers,
                'unique_sessions' => $uniqueSessions,
                'conversions' => $conversions,
                'conversion_rate' => $conversionRate,
                'device_breakdown' => $deviceBreakdown,
                'placement_breakdown' => $placementBreakdown,
                'cost' => $cost,
                'cpc' => $cpc,
                'cpm' => $cpm,
            ]
        );
    }

    /**
     * Aggregate analytics for all ads for a specific date
     */
    public static function aggregateAllForDate(string $date): int
    {
        $startOfDay = $date . ' 00:00:00';
        $endOfDay = $date . ' 23:59:59';

        // Get all unique ad IDs that had activity on this date
        $adIdsFromImpressions = AdImpression::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->distinct('ad_id')
            ->pluck('ad_id');

        $adIdsFromClicks = AdClick::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->distinct('ad_id')
            ->pluck('ad_id');

        $adIds = $adIdsFromImpressions->merge($adIdsFromClicks)->unique();

        $aggregatedCount = 0;

        foreach ($adIds as $adId) {
            static::aggregateForDate($adId, $date);
            $aggregatedCount++;
        }

        return $aggregatedCount;
    }

    // ===========================================
    // QUERY SCOPES
    // ===========================================

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for specific ad
     */
    public function scopeForAd($query, int $adId)
    {
        return $query->where('ad_id', $adId);
    }

    /**
     * Scope for ads by owner
     */
    public function scopeByOwner($query, int $ownerId, ?string $ownerType = null)
    {
        return $query->whereHas('ad', function ($q) use ($ownerId, $ownerType) {
            $q->where('owner_id', $ownerId);
            if ($ownerType) {
                $q->where('owner_type', $ownerType);
            }
        });
    }

    // ===========================================
    // SUMMARY METHODS
    // ===========================================

    /**
     * Get summary statistics for a date range
     */
    public static function getSummary(string $startDate, string $endDate, ?int $adId = null): array
    {
        $query = static::whereBetween('date', [$startDate, $endDate]);

        if ($adId) {
            $query->where('ad_id', $adId);
        }

        $totalImpressions = $query->sum('impressions');
        $totalClicks = $query->sum('clicks');
        $totalConversions = $query->sum('conversions');
        $avgCtr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;
        $avgConversionRate = $totalClicks > 0 ? round(($totalConversions / $totalClicks) * 100, 2) : 0;

        return [
            'total_impressions' => $totalImpressions,
            'total_clicks' => $totalClicks,
            'total_conversions' => $totalConversions,
            'average_ctr' => $avgCtr,
            'average_conversion_rate' => $avgConversionRate,
            'total_cost' => $query->sum('cost'),
            'average_cpc' => $query->avg('cpc'),
            'average_cpm' => $query->avg('cpm'),
        ];
    }

    /**
     * Get trending data (comparing with previous period)
     */
    public static function getTrends(string $startDate, string $endDate, ?int $adId = null): array
    {
        $currentPeriod = static::getSummary($startDate, $endDate, $adId);

        // Calculate previous period dates
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $days = $start->diffInDays($end) + 1;
        
        $prevEnd = $start->copy()->subDay()->format('Y-m-d');
        $prevStart = $start->copy()->subDays($days)->format('Y-m-d');
        
        $previousPeriod = static::getSummary($prevStart, $prevEnd, $adId);

        // Calculate percentage changes
        $calculateChange = function ($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100 : 0;
            }
            return round((($current - $previous) / $previous) * 100, 2);
        };

        return [
            'current' => $currentPeriod,
            'previous' => $previousPeriod,
            'changes' => [
                'impressions' => $calculateChange($currentPeriod['total_impressions'], $previousPeriod['total_impressions']),
                'clicks' => $calculateChange($currentPeriod['total_clicks'], $previousPeriod['total_clicks']),
                'conversions' => $calculateChange($currentPeriod['total_conversions'], $previousPeriod['total_conversions']),
                'ctr' => $calculateChange($currentPeriod['average_ctr'], $previousPeriod['average_ctr']),
            ],
        ];
    }
}
