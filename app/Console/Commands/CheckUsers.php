<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check user data and roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Total users: ' . User::count());
        
        $roles = User::select('role', DB::raw('count(*) as count'))
                    ->whereNotNull('role')
                    ->groupBy('role')
                    ->get();
        
        $this->info('Roles breakdown:');
        foreach ($roles as $role) {
            $this->line('  ' . $role->role . ': ' . $role->count);
        }
        
        $this->info('\nPartner counts:');
        $this->line('  All partners: ' . User::partners()->count());
        $this->line('  Active partners: ' . User::partners()->active()->count());
        $this->line('  Pending partners: ' . User::partners()->pending()->count());
        $this->line('  Suspended partners: ' . User::partners()->suspended()->count());
        
        $this->info('\nSample partner data:');
        $partners = User::partners()->take(3)->get(['id', 'name', 'email', 'role', 'status']);
        foreach ($partners as $partner) {
            $this->line('  ID: ' . $partner->id . ' | ' . $partner->name . ' (' . $partner->role . ') - ' . $partner->status);
        }
    }
}
