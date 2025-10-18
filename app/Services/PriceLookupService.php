<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Flight;
use App\Models\TransportService;
use App\Models\RoomRate;
use App\Models\InventoryCalendar;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PriceLookupService
{
    /**
     * Get base price for a service request
     *
     * @param string $providerType
     * @param int $itemId
     * @param array $options
     * @return array
     */
    public function getBasePrice(string $providerType, int $itemId, array $options = []): array
    {
        try {
            switch ($providerType) {
                case 'hotel':
                    return $this->getHotelPrice($itemId, $options);
                    
                case 'flight':
                    return $this->getFlightPrice($itemId, $options);
                    
                case 'transport':
                    return $this->getTransportPrice($itemId, $options);
                    
                default:
                    return $this->getDefaultPrice($providerType, $options);
            }
        } catch (\Exception $e) {
            Log::error('Error determining base price', [
                'provider_type' => $providerType,
                'item_id' => $itemId,
                'options' => $options,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Unable to determine base price',
                'error' => $e->getMessage(),
                'price' => null,
                'currency' => 'USD'
            ];
        }
    }

    /**
     * Get hotel room base price
     */
    private function getHotelPrice(int $roomId, array $options): array
    {
        $room = Room::with('hotel')->find($roomId);
        
        if (!$room) {
            return [
                'success' => false,
                'message' => 'Room not found',
                'price' => null,
                'currency' => 'USD'
            ];
        }

        $startDate = $options['start_date'] ?? null;
        $endDate = $options['end_date'] ?? null;
        $quantity = $options['quantity'] ?? 1;
        
        // Try to get specific room rate for the dates
        $price = $room->base_price;
        $currency = 'USD'; // Default currency
        $source = 'base_price';
        
        if ($startDate && $endDate) {
            $averageRate = $this->getAverageRoomRate($roomId, $startDate, $endDate);
            if ($averageRate) {
                $price = $averageRate;
                $source = 'room_rates';
            }
        }
        
        // Calculate total for quantity (number of rooms)
        $totalPrice = $price * $quantity;
        
        // Calculate per night for the date range
        if ($startDate && $endDate) {
            $nights = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));
            $totalPrice = $totalPrice * max(1, $nights);
        }

        return [
            'success' => true,
            'price' => $price,
            'total_price' => $totalPrice,
            'currency' => $currency,
            'source' => $source,
            'details' => [
                'room_name' => $room->name,
                'room_number' => $room->room_number,
                'hotel_name' => $room->hotel->name ?? 'Unknown Hotel',
                'per_night' => $price,
                'quantity' => $quantity,
                'nights' => isset($nights) ? $nights : 1
            ]
        ];
    }

    /**
     * Get flight base price
     */
    private function getFlightPrice(int $flightId, array $options): array
    {
        $flight = Flight::find($flightId);
        
        if (!$flight) {
            return [
                'success' => false,
                'message' => 'Flight not found',
                'price' => null,
                'currency' => 'USD'
            ];
        }

        $class = $options['class'] ?? 'economy';
        $quantity = $options['quantity'] ?? 1;
        $isGroup = $options['is_group'] ?? false;
        
        // Determine price based on class and group status
        $price = $this->getFlightPriceForClass($flight, $class, $isGroup);
        
        if (!$price) {
            $price = $flight->economy_price; // Fallback to economy
        }

        $totalPrice = $price * $quantity;

        return [
            'success' => true,
            'price' => $price,
            'total_price' => $totalPrice,
            'currency' => $flight->currency ?? 'USD',
            'source' => 'flight_pricing',
            'details' => [
                'flight_number' => $flight->flight_number,
                'airline' => $flight->airline,
                'class' => $class,
                'is_group' => $isGroup,
                'per_person' => $price,
                'quantity' => $quantity,
                'route' => $flight->route ?? ($flight->departure_airport . ' → ' . $flight->arrival_airport)
            ]
        ];
    }

    /**
     * Get transport service base price
     */
    private function getTransportPrice(int $transportId, array $options): array
    {
        $transport = TransportService::find($transportId);
        
        if (!$transport) {
            // Fallback to InventoryCalendar if TransportService doesn't exist
            return $this->getInventoryPrice('transport', $transportId, $options);
        }

        $quantity = $options['quantity'] ?? 1;
        $startDate = $options['start_date'] ?? null;
        
        // Get base rate - this might need adjustment based on your transport pricing model
        $price = $transport->base_price ?? 50.00; // Default fallback
        
        // Apply any pricing rules if available
        if (method_exists($transport, 'getRateWithPricingRules') && $startDate) {
            $rateWithRules = $transport->getRateWithPricingRules($startDate, $quantity);
            if ($rateWithRules) {
                $price = $rateWithRules;
            }
        }

        $totalPrice = $price * $quantity;

        return [
            'success' => true,
            'price' => $price,
            'total_price' => $totalPrice,
            'currency' => $transport->currency ?? 'USD',
            'source' => 'transport_pricing',
            'details' => [
                'service_name' => $transport->name ?? 'Transport Service',
                'service_type' => $transport->service_type ?? 'transport',
                'per_trip' => $price,
                'quantity' => $quantity
            ]
        ];
    }

    /**
     * Get price from inventory calendar (fallback method)
     */
    private function getInventoryPrice(string $providerType, int $itemId, array $options): array
    {
        $startDate = $options['start_date'] ?? now()->format('Y-m-d');
        
        $inventory = InventoryCalendar::where('provider_type', $providerType)
            ->where('item_id', $itemId)
            ->where('date', $startDate)
            ->first();

        if ($inventory && $inventory->base_price) {
            $quantity = $options['quantity'] ?? 1;
            $price = $inventory->base_price;
            
            return [
                'success' => true,
                'price' => $price,
                'total_price' => $price * $quantity,
                'currency' => $inventory->currency ?? 'USD',
                'source' => 'inventory_calendar',
                'details' => [
                    'date' => $startDate,
                    'per_unit' => $price,
                    'quantity' => $quantity
                ]
            ];
        }

        return $this->getDefaultPrice($providerType, $options);
    }

    /**
     * Get default price when no specific pricing is found
     */
    private function getDefaultPrice(string $providerType, array $options): array
    {
        $defaultPrices = [
            'hotel' => 100.00,
            'flight' => 300.00,
            'transport' => 50.00
        ];

        $price = $defaultPrices[$providerType] ?? 100.00;
        $quantity = $options['quantity'] ?? 1;

        return [
            'success' => true,
            'price' => $price,
            'total_price' => $price * $quantity,
            'currency' => 'USD',
            'source' => 'default_pricing',
            'details' => [
                'note' => 'Default pricing used - no specific rates found',
                'provider_type' => $providerType,
                'per_unit' => $price,
                'quantity' => $quantity
            ],
            'warning' => 'Using default pricing. Please verify with actual service rates.'
        ];
    }

    /**
     * Get average room rate for date range
     */
    private function getAverageRoomRate(int $roomId, string $startDate, string $endDate): ?float
    {
        $rates = RoomRate::where('room_id', $roomId)
            ->where('is_active', true)
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('price');

        if ($rates->isEmpty()) {
            return null;
        }

        return $rates->average();
    }

    /**
     * Get flight price for specific class
     */
    private function getFlightPriceForClass(Flight $flight, string $class, bool $isGroup = false): ?float
    {
        switch ($class) {
            case 'economy':
                return $isGroup && $flight->group_economy_price 
                    ? $flight->group_economy_price 
                    : $flight->economy_price;
                    
            case 'business':
                return $isGroup && $flight->group_business_price 
                    ? $flight->group_business_price 
                    : $flight->business_price;
                    
            case 'first_class':
            case 'first':
                return $isGroup && $flight->group_first_class_price 
                    ? $flight->group_first_class_price 
                    : $flight->first_class_price;
                    
            default:
                return $flight->economy_price; // Default fallback
        }
    }

    /**
     * Calculate price for date range
     */
    public function calculatePriceForDateRange(string $providerType, int $itemId, string $startDate, string $endDate, array $options = []): array
    {
        $result = $this->getBasePrice($providerType, $itemId, array_merge($options, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]));

        if (!$result['success']) {
            return $result;
        }

        // For hotels, calculate per night cost
        if ($providerType === 'hotel') {
            $nights = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));
            $nights = max(1, $nights); // At least 1 night
            
            $result['nights'] = $nights;
            $result['total_for_stay'] = $result['total_price'] * $nights;
            $result['details']['nights'] = $nights;
        }

        return $result;
    }
}