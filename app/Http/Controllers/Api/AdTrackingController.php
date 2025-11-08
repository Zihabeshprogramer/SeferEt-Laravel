<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdImpression;
use App\Models\AdClick;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;

class AdTrackingController extends Controller
{
    /**
     * Track ad impression
     */
    public function trackImpression(Request $request, int $id): JsonResponse
    {
        // Rate limiting: max 60 impressions per minute per IP
        $key = 'ad-impression:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests',
            ], 429);
        }

        RateLimiter::hit($key, 60);

        try {
            $ad = Ad::findOrFail($id);

            // Prevent duplicate impressions in short timeframe
            $sessionId = session()->getId();
            if (AdImpression::recentlyTracked($id, $sessionId, 5)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Impression already tracked',
                    'duplicate' => true,
                ]);
            }

            // Validate request data
            $validated = $request->validate([
                'device_type' => 'nullable|string',
                'placement' => 'nullable|string',
            ]);

            // Record impression
            AdImpression::record(
                $id,
                auth()->id(),
                $sessionId,
                $validated['device_type'] ?? null,
                $validated['placement'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Impression tracked',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track impression',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal error',
            ], 500);
        }
    }

    /**
     * Track ad click
     */
    public function trackClick(Request $request, int $id): JsonResponse
    {
        // Rate limiting: max 30 clicks per minute per IP
        $key = 'ad-click:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests',
            ], 429);
        }

        RateLimiter::hit($key, 60);

        try {
            $ad = Ad::findOrFail($id);

            // Prevent duplicate clicks in short timeframe
            $sessionId = session()->getId();
            if (AdClick::recentlyTracked($id, $sessionId, 5)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Click already tracked',
                    'duplicate' => true,
                    'redirect_url' => $ad->cta_action,
                ]);
            }

            // Validate request data
            $validated = $request->validate([
                'device_type' => 'nullable|string',
                'placement' => 'nullable|string',
                'destination_url' => 'nullable|url|max:500',
            ]);

            // Record click
            AdClick::record(
                $id,
                $validated['destination_url'] ?? $ad->cta_action,
                auth()->id(),
                $sessionId,
                $validated['device_type'] ?? null,
                $validated['placement'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Click tracked',
                'redirect_url' => $ad->cta_action,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track click',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal error',
            ], 500);
        }
    }

    /**
     * Track ad conversion (when user completes desired action)
     */
    public function trackConversion(Request $request, int $adId, int $clickId): JsonResponse
    {
        try {
            $click = AdClick::where('ad_id', $adId)
                ->where('id', $clickId)
                ->firstOrFail();

            if (!$click->converted) {
                $click->markConverted();
            }

            return response()->json([
                'success' => true,
                'message' => 'Conversion tracked',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track conversion',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal error',
            ], 500);
        }
    }

    /**
     * Batch track impressions (for performance)
     */
    public function batchTrackImpressions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'impressions' => 'required|array|max:10',
            'impressions.*.ad_id' => 'required|integer|exists:ads,id',
            'impressions.*.device_type' => 'nullable|string',
            'impressions.*.placement' => 'nullable|string',
        ]);

        $tracked = [];
        $errors = [];
        $sessionId = session()->getId();

        foreach ($validated['impressions'] as $impression) {
            try {
                // Check if recently tracked
                if (AdImpression::recentlyTracked($impression['ad_id'], $sessionId, 5)) {
                    continue;
                }

                AdImpression::record(
                    $impression['ad_id'],
                    auth()->id(),
                    $sessionId,
                    $impression['device_type'] ?? null,
                    $impression['placement'] ?? null
                );

                $tracked[] = $impression['ad_id'];
            } catch (\Exception $e) {
                $errors[] = [
                    'ad_id' => $impression['ad_id'],
                    'error' => config('app.debug') ? $e->getMessage() : 'Failed to track',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'tracked_count' => count($tracked),
            'tracked_ads' => $tracked,
            'errors' => $errors,
        ]);
    }
}
