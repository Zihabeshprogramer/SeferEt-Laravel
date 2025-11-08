<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\AdAnalyticsDaily;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * Ad Analytics Cache Service
 * 
 * Manages caching strategy for ad analytics to improve performance.
 * Uses Redis for fast access to frequently queried analytics data.
 */
class AdAnalyticsCacheService
{
    /**
     * Cache TTL in seconds
     */
    protected const CACHE_TTL_SHORT = 300; // 5 minutes
    protected const CACHE_TTL_MEDIUM = 1800; // 30 minutes
    protected const CACHE_TTL_LONG = 3600; // 1 hour
    protected const CACHE_TTL_DAILY = 86400; // 24 hours

    /**
     * Get cached summary for date range
     */
    public function getSummary(string $startDate, string $endDate, ?int $adId = null): array
    {
        $cacheKey = $this->makeSummaryKey($startDate, $endDate, $adId);
        
        return Cache::remember($cacheKey, self::CACHE_TTL_MEDIUM, function () use ($startDate, $endDate, $adId) {
            return AdAnalyticsDaily::getSummary($startDate, $endDate, $adId);
        });
    }

    /**
     * Get cached trends for date range
     */
    public function getTrends(string $startDate, string $endDate, ?int $adId = null): array
    {
        $cacheKey = $this->makeTrendsKey($startDate, $endDate, $adId);
        
        return Cache::remember($cacheKey, self::CACHE_TTL_MEDIUM, function () use ($startDate, $endDate, $adId) {
            return AdAnalyticsDaily::getTrends($startDate, $endDate, $adId);
        });
    }

    /**
     * Get cached daily data for charts
     */
    public function getDailyData(string $startDate, string $endDate, ?int $adId = null): array
    {
        $cacheKey = $this->makeDailyDataKey($startDate, $endDate, $adId);
        
        return Cache::remember($cacheKey, self::CACHE_TTL_MEDIUM, function () use ($startDate, $endDate, $adId) {
            $query = AdAnalyticsDaily::whereBetween('date', [$startDate, $endDate]);
            
            if ($adId) {
                $query->where('ad_id', $adId);
            }
            
            return $query->orderBy('date')
                ->get()
                ->groupBy('date')
                ->map(function ($group) {
                    return [
                        'date' => $group->first()->date->format('Y-m-d'),
                        'impressions' => $group->sum('impressions'),
                        'clicks' => $group->sum('clicks'),
                        'conversions' => $group->sum('conversions'),
                        'ctr' => $group->sum('impressions') > 0 
                            ? round(($group->sum('clicks') / $group->sum('impressions')) * 100, 2) 
                            : 0,
                    ];
                })
                ->values()
                ->toArray();
        });
    }

