<?php

namespace App\Services;

use App\Models\Flight;
use App\Models\FlightBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * FlightAvailabilityService
 * 
 * Handles flight seat availability and booking validation
 */
class FlightAvailabilityService
{
    /**
     * Check if a flight has sufficient available seats
     * 
     * @param Flight $flight
     * @param int $requiredSeats
     * @return bool
     */
    public function hasAvailableSeats(Flight $flight, int $requiredSeats = 1): bool
    {
        return $flight->available_seats >= $requiredSeats;
    }

    /**
     * Get available flights for specific dates and passenger count
     * 
     * @param string $departureAirport
     * @param string $arrivalAirport
     * @param Carbon $departureDate
     * @param Carbon|null $returnDate
     * @param int $passengerCount
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableFlights(
        string $departureAirport,
        string $arrivalAirport,
        Carbon $departureDate,
        ?Carbon $returnDate = null,
        int $passengerCount = 1,
        array $filters = []
    ) {
        $query = Flight::query()
            ->where('is_active', true)
            ->where('status', Flight::STATUS_SCHEDULED)
            ->where('available_seats', '>=', $passengerCount)
            ->where('departure_airport', 'like', "%{$departureAirport}%")
            ->where('arrival_airport', 'like', "%{$arrivalAirport}%")
            ->where('departure_datetime', '>=', $departureDate->startOfDay())
            ->where('departure_datetime', '<=', $departureDate->endOfDay())
            ->whereNull('deleted_at');

        // Apply filters
        if (isset($filters['flight_class'])) {
            $this->applyClassFilter($query, $filters['flight_class']);
        }

        if (isset($filters['max_price'])) {
            $this->applyPriceFilter($query, $filters['max_price'], $filters['flight_class'] ?? 'economy');
        }

        if (isset($filters['airline'])) {
            $query->where('airline', 'like', "%{$filters['airline']}%");
        }

        if (isset($filters['is_group_booking'])) {
            $query->where('is_group_booking', $filters['is_group_booking']);
            if ($filters['is_group_booking']) {
                $query->where('min_group_size', '<=', $passengerCount)
                      ->where('max_group_size', '>=', $passengerCount);
            }
        }

        return $query->orderBy('departure_datetime')->get();
    }

    /**
     * Reserve seats for a flight (deduct from available_seats)
     * 
     * @param Flight $flight
     * @param int $seats
     * @return bool
     */
    public function reserveSeats(Flight $flight, int $seats): bool
    {
        try {
            // Use database-level atomic operation to prevent race conditions
            $updated = DB::table('flights')
                ->where('id', $flight->id)
                ->where('available_seats', '>=', $seats)
                ->update([
                    'available_seats' => DB::raw('available_seats - ' . $seats),
                    'updated_at' => now()
                ]);

            if ($updated === 0) {
                Log::warning('Failed to reserve seats - insufficient availability', [
                    'flight_id' => $flight->id,
                    'requested_seats' => $seats,
                    'current_available_seats' => $flight->available_seats
                ]);
                return false;
            }

            // Refresh the model to get updated values
            $flight->refresh();

            Log::info('Seats reserved successfully', [
                'flight_id' => $flight->id,
                'seats_reserved' => $seats,
                'remaining_seats' => $flight->available_seats
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error reserving seats', [
                'flight_id' => $flight->id,
                'seats' => $seats,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Release reserved seats back to the flight
     * 
     * @param Flight $flight
     * @param int $seats
     * @return bool
     */
    public function releaseSeats(Flight $flight, int $seats): bool
    {
        try {
            // Ensure we don't exceed total seats
            $newAvailableSeats = min($flight->available_seats + $seats, $flight->total_seats);

            $updated = DB::table('flights')
                ->where('id', $flight->id)
                ->update([
                    'available_seats' => $newAvailableSeats,
                    'updated_at' => now()
                ]);

            if ($updated === 0) {
                Log::warning('Failed to release seats', [
                    'flight_id' => $flight->id,
                    'seats_to_release' => $seats
                ]);
                return false;
            }

            // Refresh the model
            $flight->refresh();

            Log::info('Seats released successfully', [
                'flight_id' => $flight->id,
                'seats_released' => $seats,
                'available_seats' => $flight->available_seats
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error releasing seats', [
                'flight_id' => $flight->id,
                'seats' => $seats,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if flight can accommodate group booking
     * 
     * @param Flight $flight
     * @param int $passengerCount
     * @return bool
     */
    public function canAccommodateGroup(Flight $flight, int $passengerCount): bool
    {
        if (!$flight->is_group_booking) {
            return $this->hasAvailableSeats($flight, $passengerCount);
        }

        return $flight->meetsMinGroupSize($passengerCount) &&
               $flight->hasSpaceForGroup($passengerCount) &&
               $this->hasAvailableSeats($flight, $passengerCount);
    }

    /**
     * Get booking statistics for a flight
     * 
     * @param Flight $flight
     * @return array
     */
    public function getBookingStatistics(Flight $flight): array
    {
        $totalBookedSeats = FlightBooking::where('flight_id', $flight->id)
            ->whereNotIn('status', [FlightBooking::STATUS_CANCELLED])
            ->sum('passengers');

        $occupancyRate = $flight->total_seats > 0 
            ? round(($totalBookedSeats / $flight->total_seats) * 100, 1) 
            : 0;

        return [
            'total_seats' => $flight->total_seats,
            'available_seats' => $flight->available_seats,
            'booked_seats' => $totalBookedSeats,
            'occupancy_rate' => $occupancyRate,
            'is_full' => $flight->available_seats === 0,
            'booking_percentage' => round((($flight->total_seats - $flight->available_seats) / $flight->total_seats) * 100, 1)
        ];
    }

    /**
     * Validate booking availability before creating booking
     * 
     * @param Flight $flight
     * @param int $passengerCount
     * @param array $options
     * @return array
     */
    public function validateBookingAvailability(Flight $flight, int $passengerCount, array $options = []): array
    {
        $errors = [];

        // Check if flight is active
        if (!$flight->is_active) {
            $errors[] = 'Flight is not active';
        }

        // Check if flight is scheduleds
        if ($flight->status !== Flight::STATUS_SCHEDULED) {
            $errors[] = 'Flight is not available for booking (status: ' . $flight->status . ')';
        }

        // Check if flight is in the future
        if ($flight->departure_datetime <= now()) {
            $errors[] = 'Flight has already departed or departure time has passed';
        }

        // Check booking deadline
        if ($flight->isBookingDeadlinePassed()) {
            $errors[] = 'Booking deadline has passed';
        }

        // Check available seats
        if (!$this->hasAvailableSeats($flight, $passengerCount)) {
            $errors[] = "Insufficient seats available. Requested: {$passengerCount}, Available: {$flight->available_seats}";
        }

        // Check group booking requirements
        if ($flight->is_group_booking && !$flight->meetsMinGroupSize($passengerCount)) {
            $errors[] = "Minimum group size is {$flight->min_group_size} passengers";
        }

        if ($flight->is_group_booking && !$flight->hasSpaceForGroup($passengerCount)) {
            $errors[] = "Group size exceeds maximum limit of {$flight->max_group_size} passengers";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get flights with low availability (warning threshold)
     * 
     * @param int $threshold
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFlightsWithLowAvailability(int $threshold = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Flight::where('is_active', true)
            ->where('status', Flight::STATUS_SCHEDULED)
            ->where('available_seats', '>', 0)
            ->where('available_seats', '<=', $threshold)
            ->where('departure_datetime', '>', now())
            ->orderBy('available_seats')
            ->get();
    }

    /**
     * Apply class filter to query
     * 
     * @param $query
     * @param string $class
     */
    private function applyClassFilter($query, string $class): void
    {
        switch ($class) {
            case Flight::CLASS_ECONOMY:
                $query->whereNotNull('economy_price');
                break;
            case Flight::CLASS_BUSINESS:
                $query->whereNotNull('business_price');
                break;
            case Flight::CLASS_FIRST:
                $query->whereNotNull('first_class_price');
                break;
        }
    }

    /**
     * Apply price filter to query
     * 
     * @param $query
     * @param float $maxPrice
     * @param string $class
     */
    private function applyPriceFilter($query, float $maxPrice, string $class = 'economy'): void
    {
        switch ($class) {
            case Flight::CLASS_ECONOMY:
                $query->where('economy_price', '<=', $maxPrice);
                break;
            case Flight::CLASS_BUSINESS:
                $query->where('business_price', '<=', $maxPrice);
                break;
            case Flight::CLASS_FIRST:
                $query->where('first_class_price', '<=', $maxPrice);
                break;
        }
    }
}