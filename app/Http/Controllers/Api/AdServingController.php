<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AdServingController extends Controller
{
    /**
     * Serve ads based on context and targeting criteria
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function serveAds(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'placement' => 'nullable|string',
            'device_type' => 'nullable|in:mobile,tablet,desktop',
            'region' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:10',
            'product_type' => 'nullable|string',
            'product_id' => 'nullable|integer',
        ]);

        $placement = $validated['placement'] ?? 'home_top';
        $deviceType = $validated['device_type'] ?? $this->detectDeviceType($request);
        $region = $validated['region'] ?? $this->detectRegion($request);
        $limit = $validated['limit'] ?? 3;

        // Create cache key
        $cacheKey = "ads_serve_{$placement}_{$deviceType}_{$region}_{$limit}";
        
        // Cache for 2 minutes (short TTL as required)
        $ads = Cache::remember($cacheKey, 120, function () use ($placement, $deviceType, $region, $limit, $validated) {
            $query = Ad::active()
                ->forPlacement($placement)
                ->forDevice($deviceType)
                ->forRegion($region);

            // Filter by product if specified
            if (!empty($validated['product_type']) && !empty($validated['product_id'])) {
                $query->byProduct($validated['product_id'], $validated['product_type']);
            }

            // Prioritize: local owners first, then by priority, then random
            $ads = $query->prioritized()
                ->limit($limit * 2) // Get more than needed for randomization
                ->get();

            // Add some randomness within priority groups
            $grouped = $ads->groupBy('priority');
            $selected = collect();

            foreach ($grouped as $priority => $group) {
                $selected = $selected->merge($group->shuffle());
            }

            return $selected->take($limit)->map(function ($ad) {
                return [
                    'id' => $ad->id,
                    'title' => $ad->title,
                    'description' => $ad->description,
                    'image_url' => $ad->image_url,
                    'image_variants' => $ad->image_variant_urls,
                    'cta_text' => $ad->cta_text,
                    'cta_action' => $ad->cta_action,
                    'cta_position' => $ad->cta_position,
                    'cta_style' => $ad->cta_style,
                    'placement' => $ad->placement,
                    'priority' => $ad->priority,
                    'is_local' => $ad->is_local_owner,
                    'tracking' => [
                        'impression_url' => route('api.ads.track.impression', $ad->id),
                        'click_url' => route('api.ads.track.click', $ad->id),
                    ],
                    'analytics_meta' => $ad->analytics_meta,
                ];
            });
        });

        return response()->json([
            'success' => true,
            'ads' => $ads,
            'meta' => [
                'placement' => $placement,
                'device_type' => $deviceType,
                'region' => $region,
                'count' => $ads->count(),
                'cached_until' => now()->addSeconds(120)->toISOString(),
            ],
        ]);
    }

    /**
     * Get single ad by ID
     */
    public function getAd(int $id): JsonResponse
    {
        $ad = Ad::active()->findOrFail($id);

        return response()->json([
            'success' => true,
            'ad' => [
                'id' => $ad->id,
                'title' => $ad->title,
                'description' => $ad->description,
                'image_url' => $ad->image_url,
                'image_variants' => $ad->image_variant_urls,
                'cta_text' => $ad->cta_text,
                'cta_action' => $ad->cta_action,
                'cta_position' => $ad->cta_position,
                'cta_style' => $ad->cta_style,
                'placement' => $ad->placement,
                'tracking' => [
                    'impression_url' => route('api.ads.track.impression', $ad->id),
                    'click_url' => route('api.ads.track.click', $ad->id),
                ],
            ],
        ]);
    }

    /**
     * Detect device type from user agent
     */
    protected function detectDeviceType(Request $request): string
    {
        $userAgent = $request->userAgent();
        
        if (preg_match('/mobile|android|iphone|ipod/i', $userAgent)) {
            return 'mobile';
        }
        
        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }
        
        return 'desktop';
    }

    /**
     * Detect region from IP or request headers
     */
    protected function detectRegion(Request $request): ?string
    {
        // Try to get from header first (if set by CDN/proxy)
        $region = $request->header('CF-IPCountry') ?? // Cloudflare
                  $request->header('X-Country-Code') ?? // Generic
                  null;

        // Could implement GeoIP lookup here
        // For now, return null to match all regions
        return $region;
    }
}
