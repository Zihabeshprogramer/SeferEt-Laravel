<?php

namespace App\Jobs;

use App\Models\Ad;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAdScheduling implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->activateScheduledAds();
        $this->expireScheduledAds();
        $this->deactivateAdsReachedLimits();
    }

    /**
     * Activate ads that have reached their start_at time
     */
    protected function activateScheduledAds(): void
    {
        $ads = Ad::where('status', Ad::STATUS_APPROVED)
            ->where('is_active', false)
            ->whereNotNull('start_at')
            ->where('start_at', '<=', now())
            ->where(function($q) {
                $q->whereNull('end_at')
                  ->orWhere('end_at', '>', now());
            })
            ->get();

        foreach ($ads as $ad) {
            $ad->is_active = true;
            $ad->save();

            $ad->logAudit('auto_activated', null, [
                'activated_at' => now()->toISOString(),
                'reason' => 'Scheduled start time reached',
            ]);

            Log::info("Ad #{$ad->id} auto-activated", ['ad_id' => $ad->id, 'title' => $ad->title]);
        }

        if ($ads->count() > 0) {
            Log::info("Activated {$ads->count()} scheduled ads");
        }
    }

    /**
     * Deactivate ads that have passed their end_at time
     */
    protected function expireScheduledAds(): void
    {
        $ads = Ad::where('status', Ad::STATUS_APPROVED)
            ->where('is_active', true)
            ->whereNotNull('end_at')
            ->where('end_at', '<=', now())
            ->get();

        foreach ($ads as $ad) {
            $ad->is_active = false;
            $ad->save();

            $ad->logAudit('auto_expired', null, [
                'expired_at' => now()->toISOString(),
                'reason' => 'Scheduled end time reached',
            ]);

            Log::info("Ad #{$ad->id} auto-expired", ['ad_id' => $ad->id, 'title' => $ad->title]);

            // Optionally notify owner
            // if ($ad->owner) {
            //     $ad->owner->notify(new AdExpiredNotification($ad));
            // }
        }

        if ($ads->count() > 0) {
            Log::info("Expired {$ads->count()} scheduled ads");
        }
    }

    /**
     * Deactivate ads that have reached their impression or click limits
     */
    protected function deactivateAdsReachedLimits(): void
    {
        // Ads that reached impression limit
        $impressionLimitAds = Ad::where('status', Ad::STATUS_APPROVED)
            ->where('is_active', true)
            ->whereNotNull('max_impressions')
            ->whereRaw('impressions_count >= max_impressions')
            ->get();

        foreach ($impressionLimitAds as $ad) {
            $ad->is_active = false;
            $ad->save();

            $ad->logAudit('auto_deactivated', null, [
                'deactivated_at' => now()->toISOString(),
                'reason' => 'Maximum impressions reached',
                'impressions_count' => $ad->impressions_count,
                'max_impressions' => $ad->max_impressions,
            ]);

            Log::info("Ad #{$ad->id} deactivated - impression limit reached", [
                'ad_id' => $ad->id,
                'impressions' => $ad->impressions_count,
            ]);
        }

        // Ads that reached click limit
        $clickLimitAds = Ad::where('status', Ad::STATUS_APPROVED)
            ->where('is_active', true)
            ->whereNotNull('max_clicks')
            ->whereRaw('clicks_count >= max_clicks')
            ->get();

        foreach ($clickLimitAds as $ad) {
            $ad->is_active = false;
            $ad->save();

            $ad->logAudit('auto_deactivated', null, [
                'deactivated_at' => now()->toISOString(),
                'reason' => 'Maximum clicks reached',
                'clicks_count' => $ad->clicks_count,
                'max_clicks' => $ad->max_clicks,
            ]);

            Log::info("Ad #{$ad->id} deactivated - click limit reached", [
                'ad_id' => $ad->id,
                'clicks' => $ad->clicks_count,
            ]);
        }

        $totalDeactivated = $impressionLimitAds->count() + $clickLimitAds->count();
        if ($totalDeactivated > 0) {
            Log::info("Deactivated {$totalDeactivated} ads due to limit reached");
        }
    }
}
