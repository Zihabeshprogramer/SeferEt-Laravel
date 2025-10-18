<?php

namespace App\Console\Commands;

use App\Events\ServiceRequestExpired;
use App\Models\ServiceRequest;
use App\Notifications\ServiceRequestExpiredNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireServiceRequests extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'service-requests:expire';

    /**
     * The console command description.
     */
    protected $description = 'Mark expired service requests and send notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('Starting service request expiration process...');

            // Find all pending service requests that have expired
            $expiredRequests = ServiceRequest::with(['package', 'agent', 'provider'])
                ->where('status', ServiceRequest::STATUS_PENDING)
                ->where('expires_at', '<=', now())
                ->get();

            if ($expiredRequests->isEmpty()) {
                $this->info('No expired service requests found.');
                return 0;
            }

            $count = $expiredRequests->count();
            $this->info("Found {$count} expired service requests. Processing...");

            DB::beginTransaction();

            foreach ($expiredRequests as $serviceRequest) {
                try {
                    // Update status to expired
                    $serviceRequest->update([
                        'status' => ServiceRequest::STATUS_EXPIRED,
                        'expired_at' => now(),
                    ]);

                    // Send notifications to both agent and provider
                    $serviceRequest->agent->notify(new ServiceRequestExpiredNotification($serviceRequest));
                    $serviceRequest->provider->notify(new ServiceRequestExpiredNotification($serviceRequest));

                    // Broadcast real-time event
                    broadcast(new ServiceRequestExpired($serviceRequest));

                    $this->line("Expired request ID: {$serviceRequest->id} (UUID: {$serviceRequest->uuid})");

                } catch (\Exception $e) {
                    Log::error('Error processing expired service request', [
                        'service_request_id' => $serviceRequest->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    $this->error("Failed to process request ID: {$serviceRequest->id}");
                }
            }

            DB::commit();

            $this->info("Successfully processed {$count} expired service requests.");
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error in service request expiration process', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->error('Failed to process expired service requests: ' . $e->getMessage());
            return 1;
        }
    }
}