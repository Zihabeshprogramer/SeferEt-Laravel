<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING HOTELS ===\n\n";

$hotels = DB::table('hotels')
    ->where('status', 'active')
    ->where('is_active', 1)
    ->select('id', 'name', 'city', 'status', 'is_active')
    ->get();

echo "Total active hotels: " . $hotels->count() . "\n\n";

foreach ($hotels as $hotel) {
    echo "Hotel ID: {$hotel->id}\n";
    echo "Name: {$hotel->name}\n";
    echo "City: {$hotel->city}\n";
    echo "Status: {$hotel->status}\n";
    echo "Active: " . ($hotel->is_active ? 'Yes' : 'No') . "\n";
    
    // Check rooms
    $rooms = DB::table('rooms')
        ->where('hotel_id', $hotel->id)
        ->select('id', 'category', 'is_available', 'is_active', 'max_occupancy', 'base_price')
        ->get();
    
    echo "Total rooms: " . $rooms->count() . "\n";
    
    $availableCount = $rooms->where('is_available', 1)->where('is_active', 1)->count();
    echo "Available rooms: {$availableCount}\n";
    
    foreach ($rooms as $room) {
        echo "  - Room {$room->id}: {$room->category}, Available: " . ($room->is_available ? 'Yes' : 'No') . ", Active: " . ($room->is_active ? 'Yes' : 'No') . ", Max Occupancy: {$room->max_occupancy}, Price: \${$room->base_price}\n";
    }
    
    echo "\n";
}

echo "\n=== SAMPLE SEARCH URL ===\n\n";

if ($hotels->count() > 0) {
    $firstHotel = $hotels->first();
    $city = $firstHotel->city;
    $checkIn = date('Y-m-d', strtotime('+7 days'));
    $checkOut = date('Y-m-d', strtotime('+10 days'));
    
    echo "Try this URL:\n";
    echo "/hotels?location_display=" . urlencode($city) . "&location={$city}&check_in={$checkIn}&check_out={$checkOut}&rooms=1&guests=1\n\n";
} else {
    echo "No active hotels found in database.\n\n";
}
