<?php

namespace App\Jobs;

use App\Models\AdAnalyticsDaily;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Aggregate Ad Analytics Job
 * 
 * Aggregates impression and click data into daily analytics records.
 * Designed to run daily via scheduler for the previous day's data.
 */
class AggregateAdAnalytics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The date to aggregate (Y-m-d format)
     */
    protected string $date;

    /**
     * Optional specific ad ID to aggregate
     */
    protected ?int $adId;

    /**
     * Number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * Number of seconds the job can run before timing out.
     */
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(?string $date = null, ?int $adId = null)
    {
        // Default to yesterday if no date provided
        $this->date = $date ?? now()->subDay()->format('Y-m-d');
        $this->adId = $adId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting ad analytics aggregation', [
            'date' => $this->date,
            'ad_id' => $this->adId,
        ]);

        $startTime = microtime(true);

        try {
            if ($this->adId) {
                // Aggregate specific ad
                AdAnalyticsDaily::aggregateForDate($this->adId, $this->date);
                $count = 1;
            } else {
                // Aggregate all ads
                $count = AdAnalyticsDaily::aggregateAllForDate($this->date);
            }

            $duration = round(microtime(true) - $startTime, 2);

            Log::info('Completed ad analytics aggregation', [
                'date' => $this->date,
                'ads_processed' => $count,
                'duration_seconds' => $duration,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to aggregate ad analytics', [
                'date' => $this->date,
                'ad_id' => $this->adId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to allow retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Ad analytics aggregation job failed permanently', [
            'date' => $this->date,
            'ad_id' => $this->adId,
            'error' => $exception->getMessage(),
        ]);

        // Could send notification to admins here
    }
}
