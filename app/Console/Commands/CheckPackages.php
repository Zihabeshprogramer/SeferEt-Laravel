<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Package;
use Illuminate\Support\Facades\DB;

class CheckPackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-packages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check package data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Total packages: ' . Package::count());
        
        $statuses = Package::select('status', DB::raw('count(*) as count'))
                    ->whereNotNull('status')
                    ->groupBy('status')
                    ->get();
        
        $this->info('Status breakdown:');
        foreach ($statuses as $status) {
            $this->line('  ' . $status->status . ': ' . $status->count);
        }
        
        $approvalStatuses = Package::select('approval_status', DB::raw('count(*) as count'))
                    ->whereNotNull('approval_status')
                    ->groupBy('approval_status')
                    ->get();
        
        $this->info('Approval status breakdown:');
        foreach ($approvalStatuses as $approvalStatus) {
            $this->line('  ' . $approvalStatus->approval_status . ': ' . $approvalStatus->count);
        }
        
        $this->info('\nSample package data:');
        $packages = Package::with('creator:id,name')->take(5)->get(['id', 'name', 'creator_id', 'status', 'approval_status', 'created_at']);
        foreach ($packages as $package) {
            $creator = $package->creator ? $package->creator->name : 'No creator';
            $this->line('  ID: ' . $package->id . ' | ' . $package->name . ' | Creator: ' . $creator . ' | Status: ' . $package->status . ' | Approval: ' . $package->approval_status);
        }
    }
}