    /**
     * Get cached top performing ads
     */
    public function getTopAds(string $startDate, string $endDate, ?int $ownerId = null, int $limit = 10): array
    {
        $cacheKey = $this->makeTopAdsKey($startDate, $endDate, $ownerId, $limit);
        
        return Cache::remember($cacheKey, self::CACHE_TTL_LONG, function () use ($startDate, $endDate, $ownerId, $limit) {
            $query = AdAnalyticsDaily::with('ad')
                ->whereBetween('date', [$startDate, $endDate]);

            if ($ownerId) {
                $query->whereHas('ad', function ($q) use ($ownerId) {
                    $q->where('owner_id', $ownerId);
                });
            }

            return $query->select('ad_id', 
                    \DB::raw('SUM(impressions) as total_impressions'),
                    \DB::raw('SUM(clicks) as total_clicks'),
                    \DB::raw('SUM(conversions) as total_conversions'),
                    \DB::raw('AVG(ctr) as avg_ctr'))
                ->groupBy('ad_id')
                ->orderByDesc('total_clicks')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * Get real-time ad stats (today's unaggreated data)
     */
    public function getRealtimeStats(int $adId): array
    {
        $cacheKey = $this->makeRealtimeKey($adId);
        
        // Cache for only 1 minute for real-time data
        return Cache::remember($cacheKey, 60, function () use ($adId) {
            $ad = Ad::find($adId);
            if (!$ad) {
                return null;
            }

            $today = now()->format('Y-m-d');
            $startOfDay = $today . ' 00:00:00';
            $endOfDay = $today . ' 23:59:59';

            $impressionsToday = $ad->impressions()
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count();

            $clicksToday = $ad->clicks()
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count();

            $ctrToday = $impressionsToday > 0 
                ? round(($clicksToday / $impressionsToday) * 100, 2) 
                : 0;

            return [
                'ad_id' => $ad->id,
                'today' => [
                    'impressions' => $impressionsToday,
                    'clicks' => $clicksToday,
                    'ctr' => $ctrToday,
                ],
                'total' => [
                    'impressions' => $ad->impressions_count,
                    'clicks' => $ad->clicks_count,
                    'ctr' => $ad->ctr,
                ],
            ];
        });
    }

    /**
     * Invalidate cache for a specific ad
     */
    public function invalidateAdCache(int $adId): void
    {
        // Find all cache keys related to this ad
        $patterns = [
            "ad_analytics_summary_*_ad_{$adId}",
            "ad_analytics_trends_*_ad_{$adId}",
            "ad_analytics_daily_*_ad_{$adId}",
            "ad_analytics_realtime_{$adId}",
        ];

        foreach ($patterns as $pattern) {
            $this->deleteByPattern($pattern);
        }
    }

    /**
     * Invalidate all analytics cache
     */
    public function invalidateAll(): void
    {
        $patterns = [
            'ad_analytics_*',
        ];

        foreach ($patterns as $pattern) {
            $this->deleteByPattern($pattern);
        }
    }

    /**
     * Warm up cache for common date ranges
     */
    public function warmUpCache(?int $adId = null): void
    {
        $today = now()->format('Y-m-d');
        $dateRanges = [
            ['start' => now()->subDays(6)->format('Y-m-d'), 'end' => $today], // Last 7 days
            ['start' => now()->subDays(29)->format('Y-m-d'), 'end' => $today], // Last 30 days
            ['start' => now()->subDays(89)->format('Y-m-d'), 'end' => $today], // Last 90 days
        ];

        foreach ($dateRanges as $range) {
            $this->getSummary($range['start'], $range['end'], $adId);
            $this->getTrends($range['start'], $range['end'], $adId);
            $this->getDailyData($range['start'], $range['end'], $adId);
        }

        if (!$adId) {
            // Warm up top ads cache
            foreach ($dateRanges as $range) {
                $this->getTopAds($range['start'], $range['end']);
            }
        }
    }

    /**
     * Delete cache keys by pattern
     */
    protected function deleteByPattern(string $pattern): void
    {
        try {
            // Try Redis first (more efficient)
            if (config('cache.default') === 'redis') {
                $keys = Redis::keys($pattern);
                if (!empty($keys)) {
                    Redis::del($keys);
                }
            } else {
                // Fallback to Laravel Cache for other drivers
                // Note: Pattern matching is not supported by all cache drivers
                Cache::forget($pattern);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to delete cache by pattern', [
                'pattern' => $pattern,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Make cache key for summary
     */
    protected function makeSummaryKey(string $startDate, string $endDate, ?int $adId): string
    {
        $key = "ad_analytics_summary_{$startDate}_{$endDate}";
        if ($adId) {
            $key .= "_ad_{$adId}";
        }
        return $key;
    }

    /**
     * Make cache key for trends
     */
    protected function makeTrendsKey(string $startDate, string $endDate, ?int $adId): string
    {
        $key = "ad_analytics_trends_{$startDate}_{$endDate}";
        if ($adId) {
            $key .= "_ad_{$adId}";
        }
        return $key;
    }

    /**
     * Make cache key for daily data
     */
    protected function makeDailyDataKey(string $startDate, string $endDate, ?int $adId): string
    {
        $key = "ad_analytics_daily_{$startDate}_{$endDate}";
        if ($adId) {
            $key .= "_ad_{$adId}";
        }
        return $key;
    }

    /**
     * Make cache key for top ads
     */
    protected function makeTopAdsKey(string $startDate, string $endDate, ?int $ownerId, int $limit): string
    {
        $key = "ad_analytics_top_ads_{$startDate}_{$endDate}_limit_{$limit}";
        if ($ownerId) {
            $key .= "_owner_{$ownerId}";
        }
        return $key;
    }

    /**
     * Make cache key for realtime data
     */
    protected function makeRealtimeKey(int $adId): string
    {
        return "ad_analytics_realtime_{$adId}";
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        try {
            if (config('cache.default') === 'redis') {
                $keys = Redis::keys('ad_analytics_*');
                $totalKeys = count($keys);
                $totalSize = 0;

                foreach ($keys as $key) {
                    $ttl = Redis::ttl($key);
                    $totalSize += strlen(Redis::get($key) ?? '');
                }

                return [
                    'total_keys' => $totalKeys,
                    'total_size_bytes' => $totalSize,
                    'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                ];
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to get cache stats', [
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'total_keys' => 0,
            'total_size_bytes' => 0,
            'total_size_mb' => 0,
        ];
    }
}
