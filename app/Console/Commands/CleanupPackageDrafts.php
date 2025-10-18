<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PackageDraft;

class CleanupPackageDrafts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drafts:cleanup-packages {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired package drafts older than 7 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting package drafts cleanup...');
        
        // Count drafts that will be expired
        $draftsToExpire = PackageDraft::where('is_expired', false)
            ->where(function($query) {
                $query->where('last_accessed_at', '<', now()->subDays(7))
                      ->orWhere('created_at', '<', now()->subDays(7));
            })
            ->count();

        if ($draftsToExpire === 0) {
            $this->info('No package drafts found for cleanup.');
            return 0;
        }

        $this->info("Found {$draftsToExpire} package drafts to expire.");

        if (!$this->option('force') && !$this->confirm('Do you want to continue?')) {
            $this->info('Cleanup cancelled.');
            return 0;
        }

        // Expire the drafts
        $expiredCount = PackageDraft::expireOldDrafts();

        $this->info("Successfully expired {$expiredCount} package drafts.");

        // Optionally delete expired drafts older than 30 days
        $oldExpiredDrafts = PackageDraft::expired()
            ->where('expires_at', '<', now()->subDays(30))
            ->count();

        if ($oldExpiredDrafts > 0) {
            $this->info("Found {$oldExpiredDrafts} expired drafts older than 30 days.");
            
            if ($this->option('force') || $this->confirm('Delete these old expired drafts permanently?')) {
                $deletedCount = PackageDraft::expired()
                    ->where('expires_at', '<', now()->subDays(30))
                    ->delete();
                
                $this->info("Deleted {$deletedCount} old expired drafts.");
            }
        }

        $this->info('Package drafts cleanup completed.');
        return 0;
    }
}
