<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DemonstrateApprovalMessages extends Command
{
    protected $signature = 'demo:approval-messages';
    protected $description = 'Demonstrate the different approval response messages';

    public function handle()
    {
        $this->info('📋 Hotel Booking Approval Response Messages');
        $this->newLine();

        $this->info('Here are the different response messages users will now see:');
        $this->newLine();

        // Success scenario
        $this->info('✅ SUCCESS SCENARIO:');
        $this->info('Request: POST /b2b/hotel-provider/requests/{id}/approve');
        $this->info('Response when approval succeeds and booking is created:');
        $this->line('');
        $this->line('{');
        $this->line('  "success": true,');
        $this->line('  "message": "Request approved successfully and booking created automatically",');
        $this->line('  "allocation": { ... },');
        $this->line('  "booking": {');
        $this->line('    "created": true,');
        $this->line('    "id": 123,');
        $this->line('    "type": "hotel",');
        $this->line('    "reference": "HB-ABC123-241013"');
        $this->line('  }');
        $this->line('}');
        $this->newLine();

        // No rooms available scenario  
        $this->info('❌ NO ROOMS AVAILABLE SCENARIO:');
        $this->info('Response when no rooms are available during approval validation:');
        $this->line('');
        $this->line('{');
        $this->line('  "success": false,');
        $this->line('  "message": "No rooms available for the requested dates. Cannot approve this request.",');
        $this->line('  "error_code": "NO_ROOMS_AVAILABLE"');
        $this->line('}');
        $this->newLine();

        // Selected room unavailable scenario
        $this->info('🚫 SELECTED ROOM UNAVAILABLE SCENARIO:');
        $this->info('Response when a specific room is selected but no longer available:');
        $this->line('');
        $this->line('{');
        $this->line('  "success": false,');
        $this->line('  "message": "Selected room is no longer available for the requested dates.",');
        $this->line('  "error_code": "SELECTED_ROOM_UNAVAILABLE"');
        $this->line('}');
        $this->newLine();

        // Booking creation failed and reverted scenario
        $this->info('🔄 BOOKING FAILED - APPROVAL REVERTED SCENARIO:');
        $this->info('Response when approval succeeds but booking creation fails due to race condition:');
        $this->line('');
        $this->line('{');
        $this->line('  "success": false,');
        $this->line('  "message": "Request was approved but booking creation failed: No rooms available. The approval has been reverted.",');
        $this->line('  "error_code": "BOOKING_FAILED_NO_ROOMS",');
        $this->line('  "booking": {');
        $this->line('    "created": false,');
        $this->line('    "error": "Room 101 is no longer available for the requested dates...",');
        $this->line('    "error_code": "ROOM_NOT_AVAILABLE"');
        $this->line('  }');
        $this->line('}');
        $this->newLine();

        // Other booking errors scenario
        $this->info('⚠️ OTHER BOOKING ERRORS SCENARIO:');
        $this->info('Response when approval succeeds but booking fails for non-availability reasons:');
        $this->line('');
        $this->line('{');
        $this->line('  "success": true,');
        $this->line('  "message": "Request approved successfully, but booking creation failed: Database connection error",');
        $this->line('  "warning": "You may need to create the booking manually or contact support.",');
        $this->line('  "allocation": { ... },');
        $this->line('  "booking": {');
        $this->line('    "created": false,');
        $this->line('    "error": "Database connection error",');
        $this->line('    "error_code": "DATABASE_ERROR"');
        $this->line('  }');
        $this->line('}');
        $this->newLine();

        $this->info('🎯 KEY IMPROVEMENTS:');
        $this->info('1. ❌ Clear failure when no rooms are available');
        $this->info('2. 🔄 Automatic approval reversion when booking fails due to availability');
        $this->info('3. ⚠️ Clear warnings when booking fails for other reasons');
        $this->info('4. 📱 Specific error codes for frontend handling');
        $this->info('5. 💡 Helpful user guidance on what to do next');
        $this->newLine();

        $this->info('✨ This ensures users always understand exactly what happened and what they need to do!');

        return 0;
    }
}