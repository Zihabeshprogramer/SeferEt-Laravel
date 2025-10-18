<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ServiceRequest;
use App\Models\Allocation;
use App\Services\NotificationService;
use App\Services\ApprovalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessExpiredServiceRequests extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'service-requests:process-expired 
                          {--dry-run : Show what would be done without making changes}
                          {--batch-size=100 : Number of requests to process in each batch}';

    /**
     * The console command description.
     */
    protected $description = 'Process expired service requests and release allocations';

    protected NotificationService $notificationService;
    protected ApprovalService $approvalService;

    public function __construct(
        NotificationService $notificationService,
        ApprovalService $approvalService
    ) {
        parent::__construct();
        $this->notificationService = $notificationService;
        $this->approvalService = $approvalService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch-size');

        $this->info('Starting expired service request processing...');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        try {
            // Step 1: Send expiration reminders
            $this->sendExpirationReminders($dryRun);

            // Step 2: Process expired pending requests
            $expiredCount = $this->processExpiredRequests($dryRun, $batchSize);

            // Step 3: Release expired allocations
            $releasedAllocations = $this->releaseExpiredAllocations($dryRun, $batchSize);

            // Step 4: Clean up old notifications
            $cleanedNotifications = $this->cleanupOldData($dryRun);

            // Summary
            $this->info('Processing completed successfully:');
            $this->line("- Expired requests processed: {$expiredCount}");
            $this->line("- Allocations released: {$releasedAllocations}");
            $this->line("- Notifications cleaned: {$cleanedNotifications}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error processing expired service requests: ' . $e->getMessage());
            Log::error('ProcessExpiredServiceRequests command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Send expiration reminders for requests expiring soon
     */
    private function sendExpirationReminders(bool $dryRun): void
    {
        $this->info('Sending expiration reminders...');

        if ($dryRun) {
            $count = ServiceRequest::where('status', ServiceRequest::STATUS_PENDING)
                ->where('expires_at', '>', now())
                ->where('expires_at', '<=', now()->addHours(4))
                ->where('reminder_sent', false)
                ->count();
            
            $this->line("Would send reminders for {$count} requests");
            return;
        }

        $this->notificationService->sendExpirationReminders();
        $this->info('✓ Expiration reminders sent');
    }

    /**
     * Process expired pending requests
     */
    private function processExpiredRequests(bool $dryRun, int $batchSize): int
    {
        $this->info('Processing expired requests...');

        $totalProcessed = 0;

        do {
            $expiredRequests = ServiceRequest::with(['allocations', 'agent', 'provider'])
                ->where('status', ServiceRequest::STATUS_PENDING)
                ->where('expires_at', '<', now())
                ->limit($batchSize)
                ->get();

            if ($expiredRequests->isEmpty()) {
                break;
            }

            if ($dryRun) {
                $this->line("Would process {$expiredRequests->count()} expired requests:");
                foreach ($expiredRequests as $request) {
                    $this->line("  - ID: {$request->id}, Package: {$request->package_id}, Expired: {$request->expires_at}");
                }
            } else {
                foreach ($expiredRequests as $request) {
                    $this->processExpiredRequest($request);
                }
            }

            $totalProcessed += $expiredRequests->count();

            if (!$dryRun) {
                $this->line("Processed batch of {$expiredRequests->count()} expired requests");
            }

        } while ($expiredRequests->count() === $batchSize);

        $this->info("✓ Total expired requests processed: {$totalProcessed}");
        return $totalProcessed;
    }

    /**
     * Process individual expired request
     */
    private function processExpiredRequest(ServiceRequest $serviceRequest): void
    {
        try {
            DB::transaction(function () use ($serviceRequest) {
                // Release any allocations first
                foreach ($serviceRequest->allocations as $allocation) {
                    if ($allocation->status === Allocation::STATUS_ACTIVE) {
                        $this->approvalService->releaseAllocation($allocation, [
                            'reason' => 'Service request expired',
                            'auto_released' => true
                        ]);
                    }
                }

                // Update request status
                $serviceRequest->update([
                    'status' => ServiceRequest::STATUS_EXPIRED,
                    'expired_at' => now()
                ]);

                // Send notifications
                $this->notificationService->notifyServiceRequestExpired($serviceRequest);

                Log::info('Service request expired and processed', [
                    'service_request_id' => $serviceRequest->id,
                    'package_id' => $serviceRequest->package_id,
                    'allocations_released' => $serviceRequest->allocations->count()
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Failed to process expired service request', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage()
            ]);
            
            $this->error("Failed to process expired request ID: {$serviceRequest->id}");
        }
    }

    /**
     * Release expired allocations
     */
    private function releaseExpiredAllocations(bool $dryRun, int $batchSize): int
    {
        $this->info('Releasing expired allocations...');

        $totalReleased = 0;

        do {
            $expiredAllocations = Allocation::with('serviceRequest')
                ->where('status', Allocation::STATUS_ACTIVE)
                ->where('expires_at', '<', now())
                ->limit($batchSize)
                ->get();

            if ($expiredAllocations->isEmpty()) {
                break;
            }

            if ($dryRun) {
                $this->line("Would release {$expiredAllocations->count()} expired allocations:");
                foreach ($expiredAllocations as $allocation) {
                    $this->line("  - ID: {$allocation->id}, Request: {$allocation->service_request_id}, Expired: {$allocation->expires_at}");
                }
            } else {
                foreach ($expiredAllocations as $allocation) {
                    $result = $this->approvalService->releaseAllocation($allocation, [
                        'reason' => 'Allocation expired',
                        'auto_released' => true
                    ]);

                    if (!$result['success']) {
                        $this->warn("Failed to release allocation ID: {$allocation->id}");
                    }
                }
            }

            $totalReleased += $expiredAllocations->count();

            if (!$dryRun) {
                $this->line("Released batch of {$expiredAllocations->count()} expired allocations");
            }

        } while ($expiredAllocations->count() === $batchSize);

        $this->info("✓ Total expired allocations released: {$totalReleased}");
        return $totalReleased;
    }

    /**
     * Clean up old data
     */
    private function cleanupOldData(bool $dryRun): int
    {
        $this->info('Cleaning up old notifications...');

        if ($dryRun) {
            $cutoffDate = now()->subDays(30);
            $count = DB::table('notifications')
                ->where('created_at', '<', $cutoffDate)
                ->count();
            
            $this->line("Would clean up {$count} old notifications");
            return $count;
        }

        $cleanedCount = $this->notificationService->cleanupOldNotifications(30);
        $this->info("✓ Cleaned up {$cleanedCount} old notifications");
        
        return $cleanedCount;
    }

    /**
     * Display processing statistics
     */
    private function displayStatistics(): void
    {
        $this->info('Current system statistics:');

        $stats = [
            'pending_requests' => ServiceRequest::where('status', ServiceRequest::STATUS_PENDING)->count(),
            'expired_requests' => ServiceRequest::where('status', ServiceRequest::STATUS_EXPIRED)->count(),
            'active_allocations' => Allocation::where('status', Allocation::STATUS_ACTIVE)->count(),
            'requests_expiring_soon' => ServiceRequest::where('status', ServiceRequest::STATUS_PENDING)
                ->where('expires_at', '>', now())
                ->where('expires_at', '<=', now()->addHours(24))
                ->count(),
        ];

        $this->table(
            ['Metric', 'Count'],
            [
                ['Pending requests', $stats['pending_requests']],
                ['Expired requests', $stats['expired_requests']],
                ['Active allocations', $stats['active_allocations']],
                ['Expiring within 24h', $stats['requests_expiring_soon']],
            ]
        );

        // Show next few requests expiring
        $nextExpiring = ServiceRequest::with(['package', 'agent', 'provider'])
            ->where('status', ServiceRequest::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->orderBy('expires_at', 'asc')
            ->limit(5)
            ->get();

        if ($nextExpiring->isNotEmpty()) {
            $this->info('Next 5 requests expiring:');
            
            $expiringData = $nextExpiring->map(function ($request) {
                return [
                    'ID' => $request->id,
                    'Package' => $request->package_id,
                    'Provider' => $request->provider->name ?? 'Unknown',
                    'Expires At' => $request->expires_at->format('Y-m-d H:i:s'),
                    'Time Left' => $request->expires_at->diffForHumans()
                ];
            })->toArray();

            $this->table(
                ['ID', 'Package', 'Provider', 'Expires At', 'Time Left'],
                $expiringData
            );
        }
    }
}