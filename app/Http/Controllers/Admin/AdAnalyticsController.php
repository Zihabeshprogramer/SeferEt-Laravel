<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdAnalyticsDaily;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Ad Analytics Controller
 * 
 * Handles admin analytics reporting, filtering, and export functionality.
 */
class AdAnalyticsController extends Controller
{
    /**
     * Display analytics dashboard
     */
    public function index(Request $request)
    {
        // Default date range: last 30 days
        $defaultEndDate = now()->format('Y-m-d');
        $defaultStartDate = now()->subDays(29)->format('Y-m-d');

        $filters = [
            'start_date' => $request->input('start_date', $defaultStartDate),
            'end_date' => $request->input('end_date', $defaultEndDate),
            'ad_id' => $request->input('ad_id'),
            'owner_id' => $request->input('owner_id'),
            'owner_type' => $request->input('owner_type'),
            'status' => $request->input('status'),
        ];

        // Cache key based on filters
        $cacheKey = 'ad_analytics_' . md5(json_encode($filters));

        // Cache for 5 minutes
        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters) {
            return $this->getAnalyticsData($filters);
        });

        // Get filter options
        $ads = Ad::select('id', 'title', 'owner_id', 'owner_type')
            ->orderBy('title')
            ->get();

        $owners = User::select('id', 'name')
            ->whereHas('ownedAds')
            ->orderBy('name')
            ->get();

        return view('admin.ads.analytics.index', compact('data', 'filters', 'ads', 'owners'));
    }

    /**
     * Get analytics data with filters
     */
    protected function getAnalyticsData(array $filters): array
    {
        $query = AdAnalyticsDaily::with('ad.owner')
            ->whereBetween('date', [$filters['start_date'], $filters['end_date']]);

        // Apply filters
        if (!empty($filters['ad_id'])) {
            $query->where('ad_id', $filters['ad_id']);
        }

        if (!empty($filters['owner_id'])) {
            $query->whereHas('ad', function ($q) use ($filters) {
                $q->where('owner_id', $filters['owner_id']);
                if (!empty($filters['owner_type'])) {
                    $q->where('owner_type', $filters['owner_type']);
                }
            });
        }

        if (!empty($filters['status'])) {
            $query->whereHas('ad', function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            });
        }

        // Get daily data for charts
        $dailyData = $query->orderBy('date')
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
            })->values();

        // Get summary stats
        $summary = AdAnalyticsDaily::getSummary(
            $filters['start_date'],
            $filters['end_date'],
            $filters['ad_id'] ?? null
        );

        // Get trends
        $trends = AdAnalyticsDaily::getTrends(
            $filters['start_date'],
            $filters['end_date'],
            $filters['ad_id'] ?? null
        );

        // Get top performing ads
        $topAds = $this->getTopPerformingAds($filters);

        // Get device breakdown
        $deviceBreakdown = $this->getDeviceBreakdown($filters);

        // Get placement breakdown
        $placementBreakdown = $this->getPlacementBreakdown($filters);

        return [
            'daily_data' => $dailyData,
            'summary' => $summary,
            'trends' => $trends,
            'top_ads' => $topAds,
            'device_breakdown' => $deviceBreakdown,
            'placement_breakdown' => $placementBreakdown,
        ];
    }

    /**
     * Get top performing ads
     */
    protected function getTopPerformingAds(array $filters, int $limit = 10): array
    {
        $query = AdAnalyticsDaily::with('ad')
            ->whereBetween('date', [$filters['start_date'], $filters['end_date']]);

        if (!empty($filters['owner_id'])) {
            $query->whereHas('ad', function ($q) use ($filters) {
                $q->where('owner_id', $filters['owner_id']);
            });
        }

        return $query->select('ad_id', 
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('SUM(conversions) as total_conversions'),
                DB::raw('AVG(ctr) as avg_ctr'))
            ->groupBy('ad_id')
            ->orderByDesc('total_clicks')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get device breakdown
     */
    protected function getDeviceBreakdown(array $filters): array
    {
        $query = AdAnalyticsDaily::whereBetween('date', [$filters['start_date'], $filters['end_date']]);

        if (!empty($filters['ad_id'])) {
            $query->where('ad_id', $filters['ad_id']);
        }

        $analytics = $query->get();

        $breakdown = [];
        foreach ($analytics as $record) {
            if ($record->device_breakdown) {
                foreach ($record->device_breakdown as $device => $count) {
                    $breakdown[$device] = ($breakdown[$device] ?? 0) + $count;
                }
            }
        }

        return $breakdown;
    }

    /**
     * Get placement breakdown
     */
    protected function getPlacementBreakdown(array $filters): array
    {
        $query = AdAnalyticsDaily::whereBetween('date', [$filters['start_date'], $filters['end_date']]);

        if (!empty($filters['ad_id'])) {
            $query->where('ad_id', $filters['ad_id']);
        }

        $analytics = $query->get();

        $breakdown = [];
        foreach ($analytics as $record) {
            if ($record->placement_breakdown) {
                foreach ($record->placement_breakdown as $placement => $count) {
                    $breakdown[$placement] = ($breakdown[$placement] ?? 0) + $count;
                }
            }
        }

        return $breakdown;
    }

    /**
     * View detailed analytics for a specific ad
     */
    public function show(Request $request, Ad $ad)
    {
        $defaultEndDate = now()->format('Y-m-d');
        $defaultStartDate = now()->subDays(29)->format('Y-m-d');

        $filters = [
            'start_date' => $request->input('start_date', $defaultStartDate),
            'end_date' => $request->input('end_date', $defaultEndDate),
            'ad_id' => $ad->id,
        ];

        $cacheKey = 'ad_analytics_detail_' . $ad->id . '_' . md5(json_encode($filters));

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters) {
            return $this->getAnalyticsData($filters);
        });

        return view('admin.ads.analytics.show', compact('ad', 'data', 'filters'));
    }

    /**
     * Export analytics to CSV
     */
    public function export(Request $request)
    {
        $filters = [
            'start_date' => $request->input('start_date', now()->subDays(29)->format('Y-m-d')),
            'end_date' => $request->input('end_date', now()->format('Y-m-d')),
            'ad_id' => $request->input('ad_id'),
            'owner_id' => $request->input('owner_id'),
            'owner_type' => $request->input('owner_type'),
        ];

        $query = AdAnalyticsDaily::with(['ad.owner'])
            ->whereBetween('date', [$filters['start_date'], $filters['end_date']]);

        if (!empty($filters['ad_id'])) {
            $query->where('ad_id', $filters['ad_id']);
        }

        if (!empty($filters['owner_id'])) {
            $query->whereHas('ad', function ($q) use ($filters) {
                $q->where('owner_id', $filters['owner_id']);
                if (!empty($filters['owner_type'])) {
                    $q->where('owner_type', $filters['owner_type']);
                }
            });
        }

        $data = $query->orderBy('date', 'desc')->get();

        $filename = 'ad-analytics-' . $filters['start_date'] . '-to-' . $filters['end_date'] . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'Date',
                'Ad ID',
                'Ad Title',
                'Owner ID',
                'Owner Name',
                'Owner Type',
                'Impressions',
                'Clicks',
                'CTR (%)',
                'Conversions',
                'Conversion Rate (%)',
                'Unique Users',
                'Unique Sessions',
                'Cost',
                'CPC',
                'CPM',
            ]);

            // CSV Data
            foreach ($data as $record) {
                fputcsv($file, [
                    $record->date->format('Y-m-d'),
                    $record->ad_id,
                    $record->ad->title ?? 'N/A',
                    $record->ad->owner_id ?? 'N/A',
                    $record->ad->owner->name ?? 'N/A',
                    $record->ad->owner_type ?? 'N/A',
                    $record->impressions,
                    $record->clicks,
                    $record->ctr,
                    $record->conversions,
                    $record->conversion_rate,
                    $record->unique_users,
                    $record->unique_sessions,
                    $record->cost,
                    $record->cpc,
                    $record->cpm,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get real-time analytics (not cached)
     */
    public function realtime(Request $request)
    {
        $adId = $request->input('ad_id');
        
        if (!$adId) {
            return response()->json(['error' => 'Ad ID required'], 400);
        }

        $ad = Ad::findOrFail($adId);

        // Get today's stats from raw tables (not aggregated yet)
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

        return response()->json([
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
        ]);
    }
}
