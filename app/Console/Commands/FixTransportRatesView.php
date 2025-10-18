<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\View;

class FixTransportRatesView extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:transport-rates-view';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix transport rates view issues and clear caches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Fixing Transport Rates View Issues...');
        $this->newLine();
        
        // Step 1: Clear all caches
        $this->info('1. Clearing application caches...');
        
        try {
            Artisan::call('cache:clear');
            $this->info('   ✅ Application cache cleared');
        } catch (\Exception $e) {
            $this->warn('   ⚠️  Application cache clear failed: ' . $e->getMessage());
        }
        
        try {
            Artisan::call('view:clear');
            $this->info('   ✅ View cache cleared');
        } catch (\Exception $e) {
            $this->warn('   ⚠️  View cache clear failed: ' . $e->getMessage());
        }
        
        try {
            Artisan::call('route:clear');
            $this->info('   ✅ Route cache cleared');
        } catch (\Exception $e) {
            $this->warn('   ⚠️  Route cache clear failed: ' . $e->getMessage());
        }
        
        try {
            Artisan::call('config:clear');
            $this->info('   ✅ Config cache cleared');
        } catch (\Exception $e) {
            $this->warn('   ⚠️  Config cache clear failed: ' . $e->getMessage());
        }
        
        $this->newLine();
        
        // Step 2: Validate view file exists and is readable
        $this->info('2. Validating view file...');
        $viewPath = resource_path('views/b2b/transport-provider/rates.blade.php');
        
        if (file_exists($viewPath)) {
            $this->info('   ✅ View file exists: ' . $viewPath);
            
            if (is_readable($viewPath)) {
                $this->info('   ✅ View file is readable');
                
                // Check view content for issues
                $content = file_get_contents($viewPath);
                if (strpos($content, '$services') !== false) {
                    $this->info('   ✅ View references $services variable correctly');
                } else {
                    $this->warn('   ⚠️  View does not reference $services variable');
                }
            } else {
                $this->error('   ❌ View file is not readable');
            }
        } else {
            $this->error('   ❌ View file does not exist');
        }
        
        $this->newLine();
        
        // Step 3: Test view compilation
        $this->info('3. Testing view compilation...');
        
        try {
            // Create test data
            $testServices = collect([]);
            $testPricingRules = collect([]);
            
            // Try to compile the view
            $view = View::make('b2b.transport-provider.rates', [
                'services' => $testServices,
                'pricingRules' => $testPricingRules
            ]);
            
            // This will throw an exception if there are syntax errors
            $rendered = $view->render();
            
            $this->info('   ✅ View compiled successfully');
            $this->info('   ✅ Rendered content length: ' . strlen($rendered) . ' characters');
            
        } catch (\Exception $e) {
            $this->error('   ❌ View compilation failed: ' . $e->getMessage());
            $this->error('   Error in file: ' . $e->getFile() . ' at line ' . $e->getLine());
        }
        
        $this->newLine();
        
        // Step 4: Provide access instructions
        $this->info('4. Access Instructions:');
        $this->info('   ✅ Route: /b2b/transport-provider/transport-rates');
        $this->info('   ✅ Login as transport provider: transport.provider@example.com');
        $this->info('   ✅ Password: password');
        
        $this->newLine();
        
        // Step 5: Test the login credentials
        $this->info('5. Testing transport provider credentials...');
        
        $provider = \App\Models\User::where('email', 'transport.provider@example.com')
                                   ->where('role', 'transport_provider')
                                   ->first();
        
        if ($provider) {
            $this->info('   ✅ Transport provider account found: ' . $provider->name);
            $this->info('   ✅ Role: ' . $provider->role);
            $this->info('   ✅ Status: ' . $provider->status);
        } else {
            $this->warn('   ⚠️  Transport provider account not found');
            
            // Find any transport provider
            $anyProvider = \App\Models\User::where('role', 'transport_provider')->first();
            if ($anyProvider) {
                $this->info('   ✅ Alternative transport provider found: ' . $anyProvider->email);
                $this->info('   ✅ Use this email instead: ' . $anyProvider->email);
            }
        }
        
        $this->newLine();
        $this->info('🎉 Transport rates view fix completed!');
        $this->info('🚀 Try accessing the route now: /b2b/transport-provider/transport-rates');
    }
}
