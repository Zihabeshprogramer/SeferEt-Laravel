<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use App\Models\ServiceOffer;
use App\Models\Hotel;
use App\Models\TransportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ServiceOfferController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:hotel_provider,transport_provider']);
    }

    /**
     * Display a listing of service offers for the authenticated provider
     */
    public function index()
    {
        $user = Auth::user();
        $offers = ServiceOffer::where('provider_id', $user->id)
            ->with(['service'])
            ->latest()
            ->paginate(10);
        
        $stats = [
            'total_offers' => ServiceOffer::where('provider_id', $user->id)->count(),
            'active_offers' => ServiceOffer::where('provider_id', $user->id)->where('status', 'active')->count(),
            'draft_offers' => ServiceOffer::where('provider_id', $user->id)->where('status', 'draft')->count(),
            'total_bookings' => 0, // TODO: Implement booking count
        ];

        return view('b2b.common.service-offers.index', compact('offers', 'stats'));
    }

    /**
     * Show the form for creating a new service offer
     */
    public function create()
    {
        $user = Auth::user();
        $services = $this->getAvailableServices($user);
        
        return view('b2b.common.service-offers.create', compact('services'));
    }

    /**
     * Store a newly created service offer
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_type' => 'required|in:hotel,transport',
            'service_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'base_price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'max_capacity' => 'nullable|integer|min:1',
            'specifications' => 'nullable|array',
            'pricing_rules' => 'nullable|array',
            'terms_conditions' => 'nullable|array',
            'cancellation_policy' => 'nullable|array',
            'status' => 'required|in:draft,active,inactive'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verify service belongs to authenticated user
        if (!$this->verifyServiceOwnership($request->service_type, $request->service_id)) {
            return redirect()->back()
                ->with('error', 'Unauthorized access to selected service')
                ->withInput();
        }

        $offer = ServiceOffer::create([
            'provider_id' => Auth::id(),
            'service_type' => $request->service_type,
            'service_id' => $request->service_id,
            'name' => $request->name,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'currency' => $request->currency,
            'max_capacity' => $request->max_capacity,
            'specifications' => $request->specifications ?? [],
            'pricing_rules' => $request->pricing_rules ?? [],
            'availability' => [], // TODO: Implement availability calendar
            'terms_conditions' => $request->terms_conditions ?? [],
            'cancellation_policy' => $request->cancellation_policy ?? [],
            'status' => $request->status,
            'is_api_integrated' => false,
            'api_mapping' => []
        ]);

        return redirect()->route('b2b.service-offers.show', $offer)
            ->with('success', 'Service offer created successfully!');
    }

    /**
     * Display the specified service offer
     */
    public function show(ServiceOffer $serviceOffer)
    {
        // Verify ownership
        if ($serviceOffer->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to service offer.');
        }

        $serviceOffer->load(['service']);
        
        $stats = [
            'total_views' => 0, // TODO: Implement view tracking
            'total_bookings' => 0, // TODO: Implement booking tracking
            'revenue' => 0, // TODO: Implement revenue tracking
            'conversion_rate' => 0, // TODO: Calculate conversion rate
        ];

        return view('b2b.common.service-offers.show', compact('serviceOffer', 'stats'));
    }

    /**
     * Show the form for editing the specified service offer
     */
    public function edit(ServiceOffer $serviceOffer)
    {
        // Verify ownership
        if ($serviceOffer->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to service offer.');
        }

        $user = Auth::user();
        $services = $this->getAvailableServices($user);
        
        return view('b2b.common.service-offers.edit', compact('serviceOffer', 'services'));
    }

    /**
     * Update the specified service offer
     */
    public function update(Request $request, ServiceOffer $serviceOffer)
    {
        // Verify ownership
        if ($serviceOffer->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to service offer.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'base_price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'max_capacity' => 'nullable|integer|min:1',
            'specifications' => 'nullable|array',
            'pricing_rules' => 'nullable|array',
            'terms_conditions' => 'nullable|array',
            'cancellation_policy' => 'nullable|array',
            'status' => 'required|in:draft,active,inactive'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $serviceOffer->update([
            'name' => $request->name,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'currency' => $request->currency,
            'max_capacity' => $request->max_capacity,
            'specifications' => $request->specifications ?? [],
            'pricing_rules' => $request->pricing_rules ?? [],
            'terms_conditions' => $request->terms_conditions ?? [],
            'cancellation_policy' => $request->cancellation_policy ?? [],
            'status' => $request->status
        ]);

        return redirect()->route('b2b.service-offers.show', $serviceOffer)
            ->with('success', 'Service offer updated successfully!');
    }

    /**
     * Toggle service offer status
     */
    public function toggleStatus(ServiceOffer $serviceOffer)
    {
        // Verify ownership
        if ($serviceOffer->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to service offer.');
        }

        $newStatus = $serviceOffer->status === 'active' ? 'inactive' : 'active';
        $serviceOffer->update(['status' => $newStatus]);

        return redirect()->back()
            ->with('success', "Service offer {$newStatus} successfully.");
    }

    /**
     * Remove the specified service offer
     */
    public function destroy(ServiceOffer $serviceOffer)
    {
        // Verify ownership
        if ($serviceOffer->provider_id !== Auth::id()) {
            abort(403, 'Unauthorized access to service offer.');
        }

        $serviceOffer->delete();

        return redirect()->route('b2b.service-offers.index')
            ->with('success', 'Service offer deleted successfully!');
    }

    /**
     * Get available services for the authenticated user
     */
    private function getAvailableServices($user)
    {
        $services = collect();
        
        if ($user->role === 'hotel_provider') {
            $hotels = Hotel::where('provider_id', $user->id)->get();
            foreach ($hotels as $hotel) {
                $services->push([
                    'type' => 'hotel',
                    'id' => $hotel->id,
                    'name' => $hotel->name,
                    'location' => $hotel->city . ', ' . $hotel->country
                ]);
            }
        }
        
        if ($user->role === 'transport_provider') {
            $transports = TransportService::where('provider_id', $user->id)->get();
            foreach ($transports as $transport) {
                $services->push([
                    'type' => 'transport',
                    'id' => $transport->id,
                    'name' => $transport->service_name,
                    'transport_type' => $transport->transport_type
                ]);
            }
        }
        
        return $services;
    }

    /**
     * Verify that the service belongs to the authenticated user
     */
    private function verifyServiceOwnership($serviceType, $serviceId)
    {
        $user = Auth::user();
        
        switch ($serviceType) {
            case 'hotel':
                return Hotel::where('id', $serviceId)
                    ->where('provider_id', $user->id)
                    ->exists();
            case 'transport':
                return TransportService::where('id', $serviceId)
                    ->where('provider_id', $user->id)
                    ->exists();
            default:
                return false;
        }
    }
}
