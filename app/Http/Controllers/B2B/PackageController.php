<?php
namespace App\Http\Controllers\B2B;
use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageActivity;
use App\Models\PackageDraft;
use App\Models\Flight;
use App\Models\Hotel;
use App\Models\TransportService;
use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Http\Requests\PackageImageUploadRequest;
use App\Services\PackageImageService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Carbon\Carbon;
/**
 * Enhanced PackageController - Advanced Package Creation Module
 * 
 * Sophisticated multi-step package creation system with provider integration,
 * real-time validation, commission handling, and comprehensive management features.
 */
class PackageController extends Controller
{
    protected PackageImageService $imageService;
    public function __construct(PackageImageService $imageService)
    {
        $this->middleware(['auth', 'role:travel_agent']);
        $this->imageService = $imageService;
    }
    /**
     * Display a listing of packages with advanced filtering and analytics
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Package::where('creator_id', $user->id)
                        ->with(['flights', 'hotels', 'transportServices', 'packageActivities', 'approvedBy']);
        // Advanced filtering
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }
        if ($request->filled('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }
        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        $packages = $query->paginate($request->get('per_page', 12))
                          ->withQueryString();
        // Comprehensive analytics
        $stats = $this->getPackageAnalytics($user->id);
        // Filter options for the view
        $filterOptions = [
            'statuses' => Package::STATUSES,
            'approval_statuses' => Package::APPROVAL_STATUSES,
            'types' => Package::TYPES,
            'currencies' => ['USD', 'EUR', 'GBP', 'SAR', 'AED'],
        ];
        return view('b2b.travel-agent.packages.index', compact(
            'packages', 'stats', 'filterOptions'
        ));
    }
    /**
     * Get comprehensive analytics for packages
     */
    private function getPackageAnalytics($userId): array
    {
        $baseQuery = Package::where('creator_id', $userId);
        return [
            'total_packages' => $baseQuery->count(),
            'active_packages' => $baseQuery->where('status', Package::STATUS_ACTIVE)->count(),
            'draft_packages' => $baseQuery->where('status', Package::STATUS_DRAFT)->count(),
            'inactive_packages' => $baseQuery->where('status', Package::STATUS_INACTIVE)->count(),
            'suspended_packages' => $baseQuery->where('status', Package::STATUS_SUSPENDED)->count(),
            'approved_packages' => $baseQuery->where('approval_status', Package::APPROVAL_APPROVED)->count(),
            'pending_approval' => $baseQuery->where('approval_status', Package::APPROVAL_PENDING)->count(),
            'rejected_packages' => $baseQuery->where('approval_status', Package::APPROVAL_REJECTED)->count(),
            'needs_revision' => $baseQuery->where('approval_status', Package::APPROVAL_NEEDS_REVISION)->count(),
            'featured_packages' => $baseQuery->where('is_featured', true)->count(),
            'premium_packages' => $baseQuery->where('is_premium', true)->count(),
            'total_revenue' => $baseQuery->sum('total_price'),
            'average_price' => $baseQuery->avg('base_price'),
            'total_bookings' => $baseQuery->sum('bookings_count'),
            'total_views' => $baseQuery->sum('views_count'),
            'average_rating' => $baseQuery->avg('average_rating'),
            'packages_this_month' => $baseQuery->whereMonth('created_at', now()->month)->count(),
            'packages_last_month' => $baseQuery->whereMonth('created_at', now()->subMonth()->month)->count(),
        ];
    }
    /**
     * Multi-step package creation wizard - Show the form
     */
    public function create(Request $request): View
    {
        // Initialize base data for the wizard
        $baseData = $this->getWizardBaseData();
        // Check if we have draft data from continueDraft method
        $sessionDraftData = session('draft_data');
        $draft = null;
        if ($sessionDraftData) {
            // We're continuing from a saved draft
            $draft = $this->prepareDraftForView($sessionDraftData);
            // Clear the session data after using it
            session()->forget('draft_data');
            // Add success message
            session()->flash('success', 'Draft loaded successfully! Continue from step ' . $draft->current_step . '.');
        }
        return view('b2b.travel-agent.packages.create', compact('draft') + $baseData);
    }
    /**
     * Get base data required for the package creation wizard
     */
    private function getWizardBaseData(): array
    {
        return [
            'packageTypes' => Package::getPackageTypes(),
            'currencies' => [
                'SAR' => 'Saudi Riyal (SAR)',
                'USD' => 'US Dollar (USD)',
                'EUR' => 'Euro (EUR)',
                'GBP' => 'British Pound (GBP)',
                'AED' => 'UAE Dirham (AED)'
            ],
            'providerSources' => [
                Package::SOURCE_PLATFORM => 'Use Platform Providers',
                Package::SOURCE_EXTERNAL => 'Use External Providers',
                Package::SOURCE_MIXED => 'Mix of Both'
            ],
            'flightSources' => [
                Package::SOURCE_OWN => 'My Own Flights',
                Package::SOURCE_PLATFORM => 'Platform Flights',
                Package::SOURCE_EXTERNAL => 'External Flights',
                Package::SOURCE_MIXED => 'Mixed Sources'
            ],
            'difficultyLevels' => [
                'easy' => 'Easy - Suitable for beginners',
                'moderate' => 'Moderate - Some experience required',
                'challenging' => 'Challenging - Good fitness required', 
                'expert' => 'Expert - High experience and fitness required'
            ],
            'paymentTerms' => [
                'full_upfront' => 'Full Payment Upfront',
                '50_percent_deposit' => '50% Deposit + 50% Before Travel',
                '30_percent_deposit' => '30% Deposit + 70% Before Travel'
            ],
            'currentStep' => 1,
            'totalSteps' => 5
        ];
    }
    /**
     * Prepare draft data for the view
     */
    private function prepareDraftForView(array $sessionDraftData): object
    {
        $draftData = $sessionDraftData['data'] ?? [];
        $draft = (object) [
            'id' => $sessionDraftData['id'],
            'name' => $sessionDraftData['name'],
            'current_step' => $sessionDraftData['current_step'],
            'progress' => $sessionDraftData['progress'],
            'data' => $draftData,
            'last_accessed_at' => now()->toDateTimeString(),
            'is_continuing' => true
        ];
        // Add draft data fields as direct properties for easy Blade access
        foreach ($draftData as $key => $value) {
            $draft->$key = $value;
        }
        return $draft;
    }
    /**
     * Process Step 1 and move to Step 2: Itinerary Builder
     */
    public function createStep2(Request $request): View
    {
        $validatedStep1 = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'detailed_description' => 'nullable|string|max:10000',
            'type' => 'required|in:' . implode(',', Package::TYPES),
            'duration' => 'required|integer|min:1|max:365',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            // 'base_price' => 'required|numeric|min:0', // Moved to Step 4
            'currency' => 'required|string|size:3',
            'hotel_source' => 'required|in:' . implode(',', Package::PROVIDER_SOURCES),
            'transport_source' => 'required|in:' . implode(',', Package::PROVIDER_SOURCES),
            'flight_source' => 'required|in:' . implode(',', Package::FLIGHT_SOURCES),
        ]);
        // Store step 1 data in session
        session(['package_draft.step1' => $validatedStep1]);
        // Get available providers based on selections
        $availableHotels = $this->getAvailableHotels($validatedStep1['hotel_source']);
        $availableTransport = $this->getAvailableTransport($validatedStep1['transport_source']);
        $availableFlights = $this->getAvailableFlights($validatedStep1['flight_source']);
        $data = [
            'step1Data' => $validatedStep1,
            'availableHotels' => $availableHotels,
            'availableTransport' => $availableTransport,
            'availableFlights' => $availableFlights,
            'currentStep' => 2,
            'totalSteps' => 5
        ];
        return view('b2b.travel-agent.packages.create.step2-itinerary', $data);
    }
    /**
     * Process Step 2 and move to Step 3: Provider Selection
     */
    public function createStep3(Request $request): View
    {
        // Check if we're continuing from an existing draft with different data format
        $hasExistingProviders = $request->has('selected_hotels') || 
                               $request->has('selected_transport') || 
                               $request->has('selected_flights');
        if ($hasExistingProviders) {
            // More flexible validation for existing drafts with service request format
            $validatedStep2 = $request->validate([
                'selected_hotels' => 'nullable|array',
                'selected_hotels.*.id' => 'nullable|string',
                'selected_hotels.*.hotel_id' => 'nullable|exists:hotels,id',
                'selected_hotels.*.type' => 'nullable|string',
                'selected_hotels.*.provider_type' => 'nullable|string',
                'selected_hotels.*.service_request_id' => 'nullable|string',
                'selected_hotels.*.service_request_status' => 'nullable|string',
                'selected_hotels.*.is_primary' => 'nullable|boolean',
                'selected_hotels.*.nights' => 'nullable|integer|min:1',
                'selected_hotels.*.room_type' => 'nullable|string|max:100',
                'selected_hotels.*.rooms_needed' => 'nullable|integer|min:1',
                'selected_hotels.*.markup_percentage' => 'nullable|numeric|min:0|max:100',
                'selected_hotels.*.approved_at' => 'nullable',
                'selected_hotels.*.offered_price' => 'nullable',
                'selected_hotels.*.currency' => 'nullable|string',
                'selected_hotels.*.provider_notes' => 'nullable|string',
                'selected_hotels.*.terms_conditions' => 'nullable',
                'selected_hotels.*.request_info' => 'nullable|string',
                'selected_hotels.*.selected_at' => 'nullable|string',
                'selected_hotels.*.from_draft' => 'nullable|string',
                'selected_transport' => 'nullable|array',
                'selected_transport.*.id' => 'nullable|string',
                'selected_transport.*.transport_id' => 'nullable|exists:transport_services,id',
                'selected_transport.*.type' => 'nullable|string',
                'selected_transport.*.provider_type' => 'nullable|string',
                'selected_transport.*.service_request_id' => 'nullable|string',
                'selected_transport.*.service_request_status' => 'nullable|string',
                'selected_transport.*.category' => 'nullable|string|max:50',
                'selected_transport.*.pickup_location' => 'nullable|string|max:255',
                'selected_transport.*.dropoff_location' => 'nullable|string|max:255',
                'selected_transport.*.day_of_itinerary' => 'nullable|integer|min:1',
                'selected_transport.*.markup_percentage' => 'nullable|numeric|min:0|max:100',
                'selected_transport.*.approved_at' => 'nullable',
                'selected_transport.*.offered_price' => 'nullable',
                'selected_transport.*.currency' => 'nullable|string',
                'selected_transport.*.provider_notes' => 'nullable|string',
                'selected_transport.*.terms_conditions' => 'nullable',
                'selected_transport.*.request_info' => 'nullable|string',
                'selected_transport.*.selected_at' => 'nullable|string',
                'selected_transport.*.from_draft' => 'nullable|string',
                'selected_flights' => 'nullable|array',
                'selected_flights.*.id' => 'nullable|string',
                'selected_flights.*.flight_id' => 'nullable|exists:flights,id',
                'selected_flights.*.type' => 'nullable|string',
                'selected_flights.*.provider_type' => 'nullable|string',
                'selected_flights.*.service_request_id' => 'nullable|string',
                'selected_flights.*.service_request_status' => 'nullable|string',
                'selected_flights.*.flight_type' => 'nullable|in:outbound,return,connecting',
                'selected_flights.*.seats_allocated' => 'nullable|integer|min:1',
                'selected_flights.*.markup_percentage' => 'nullable|numeric|min:0|max:100',
                'selected_flights.*.approved_at' => 'nullable',
                'selected_flights.*.offered_price' => 'nullable',
                'selected_flights.*.currency' => 'nullable|string',
                'selected_flights.*.provider_notes' => 'nullable|string',
                'selected_flights.*.terms_conditions' => 'nullable',
                'selected_flights.*.request_info' => 'nullable|string',
                'selected_flights.*.selected_at' => 'nullable|string',
                'selected_flights.*.from_draft' => 'nullable|string',
                'external_providers' => 'nullable|array'
            ]);
        } else {
            // Original strict validation for new selections
            $validatedStep2 = $request->validate([
                'selected_hotels' => 'nullable|array',
                'selected_hotels.*.hotel_id' => 'required|exists:hotels,id',
                'selected_hotels.*.is_primary' => 'nullable|boolean',
                'selected_hotels.*.nights' => 'required|integer|min:1',
                'selected_hotels.*.room_type' => 'required|string|max:100',
                'selected_hotels.*.rooms_needed' => 'required|integer|min:1',
                'selected_hotels.*.markup_percentage' => 'nullable|numeric|min:0|max:100',
                'selected_transport' => 'nullable|array',
                'selected_transport.*.transport_id' => 'required|exists:transport_services,id',
                'selected_transport.*.category' => 'required|string|max:50',
                'selected_transport.*.pickup_location' => 'required|string|max:255',
                'selected_transport.*.dropoff_location' => 'required|string|max:255',
                'selected_transport.*.day_of_itinerary' => 'required|integer|min:1',
                'selected_transport.*.markup_percentage' => 'nullable|numeric|min:0|max:100',
                'selected_flights' => 'nullable|array',
                'selected_flights.*.flight_id' => 'required|exists:flights,id',
                'selected_flights.*.flight_type' => 'required|in:outbound,return,connecting',
                'selected_flights.*.seats_allocated' => 'required|integer|min:1',
                'selected_flights.*.markup_percentage' => 'nullable|numeric|min:0|max:100',
                'external_providers' => 'nullable|array'
            ]);
        }
        // Store step 2 data in session
        session(['package_draft.step2' => $validatedStep2]);
        $step1Data = session('package_draft.step1');
        $duration = $step1Data['duration'];
        $data = [
            'step1Data' => $step1Data,
            'step2Data' => $validatedStep2,
            'duration' => $duration,
            'activityCategories' => PackageActivity::CATEGORIES,
            'currentStep' => 3,
            'totalSteps' => 5
        ];
        return view('b2b.travel-agent.packages.create.step3-providers', $data);
    }
    /**
     * Process Step 3 and move to Step 4: Pricing & Options
     */
    public function createStep4(Request $request): View
    {
        $validatedStep3 = $request->validate([
            'activities' => 'nullable|array',
            'activities.*.day_number' => 'required|integer|min:1',
            'activities.*.activity_name' => 'required|string|max:255',
            'activities.*.description' => 'required|string|max:1000',
            'activities.*.start_time' => 'nullable|date_format:H:i',
            'activities.*.end_time' => 'nullable|date_format:H:i',
            'activities.*.location' => 'nullable|string|max:255',
            'activities.*.category' => 'required|in:' . implode(',', PackageActivity::CATEGORIES),
            'activities.*.is_included' => 'nullable|boolean',
            'activities.*.additional_cost' => 'nullable|numeric|min:0',
            'activities.*.is_optional' => 'nullable|boolean',
            'activities.*.display_order' => 'required|integer|min:0',
            // Selected Providers from Step 3
            'selected_hotels' => 'nullable|array',
            'selected_hotels.*.id' => 'required|integer',
            'selected_hotels.*.name' => 'required|string|max:255',
            'selected_hotels.*.type' => 'nullable|string|max:50',
            'selected_hotels.*.location' => 'nullable|string|max:255',
            'selected_hotels.*.price' => 'nullable|numeric|min:0',
            'selected_hotels.*.currency' => 'nullable|string|max:10',
            'selected_hotels.*.nights' => 'nullable|integer|min:1',
            'selected_hotels.*.rooms_needed' => 'nullable|integer|min:1',
            'selected_hotels.*.room_type' => 'nullable|string|max:100',
            'selected_hotels.*.is_primary' => 'nullable|boolean',
            'selected_hotels.*.markup_percentage' => 'nullable|numeric|min:0|max:100',
            'selected_hotels.*.special_requirements' => 'nullable|string|max:1000',
            'selected_flights' => 'nullable|array',
            'selected_flights.*.id' => 'required|integer',
            'selected_flights.*.airline' => 'required|string|max:255',
            'selected_flights.*.flight_number' => 'nullable|string|max:50',
            'selected_flights.*.departure' => 'nullable|string|max:255',
            'selected_flights.*.arrival' => 'nullable|string|max:255',
            'selected_flights.*.departure_time' => 'nullable|date_format:H:i',
            'selected_flights.*.arrival_time' => 'nullable|date_format:H:i',
            'selected_flights.*.price' => 'nullable|numeric|min:0',
            'selected_flights.*.currency' => 'nullable|string|max:10',
            'selected_flights.*.flight_type' => 'nullable|in:departure,return,domestic',
            'selected_flights.*.seats_allocated' => 'nullable|integer|min:1',
            'selected_flights.*.markup_percentage' => 'nullable|numeric|min:0|max:100',
            'selected_flights.*.special_requirements' => 'nullable|string|max:1000',
            'selected_transport' => 'nullable|array',
            'selected_transport.*.id' => 'required|integer',
            'selected_transport.*.company' => 'required|string|max:255',
            'selected_transport.*.vehicle_type' => 'nullable|string|max:100',
            'selected_transport.*.capacity' => 'nullable|integer|min:1',
            'selected_transport.*.pickup_location' => 'nullable|string|max:255',
            'selected_transport.*.dropoff_location' => 'nullable|string|max:255',
            'selected_transport.*.price' => 'nullable|numeric|min:0',
            'selected_transport.*.currency' => 'nullable|string|max:10',
            'selected_transport.*.day_of_itinerary' => 'nullable|integer|min:1',
            'selected_transport.*.markup_percentage' => 'nullable|numeric|min:0|max:100',
            'selected_transport.*.special_requirements' => 'nullable|string|max:1000',
        ]);
        // Store step 3 data in session
        session(['package_draft.step3' => $validatedStep3]);
        $step1Data = session('package_draft.step1');
        $step2Data = session('package_draft.step2');
        // Calculate estimated total price
        $estimatedPrice = $this->calculateEstimatedPrice($step1Data, $step2Data, $validatedStep3);
        $data = [
            'step1Data' => $step1Data,
            'step2Data' => $step2Data,
            'step3Data' => $validatedStep3,
            'estimatedPrice' => $estimatedPrice,
            'currentStep' => 4,
            'totalSteps' => 5
        ];
        return view('b2b.travel-agent.packages.create.step4-pricing', $data);
    }
    /**
     * Process Step 4 and move to Step 5: Review & Finalize
     */
    public function createStep5(Request $request): View
    {
        $validatedStep4 = $request->validate([
            'pricing_breakdown' => 'nullable|array',
            'pricing_breakdown.*.item' => 'required|string|max:255',
            'pricing_breakdown.*.amount' => 'required|numeric|min:0',
            'optional_addons' => 'nullable|array',
            'optional_addons.*.name' => 'required|string|max:255',
            'optional_addons.*.price' => 'required|numeric|min:0',
            'optional_addons.*.description' => 'nullable|string|max:500',
            'total_price' => 'required|numeric|min:0',
            'deposit_percentage' => 'nullable|numeric|min:0|max:100',
            'platform_commission' => 'nullable|numeric|min:0|max:50',
            'payment_terms' => 'nullable|array',
            'cancellation_policy' => 'nullable|array',
            'group_discounts' => 'nullable|array',
            'seasonal_pricing' => 'nullable|array',
            'min_participants' => 'nullable|integer|min:1',
            'max_participants' => 'nullable|integer|min:1',
            'is_featured' => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
            'allow_customization' => 'nullable|boolean',
            'instant_booking' => 'nullable|boolean',
        ]);
        // Store step 4 data in session
        session(['package_draft.step4' => $validatedStep4]);
        $allData = [
            'step1' => session('package_draft.step1'),
            'step2' => session('package_draft.step2'),
            'step3' => session('package_draft.step3'),
            'step4' => $validatedStep4,
        ];
        $data = [
            'packageData' => $allData,
            'currentStep' => 5,
            'totalSteps' => 5
        ];
        return view('b2b.travel-agent.packages.create.step5-review', $data);
    }
    /**
     * Upload an image for a package draft
     */
    public function uploadDraftImage(PackageImageUploadRequest $request): JsonResponse
    {
        try {
            // Validation is handled by PackageImageUploadRequest
            $draftId = $request->input('package_draft_id') ?? $request->input('draft_id') ?? session('package_draft_id');
            \Log::info('Draft image upload attempt', [
                'request_package_draft_id' => $request->input('package_draft_id'),
                'request_draft_id' => $request->input('draft_id'),
                'session_package_draft_id' => session('package_draft_id'),
                'final_draft_id' => $draftId,
                'user_id' => Auth::id()
            ]);
            if (!$draftId) {
                return response()->json([
                    'error' => 'No active draft found',
                    'debug' => [
                        'request_package_draft_id' => $request->input('package_draft_id'),
                        'request_draft_id' => $request->input('draft_id'),
                        'session_package_draft_id' => session('package_draft_id')
                    ]
                ], 400);
            }
            $uploadedFile = $request->file('image');
            $imageData = $this->processImageUpload($uploadedFile, 'package-drafts');
            // Load existing draft or create new one
            $draft = PackageDraft::where('id', $draftId)
                ->where('user_id', Auth::id())
                ->first();
            if (!$draft) {
                // Verify the draft actually exists if an ID was provided
                if ($draftId && is_numeric($draftId)) {
                    // Draft ID was provided but doesn't exist - this is an error
                    \Log::warning('Draft ID provided but draft not found', [
                        'draft_id' => $draftId,
                        'user_id' => Auth::id()
                    ]);
                    return response()->json([
                        'error' => 'Draft not found',
                        'debug' => [
                            'provided_draft_id' => $draftId,
                            'draft_exists' => false
                        ]
                    ], 404);
                } else {
                    // No valid draft ID provided, create a new draft
                    $draft = new PackageDraft();
                    $draft->user_id = Auth::id();
                    $draft->draft_data = [];
                    $draft->current_step = 1;
                    $draft->step_status = ['step_1' => 'in_progress'];
                    $draft->last_accessed_at = now();
                    $draft->save(); // Save to get an ID
                    // Update session with new draft ID
                    session(['package_draft_id' => $draft->id]);
                    \Log::info('Created new draft for image upload', [
                        'new_draft_id' => $draft->id,
                        'user_id' => Auth::id()
                    ]);
                }
            }
            $draftData = $draft->draft_data ?? [];
            $images = $draftData['images'] ?? [];
            // Check for duplicate filename to prevent double uploads
            $uploadedFilename = $imageData['filename'];
            foreach ($images as $existingImage) {
                if (!empty($existingImage['filename']) && $existingImage['filename'] === $uploadedFilename) {
                    \Log::warning('Duplicate image upload attempt', [
                        'filename' => $uploadedFilename,
                        'draft_id' => $draft->id
                    ]);
                    return response()->json([
                        'success' => false,
                        'error' => 'This image has already been uploaded'
                    ], 409);
                }
            }
            // Add new image to the array
            $newImage = [
                'id' => Str::uuid()->toString(),
                'filename' => $imageData['filename'],
                'original_name' => $uploadedFile->getClientOriginalName(),
                'sizes' => $imageData['sizes'],
                'is_main' => count($images) === 0, // First image is main by default
                'alt_text' => '',
                'uploaded_at' => now()->toISOString(),
            ];
            // Only add image if it has all required data
            if (!empty($newImage['id']) && !empty($newImage['filename'])) {
                $images[] = $newImage;
                // Filter out any empty images that might exist
                $images = array_filter($images, function($img) {
                    return !empty($img) && !empty($img['id']);
                });
                // Re-index the array to avoid gaps
                $images = array_values($images);
                $draftData['images'] = $images;
                $draft->draft_data = $draftData;
                $draft->save();
            } else {
                \Log::error('Attempted to add invalid image to draft', [
                    'newImage' => $newImage,
                    'draft_id' => $draft->id
                ]);
                return response()->json(['error' => 'Failed to process image data'], 500);
            }
            \Log::info('Image uploaded successfully to draft', [
                'draft_id' => $draft->id,
                'image_id' => $newImage['id'],
                'filename' => $newImage['filename'],
                'total_images' => count($images)
            ]);
            return response()->json([
                'success' => true,
                'image' => $newImage,
                'draft_id' => $draft->id,
                'message' => 'Image uploaded successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Draft image upload failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to upload image: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Delete an image from a package draft
     */
    public function deleteDraftImage(Request $request, string $imageId): JsonResponse
    {
        try {
            $draftId = $request->input('package_draft_id') ?? $request->input('draft_id') ?? session('package_draft_id');
            if (!$draftId) {
                return response()->json(['error' => 'No active draft found'], 400);
            }
            $draft = PackageDraft::find($draftId);
            if (!$draft) {
                return response()->json(['error' => 'Draft not found'], 404);
            }
            $draftData = $draft->draft_data ?? [];
            $images = $draftData['images'] ?? [];
            $imageToDelete = null;
            $updatedImages = [];
            foreach ($images as $image) {
                if ($image['id'] === $imageId) {
                    $imageToDelete = $image;
                } else {
                    $updatedImages[] = $image;
                }
            }
            if (!$imageToDelete) {
                return response()->json(['error' => 'Image not found'], 404);
            }
            // Delete physical files
            $this->imageService->deleteImageFiles($imageToDelete, 'package-drafts');
            // If deleted image was main, set first remaining image as main
            if ($imageToDelete['is_main'] && count($updatedImages) > 0) {
                $updatedImages[0]['is_main'] = true;
            }
            $draftData['images'] = $updatedImages;
            $draft->draft_data = $draftData;
            $draft->save();
            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Draft image deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete image'], 500);
        }
    }
    /**
     * Reorder images in a package draft
     */
    public function reorderDraftImages(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'image_ids' => 'required|array',
                'image_ids.*' => 'required|string',
                'package_draft_id' => 'nullable|string',
                'draft_id' => 'nullable|string', // Keep for backward compatibility
            ]);
            $draftId = $request->input('package_draft_id') ?? $request->input('draft_id') ?? session('package_draft_id');
            $imageIds = $request->input('image_ids');
            if (!$draftId) {
                return response()->json(['error' => 'No active draft found'], 400);
            }
            $draft = PackageDraft::find($draftId);
            if (!$draft) {
                return response()->json(['error' => 'Draft not found'], 404);
            }
            $draftData = $draft->draft_data ?? [];
            $images = $draftData['images'] ?? [];
            // Create a mapping of image ID to image data
            $imageMap = [];
            foreach ($images as $image) {
                $imageMap[$image['id']] = $image;
            }
            // Reorder images based on the provided order
            $reorderedImages = [];
            foreach ($imageIds as $imageId) {
                if (isset($imageMap[$imageId])) {
                    $reorderedImages[] = $imageMap[$imageId];
                }
            }
            $draftData['images'] = $reorderedImages;
            $draft->draft_data = $draftData;
            $draft->save();
            return response()->json([
                'success' => true,
                'message' => 'Images reordered successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Draft image reordering failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to reorder images'], 500);
        }
    }
    /**
     * Set main image for a package draft
     */
    public function setMainDraftImage(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'image_id' => 'required|string',
                'package_draft_id' => 'nullable|string',
                'draft_id' => 'nullable|string', // Keep for backward compatibility
            ]);
            $draftId = $request->input('package_draft_id') ?? $request->input('draft_id') ?? session('package_draft_id');
            $imageId = $request->input('image_id');
            if (!$draftId) {
                return response()->json(['error' => 'No active draft found'], 400);
            }
            $draft = PackageDraft::find($draftId);
            if (!$draft) {
                return response()->json(['error' => 'Draft not found'], 404);
            }
            $draftData = $draft->draft_data ?? [];
            $images = $draftData['images'] ?? [];
            $imageFound = false;
            foreach ($images as &$image) {
                if ($image['id'] === $imageId) {
                    $image['is_main'] = true;
                    $imageFound = true;
                } else {
                    $image['is_main'] = false;
                }
            }
            if (!$imageFound) {
                return response()->json(['error' => 'Image not found'], 404);
            }
            $draftData['images'] = $images;
            $draft->draft_data = $draftData;
            $draft->save();
            return response()->json([
                'success' => true,
                'message' => 'Main image set successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Setting main draft image failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to set main image'], 500);
        }
    }
    /**
     * Process image upload and create different sizes
     */
    private function processImageUpload(UploadedFile $file, string $folder): array
    {
        \Log::info('Starting image processing', [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'folder' => $folder
        ]);
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = "images/{$folder}/" . date('Y/m');
        // Ensure directory exists
        $fullPath = storage_path("app/public/{$path}");
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
            \Log::info('Created directory', ['path' => $fullPath]);
        }
        // Store original image
        $originalPath = "{$path}/{$filename}";
        $file->storeAs("public/{$path}", $filename);
        \Log::info('Stored original image', ['path' => $originalPath]);
        try {
            $sizes = [
                'original' => $originalPath,
                'large' => $this->createImageSize($file, $filename, $path, 1200, 800),
                'medium' => $this->createImageSize($file, $filename, $path, 600, 400),
                'thumbnail' => $this->createImageSize($file, $filename, $path, 300, 200),
            ];
            \Log::info('Created all image sizes successfully', ['sizes' => array_keys($sizes)]);
        } catch (\Exception $e) {
            \Log::error('Failed to create image sizes', ['error' => $e->getMessage()]);
            throw $e;
        }
        return [
            'filename' => $filename,
            'sizes' => $sizes,
        ];
    }
    /**
     * Create resized image
     */
    private function createImageSize(UploadedFile $file, string $filename, string $path, int $maxWidth, int $maxHeight): string
    {
        \Log::info('Creating image size', [
            'filename' => $filename, 
            'dimensions' => "{$maxWidth}x{$maxHeight}",
            'extension' => $file->getClientOriginalExtension()
        ]);
        $extension = strtolower($file->getClientOriginalExtension());
        $sizeSuffix = "_{$maxWidth}x{$maxHeight}";
        $resizedFilename = pathinfo($filename, PATHINFO_FILENAME) . $sizeSuffix . '.' . $extension;
        $resizedPath = "{$path}/{$resizedFilename}";
        $fullResizedPath = storage_path("app/public/{$resizedPath}");
        // Create image resource
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = \imagecreatefromjpeg($file->getPathname());
                break;
            case 'png':
                $image = \imagecreatefrompng($file->getPathname());
                break;
            case 'webp':
                $image = \imagecreatefromwebp($file->getPathname());
                break;
            default:
                throw new \Exception('Unsupported image format');
        }
        if (!$image) {
            throw new \Exception('Failed to create image resource');
        }
        // Get original dimensions
        $originalWidth = \imagesx($image);
        $originalHeight = \imagesy($image);
        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = round($originalWidth * $ratio);
        $newHeight = round($originalHeight * $ratio);
        // Create new image
        $resizedImage = \imagecreatetruecolor($newWidth, $newHeight);
        // Preserve transparency for PNG and WebP
        if ($extension === 'png' || $extension === 'webp') {
            \imagealphablending($resizedImage, false);
            \imagesavealpha($resizedImage, true);
            $transparent = \imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
            \imagefill($resizedImage, 0, 0, $transparent);
        }
        // Resize image
        \imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        // Save resized image
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                \imagejpeg($resizedImage, $fullResizedPath, 85);
                break;
            case 'png':
                \imagepng($resizedImage, $fullResizedPath, 6);
                break;
            case 'webp':
                \imagewebp($resizedImage, $fullResizedPath, 85);
                break;
        }
        // Clean up memory
        \imagedestroy($image);
        \imagedestroy($resizedImage);
        return $resizedPath;
    }
    /**
     * Upload an image for an existing package (for editing)
     */
    public function uploadPackageImage(Request $request, Package $package): JsonResponse
    {
        try {
            $this->ensurePackageOwnership($package);
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // Max 5MB
            ]);
            $uploadedFile = $request->file('image');
            $imageData = $this->processImageUpload($uploadedFile, 'packages');
            $images = $package->images ?? [];
            // Add new image to the array
            $newImage = [
                'id' => Str::uuid()->toString(),
                'filename' => $imageData['filename'],
                'original_name' => $uploadedFile->getClientOriginalName(),
                'sizes' => $imageData['sizes'],
                'is_main' => count($images) === 0, // First image is main by default
                'alt_text' => '',
                'uploaded_at' => now()->toISOString(),
            ];
            $images[] = $newImage;
            $package->images = $images;
            $package->save();
            return response()->json([
                'success' => true,
                'image' => $newImage,
                'message' => 'Image uploaded successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Package image upload failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to upload image: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Delete an image from an existing package
     */
    public function deletePackageImage(Request $request, Package $package, string $imageId): JsonResponse
    {
        try {
            $this->ensurePackageOwnership($package);
            $images = $package->images ?? [];
            $imageToDelete = null;
            $updatedImages = [];
            foreach ($images as $image) {
                if ($image['id'] === $imageId) {
                    $imageToDelete = $image;
                } else {
                    $updatedImages[] = $image;
                }
            }
            if (!$imageToDelete) {
                return response()->json(['error' => 'Image not found'], 404);
            }
            // Delete physical files
            $this->imageService->deleteImageFiles($imageToDelete, 'packages');
            // If deleted image was main, set first remaining image as main
            if ($imageToDelete['is_main'] && count($updatedImages) > 0) {
                $updatedImages[0]['is_main'] = true;
            }
            $package->images = $updatedImages;
            $package->save();
            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Package image deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete image'], 500);
        }
    }
    /**
     * Reorder images for an existing package
     */
    public function reorderPackageImages(Request $request, Package $package): JsonResponse
    {
        try {
            $this->ensurePackageOwnership($package);
            $request->validate([
                'image_ids' => 'required|array',
                'image_ids.*' => 'required|string',
            ]);
            $imageIds = $request->input('image_ids');
            $images = $package->images ?? [];
            // Create a mapping of image ID to image data
            $imageMap = [];
            foreach ($images as $image) {
                $imageMap[$image['id']] = $image;
            }
            // Reorder images based on the provided order
            $reorderedImages = [];
            foreach ($imageIds as $imageId) {
                if (isset($imageMap[$imageId])) {
                    $reorderedImages[] = $imageMap[$imageId];
                }
            }
            $package->images = $reorderedImages;
            $package->save();
            return response()->json([
                'success' => true,
                'message' => 'Images reordered successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Package image reordering failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to reorder images'], 500);
        }
    }
    /**
     * Set main image for an existing package
     */
    public function setMainPackageImage(Request $request, Package $package): JsonResponse
    {
        try {
            $this->ensurePackageOwnership($package);
            $request->validate([
                'image_id' => 'required|string',
            ]);
            $imageId = $request->input('image_id');
            $images = $package->images ?? [];
            $imageFound = false;
            foreach ($images as &$image) {
                if ($image['id'] === $imageId) {
                    $image['is_main'] = true;
                    $imageFound = true;
                } else {
                    $image['is_main'] = false;
                }
            }
            if (!$imageFound) {
                return response()->json(['error' => 'Image not found'], 404);
            }
            $package->images = $images;
            $package->save();
            return response()->json([
                'success' => true,
                'message' => 'Main image set successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Setting main package image failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to set main image'], 500);
        }
    }
    /**
     * Store the final package with all data
     */
    public function store(Request $request)
    {
        $request->validate([
            'final_confirmation' => 'required|accepted',
            'terms_accepted' => 'required|accepted',
        ]);
        try {
            DB::beginTransaction();
            // Check if this is a direct form submission (AJAX) or session-based
            $isDirect = $request->expectsJson() || $request->filled('activities') || $request->filled('selected_hotels') || $request->filled('selected_flights');
            if ($isDirect) {
                // Handle direct form submission with all data in request
                Log::info('Package store - Direct submission detected', [
                    'expects_json' => $request->expectsJson(),
                    'has_activities' => $request->filled('activities'),
                    'has_hotels' => $request->filled('selected_hotels'),
                    'has_flights' => $request->filled('selected_flights')
                ]);
                return $this->storeFromDirectSubmission($request);
            }
            // Get all draft data from session (legacy approach)
            $step1 = session('package_draft.step1');
            $step2 = session('package_draft.step2');
            $step3 = session('package_draft.step3');
            $step4 = session('package_draft.step4');
            if (!$step1 || !$step2 || !$step3 || !$step4) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Package creation session expired. Please start over.'
                    ], 400);
                }
                throw new \Exception('Package creation session expired. Please start over.');
            }
            // Get draft images if they exist
            $draftImages = [];
            $draftId = session('package_draft_id');
            if ($draftId) {
                $draft = PackageDraft::find($draftId);
                if ($draft && isset($draft->draft_data['images'])) {
                    $draftImages = $draft->draft_data['images'];
                    \Log::info('Package store - Before image transfer', [
                        'draft_images_count' => count($draftImages),
                        'first_image_structure' => count($draftImages) > 0 ? array_keys($draftImages[0]) : 'no_images'
                    ]);
                    // Transfer images from draft folder to package folder
                    $draftImages = $this->imageService->transferImagesFromDraft($draftImages);
                    \Log::info('Package store - After image transfer', [
                        'transferred_images_count' => count($draftImages),
                        'first_transferred_image' => count($draftImages) > 0 ? array_keys($draftImages[0]) : 'no_transferred_images'
                    ]);
                }
            } else {
                \Log::warning('Package store - No draft ID found in session', [
                    'all_session_data' => session()->all()
                ]);
            }
            // Prepare package data
            $packageData = array_merge($step1, $step4, [
                'creator_id' => Auth::id(),
                'approval_status' => Package::APPROVAL_PENDING,
                'status' => Package::STATUS_DRAFT,
                'images' => $draftImages, // Add images to package data
            ]);
            \Log::info('Package store - Final package data prepared', [
                'package_data_keys' => array_keys($packageData),
                'images_in_package_data' => isset($packageData['images']) ? count($packageData['images']) : 'no_images_key',
                'images_data' => $packageData['images'] ?? 'no_images'
            ]);
            // Create the package
            $package = Package::create($packageData);
            \Log::info('Package store - Package created', [
                'package_id' => $package->id,
                'package_images_count' => $package->getImageCount(),
                'package_images_data' => $package->images
            ]);
            // Attach hotels
            if (isset($step2['selected_hotels'])) {
                foreach ($step2['selected_hotels'] as $hotelData) {
                    $package->hotels()->attach($hotelData['hotel_id'], [
                        'is_primary' => $hotelData['is_primary'] ?? false,
                        'nights' => $hotelData['nights'],
                        'room_type' => $hotelData['room_type'],
                        'rooms_needed' => $hotelData['rooms_needed'],
                        'markup_percentage' => $hotelData['markup_percentage'] ?? 0,
                        'source_type' => 'platform',
                        'confirmation_status' => 'pending',
                        'display_order' => 0,
                    ]);
                }
            }
            // Attach transport services
            if (isset($step2['selected_transport'])) {
                foreach ($step2['selected_transport'] as $transportData) {
                    $package->transportServices()->attach($transportData['transport_id'], [
                        'transport_category' => $transportData['category'],
                        'pickup_location' => $transportData['pickup_location'],
                        'dropoff_location' => $transportData['dropoff_location'],
                        'day_of_itinerary' => $transportData['day_of_itinerary'],
                        'markup_percentage' => $transportData['markup_percentage'] ?? 0,
                        'passengers_count' => $package->max_participants ?? 10,
                        'source_type' => 'platform',
                        'confirmation_status' => 'pending',
                        'display_order' => 0,
                    ]);
                }
            }
            // Attach flights
            if (isset($step2['selected_flights'])) {
                foreach ($step2['selected_flights'] as $flightData) {
                    $package->flights()->attach($flightData['flight_id'], [
                        'flight_type' => $flightData['flight_type'],
                        'seats_allocated' => $flightData['seats_allocated'],
                        'markup_percentage' => $flightData['markup_percentage'] ?? 0,
                        'is_required' => true,
                    ]);
                }
            }
            // Create activities
            if (isset($step3['activities'])) {
                foreach ($step3['activities'] as $activityData) {
                    PackageActivity::create(array_merge($activityData, [
                        'package_id' => $package->id,
                        'is_active' => true,
                        'availability_status' => PackageActivity::AVAILABILITY_AVAILABLE,
                    ]));
                }
            }
            // Update pricing
            $package->updatePricing();
            DB::commit();
            // Clear session data and cleanup draft
            session()->forget('package_draft');
            // Clean up draft record if it exists
            if ($draftId) {
                $draft = PackageDraft::find($draftId);
                if ($draft) {
                    $draft->delete();
                }
                session()->forget('package_draft_id');
            }
            return redirect()->route('b2b.travel-agent.packages.show', $package)
                ->with('success', 'Package created successfully and submitted for approval!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to create package: ' . $e->getMessage());
        }
    }
    /**
     * Handle direct form submission with all data in the request
     */
    private function storeFromDirectSubmission(Request $request)
    {
        try {
            Log::info('Package store - Processing direct submission', [
                'request_keys' => array_keys($request->all()),
                'activities_count' => is_array($request->input('activities')) ? count($request->input('activities')) : 0,
                'hotels_count' => is_array($request->input('selected_hotels')) ? count($request->input('selected_hotels')) : 0,
                'flights_count' => is_array($request->input('selected_flights')) ? count($request->input('selected_flights')) : 0,
            ]);
            // Get draft reference (but don't transfer yet)
            $draftId = $request->input('draft_id') ?: $request->input('package_draft_id');
            $draftImages = [];
            if ($draftId) {
                $draft = PackageDraft::find($draftId);
                if ($draft && isset($draft->draft_data['images'])) {
                    $draftImages = $draft->draft_data['images'];
                }
            }
            // Type mapping
            $typeMapping = [
                'cultural' => Package::TYPE_CULTURAL,
                'adventure' => Package::TYPE_ADVENTURE,
                'leisure' => Package::TYPE_LEISURE,
                'business' => Package::TYPE_BUSINESS,
                'family' => Package::TYPE_FAMILY,
                'luxury' => Package::TYPE_LUXURY,
                'budget' => Package::TYPE_BUDGET,
                'honeymoon' => Package::TYPE_HONEYMOON,
                'religious' => Package::TYPE_RELIGIOUS,
                'wellness' => Package::TYPE_WELLNESS,
            ];
            $formPackageType = $request->input('package_type');
            $mappedType = $typeMapping[$formPackageType] ?? Package::TYPE_STANDARD;
            // Prepare package data
            $packageData = [
                'creator_id' => Auth::id(),
                'name' => $request->input('name'),
                'description' => $request->input('short_description') ?: $request->input('description') ?: 'Package description',
                'short_description' => $request->input('short_description'),
                'detailed_description' => $request->input('detailed_description'),
                'type' => $mappedType,
                'destinations' => is_array($request->input('destinations')) ? $request->input('destinations') : explode(',', $request->input('destinations') ?: ''),
                'categories' => $request->input('categories', []),
                'difficulty_level' => $request->input('difficulty_level'),
                'currency' => $request->input('currency'),
                'base_price' => $request->input('base_price'),
                'child_price' => $request->input('child_price'),
                'child_discount_percent' => $request->input('child_discount_percent'),
                'child_price_disabled' => $request->boolean('child_price_disabled'),
                'child_discount_percent_disabled' => $request->boolean('child_discount_percent_disabled'),
                'infant_price' => $request->input('infant_price'),
                'single_supplement' => $request->input('single_supplement'),
                'commission_rate' => $request->input('commission_rate', 0),
                'platform_commission' => $request->input('commission_rate', 0),
                'payment_terms' => $request->input('payment_terms'),
                'cancellation_policy' => $request->input('cancellation_policy'),
                'total_price' => $request->input('total_price', 0),
                'terms_accepted' => $request->boolean('terms_accepted') ? ['accepted' => true, 'timestamp' => now()] : [],
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'duration' => $request->input('duration_days', 1),
                'max_participants' => $request->input('max_participants'),
                'min_participants' => $request->input('min_participants'),
                'min_booking_days' => $request->input('min_booking_days', 3),
                'requires_deposit' => $request->boolean('requires_deposit'),
                'includes_meals' => $request->boolean('includes_meals'),
                'includes_accommodation' => $request->boolean('includes_accommodation'),
                'includes_transport' => $request->boolean('includes_transport'),
                'includes_guide' => $request->boolean('includes_guide'),
                'includes_flights' => $request->boolean('includes_flights'),
                'includes_activities' => $request->boolean('includes_activities'),
                'free_cancellation' => $request->boolean('free_cancellation'),
                'instant_confirmation' => $request->boolean('instant_confirmation'),
                'hotel_source' => $request->input('hotel_source', 'platform'),
                'transport_source' => $request->input('transport_source', 'platform'),
                'flight_source' => $request->input('flight_source', 'own'),
                'status' => $request->input('status', Package::STATUS_DRAFT),
                'approval_status' => Package::APPROVAL_PENDING,
                'images' => [], // empty for now, fill after transfer
            ];
            // Step 1: Create the package
            $package = Package::create($packageData);
            // Step 2: Process activities, extras, providers, pricing
            $this->processSelectedProviders($package, $request);
            $package->updatePricing();
            $activities = $request->input('activities', []);
            if (is_array($activities)) {
                foreach ($activities as $index => $activityData) {
                    if (!empty($activityData['activity_name'])) {
                        PackageActivity::create([
                            'package_id' => $package->id,
                            'activity_name' => $activityData['activity_name'],
                            'description' => $activityData['description'] ?? '',
                            'day_number' => $activityData['day_number'] ?? ($index + 1),
                            'location' => $activityData['location'] ?? '',
                            'duration_hours' => $activityData['duration_hours'] ?? 0,
                            'category' => $activityData['category'] ?? 'general',
                            'is_included' => isset($activityData['is_included'])
                                            ? ($activityData['is_included'] ? 1 : 0)
                                            : 1,
                            'is_optional' => isset($activityData['is_optional'])
                                            ? ($activityData['is_optional'] ? 1 : 0)
                                            : 0,
                            'additional_cost' => $activityData['additional_cost'] ?? 0,
                            'is_active' => true,
                            'availability_status' => PackageActivity::AVAILABILITY_AVAILABLE,
                            'display_order' => $index,
                        ]);
                    }
                }
            }
            // Step 3: Transfer images AFTER creation is successful
            if (!empty($draftImages)) {
                $transferredImages = $this->imageService->transferImagesFromDraft($draftImages, $package->id);
                $package->update(['images' => $transferredImages]);
            }
            // Step 4: Clean up draft
            if ($draftId && $draft = PackageDraft::find($draftId)) {
                $draft->delete();
            }
            DB::commit();
            Log::info('Package store - Success', [
                'package_id' => $package->id,
                'activities_created' => $package->packageActivities()->count(),
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Package created successfully!',
                    'package_id' => $package->id,
                    'redirect_url' => route('b2b.travel-agent.packages.show', $package),
                ]);
            }
            return redirect()
                ->route('b2b.travel-agent.packages.show', $package)
                ->with('success', 'Package created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Package store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create package: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Failed to create package: ' . $e->getMessage());
        }
    }
    /**
     * Process selected providers from the request
     */
    private function processSelectedProviders(Package $package, Request $request)
    {
        // Process hotels
        $selectedHotels = $request->input('selected_hotels', []);
        if (is_array($selectedHotels)) {
            foreach ($selectedHotels as $hotelData) {
                if (is_array($hotelData) && isset($hotelData['id'])) {
                    // Convert string booleans to actual booleans
                    $isPrimary = isset($hotelData['is_primary']) ? 
                        (is_bool($hotelData['is_primary']) ? $hotelData['is_primary'] : 
                         (in_array($hotelData['is_primary'], ['true', '1', 1, true], true))) : false;
                    $package->hotels()->attach($hotelData['id'], [
                        'is_primary' => $isPrimary ? 1 : 0, // Explicit integer conversion
                        'nights' => (int)($hotelData['nights'] ?? 1),
                        'room_type' => $hotelData['room_type'] ?? 'standard',
                        'rooms_needed' => (int)($hotelData['rooms_needed'] ?? 1),
                        'markup_percentage' => (float)($hotelData['markup_percentage'] ?? 0),
                        'source_type' => $hotelData['source_type'] ?? 'platform',
                        'confirmation_status' => 'pending',
                        'display_order' => 0,
                    ]);
                }
            }
        }
        // Process flights
        $selectedFlights = $request->input('selected_flights', []);
        if (is_array($selectedFlights)) {
            foreach ($selectedFlights as $flightData) {
                if (is_array($flightData) && isset($flightData['id'])) {
                    // Convert boolean values to integers for database
                    $isRequired = isset($flightData['is_required']) ?
                        (is_bool($flightData['is_required']) ? $flightData['is_required'] :
                         (in_array($flightData['is_required'], ['true', '1', 1, true], true))) : true;
                    // Map flight type to correct enum values
                    $flightTypeMapping = [
                        'departure' => 'outbound',
                        'outbound' => 'outbound',
                        'return' => 'return',
                        'arrival' => 'return',
                        'connecting' => 'connecting',
                    ];
                    $rawFlightType = $flightData['flight_type'] ?? 'departure';
                    $mappedFlightType = $flightTypeMapping[$rawFlightType] ?? 'outbound';
                    $package->flights()->attach($flightData['id'], [
                        'flight_type' => $mappedFlightType,
                        'seats_allocated' => (int)($flightData['seats_allocated'] ?? 1),
                        'markup_percentage' => (float)($flightData['markup_percentage'] ?? 0),
                        'is_required' => $isRequired ? 1 : 0,
                    ]);
                }
            }
        }
        // Process transport
        $selectedTransport = $request->input('selected_transport', []);
        if (is_array($selectedTransport)) {
            foreach ($selectedTransport as $transportData) {
                if (is_array($transportData) && isset($transportData['id'])) {
                    // Map transport category to correct enum values
                    $transportCategoryMapping = [
                        'transfer' => 'airport_transfer',
                        'airport_transfer' => 'airport_transfer',
                        'city_transport' => 'city_transport',
                        'intercity' => 'intercity',
                        'pilgrimage_sites' => 'pilgrimage_sites',
                        'custom' => 'custom',
                        // Additional mappings for common variations
                        'airport' => 'airport_transfer',
                        'city' => 'city_transport',
                        'pilgrimage' => 'pilgrimage_sites',
                    ];
                    $rawCategory = $transportData['category'] ?? 'transfer';
                    $mappedCategory = $transportCategoryMapping[$rawCategory] ?? 'airport_transfer';
                    $package->transportServices()->attach($transportData['id'], [
                        'transport_category' => $mappedCategory,
                        'pickup_location' => $transportData['pickup_location'] ?? '',
                        'dropoff_location' => $transportData['dropoff_location'] ?? '',
                        'day_of_itinerary' => (int)($transportData['day_of_itinerary'] ?? 1),
                        'markup_percentage' => (float)($transportData['markup_percentage'] ?? 0),
                        'passengers_count' => (int)($package->max_participants ?? 10),
                        'source_type' => $transportData['source_type'] ?? 'platform',
                        'confirmation_status' => 'pending',
                        'display_order' => 0,
                    ]);
                }
            }
        }
    }
    /**
     * Display the specified package
     */
    public function show(Package $package): View
    {
        $this->ensurePackageOwnership($package);
        $package->load([
            'flights.provider', 
            'hotels', 
            'transportServices.provider', 
            'packageActivities' => function ($query) {
                $query->orderBy('day_number')->orderBy('display_order');
            },
            'approvedBy'
        ]);
        // Increment view count
        $package->increment('views_count');
        $packageStats = [
            'total_flights' => $package->flights->count(),
            'total_hotels' => $package->hotels->count(),
            'total_transport' => $package->transportServices->count(),
            'total_activities' => $package->packageActivities->count(),
            'total_cost' => $package->calculateComprehensivePrice(),
            'duration_days' => $package->duration,
            'max_participants' => $package->max_participants ?? 'Unlimited',
            'availability_percentage' => $package->getAvailabilityPercentage(),
            'estimated_commission' => $package->calculateComprehensivePrice() * ($package->platform_commission / 100),
        ];
        return view('b2b.travel-agent.packages.show', compact('package', 'packageStats'));
    }
    /**
     * Debug package images structure
     */
    public function debugImages(Package $package)
    {
        $this->ensurePackageOwnership($package);
        return response()->json([
            'package_id' => $package->id,
            'has_images' => $package->hasImages(),
            'image_count' => $package->getImageCount(),
            'raw_images' => $package->images,
            'images_with_urls' => $package->getImagesWithUrls(),
            'main_image' => $package->getMainImage(),
        ]);
    }
    /**
     * Show the form for editing the specified package
     */
    public function edit(Package $package): View
    {
        $this->ensurePackageOwnership($package);
        $package->load(['flights', 'hotels', 'transportServices', 'packageActivities']);
        $data = [
            'package' => $package,
            'packageTypes' => Package::getPackageTypes(),
            'currencies' => [
                'SAR' => 'Saudi Riyal (SAR)',
                'USD' => 'US Dollar (USD)',
                'EUR' => 'Euro (EUR)',
                'GBP' => 'British Pound (GBP)',
                'AED' => 'UAE Dirham (AED)'
            ],
            'providerSources' => [
                Package::SOURCE_PLATFORM => 'Use Platform Providers',
                Package::SOURCE_EXTERNAL => 'Use External Providers',
                Package::SOURCE_MIXED => 'Mix of Both'
            ],
            'flightSources' => [
                Package::SOURCE_OWN => 'My Own Flights',
                Package::SOURCE_PLATFORM => 'Platform Flights',
                Package::SOURCE_EXTERNAL => 'External Flights',
                Package::SOURCE_MIXED => 'Mixed Sources'
            ]
        ];
        return view('b2b.travel-agent.packages.edit', $data);
    }
    /**
     * Update the specified package
     */
    public function update(UpdatePackageRequest $request, Package $package): RedirectResponse
    {
        $this->ensurePackageOwnership($package);
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            // Handle image management
            $this->handleImageUpdates($request, $package);
            // Update the package
            $package->update($validated);
            // Update pricing
            $package->updatePricing();
            DB::commit();
            return redirect()->route('b2b.travel-agent.packages.show', $package)
                ->with('success', 'Package updated successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to update package: ' . $e->getMessage());
        }
    }
    /**
     * Remove the specified package
     */
    public function destroy(Package $package): RedirectResponse
    {
        $this->ensurePackageOwnership($package);
        try {
            DB::beginTransaction();
            // Delete package images from storage
            if ($package->images) {
                foreach ($package->images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }
            // Detach relationships
            $package->flights()->detach();
            $package->hotels()->detach();
            $package->transportServices()->detach();
            // Delete activities
            $package->packageActivities()->delete();
            // Delete the package
            $package->delete();
            DB::commit();
            return redirect()->route('b2b.travel-agent.packages.index')
                ->with('success', 'Package deleted successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Failed to delete package: ' . $e->getMessage());
        }
    }
    /**
     * Toggle package status
     */
    public function toggleStatus(Package $package): RedirectResponse
    {
        $this->ensurePackageOwnership($package);
        $newStatus = $package->status === Package::STATUS_ACTIVE 
            ? Package::STATUS_INACTIVE 
            : Package::STATUS_ACTIVE;
        $package->update(['status' => $newStatus]);
        $statusText = $newStatus === Package::STATUS_ACTIVE ? 'activated' : 'deactivated';
        return back()->with('success', "Package {$statusText} successfully!");
    }
    /**
     * Clean destinations array to prevent duplication
     */
    private function cleanDestinations(array $destinations): array
    {
        $cleanDestinations = [];
        foreach ($destinations as $destination) {
            if (is_string($destination)) {
                // Split comma-separated destinations
                $parts = explode(',', $destination);
                foreach ($parts as $part) {
                    $trimmed = trim($part);
                    if (!empty($trimmed)) {
                        $cleanDestinations[] = $trimmed;
                    }
                }
            }
        }
        // Remove duplicates and empty values, preserve original casing
        $uniqueDestinations = [];
        foreach ($cleanDestinations as $dest) {
            $lowerDest = strtolower($dest);
            if (!in_array($lowerDest, array_map('strtolower', $uniqueDestinations))) {
                $uniqueDestinations[] = $dest;
            }
        }
        \Log::info('Destinations cleaned', [
            'original' => $destinations,
            'cleaned' => $uniqueDestinations
        ]);
        return $uniqueDestinations;
    }
    /**
     * Process empty provider array indicators and clear old data
     */
    private function processEmptyProviderArrays(&$draftData)
    {
        $providerTypes = ['selected_hotels', 'selected_flights', 'selected_transport'];
        foreach ($providerTypes as $providerType) {
            $emptyKey = $providerType . '_empty';
            // If empty indicator is present, ensure the provider array is empty
            if (isset($draftData[$emptyKey]) && $draftData[$emptyKey] === 'true') {
                $draftData[$providerType] = []; // Set to empty array
                unset($draftData[$emptyKey]); // Remove the indicator
            }
        }
    }
    /**
     * Preserve approved provider statuses when merging frontend draft data
     */
    private function preserveApprovedProviders(array $existingData, array $newData): array
    {
        // Start with new data - this ensures destinations and other fields are replaced, not merged
        $mergedData = $newData;
        // Preserve images from existing data - images are managed separately
        if (isset($existingData['images']) && !empty($existingData['images'])) {
            // Always preserve existing images unless new images are explicitly provided
            $mergedData['images'] = $existingData['images'];
            \Log::info('Preserved existing images in draft merge', [
                'existing_images_count' => count($existingData['images']),
                'new_data_has_images' => isset($newData['images']),
                'new_images_count' => isset($newData['images']) ? count($newData['images']) : 0
            ]);
        } else if (isset($newData['images']) && !empty($newData['images'])) {
            // If no existing images but new data has images, use them
            $mergedData['images'] = $newData['images'];
            \Log::info('Using new images in draft merge', [
                'new_images_count' => count($newData['images'])
            ]);
        } else {
            // No images in either existing or new data
            $mergedData['images'] = [];
            \Log::info('No images found in either existing or new data', [
                'existing_has_images' => isset($existingData['images']),
                'new_has_images' => isset($newData['images'])
            ]);
        }
        // For destinations, always use the new data (no merging)
        if (isset($newData['destinations'])) {
            $mergedData['destinations'] = $newData['destinations'];
        }
        // For categories, always use the new data (no merging)
        if (isset($newData['categories'])) {
            $mergedData['categories'] = $newData['categories'];
        }
        // For pricing data including optional extras, always use the new data
        $pricingFields = [
            'base_price', 'child_price', 'child_discount_percent', 'infant_price',
            'single_supplement', 'commission_rate', 'payment_terms', 'cancellation_policy',
            'min_booking_days', 'requires_deposit', 'deposit_amount', 'total_price',
            'optional_extras', 'pricing_data'
        ];
        foreach ($pricingFields as $field) {
            if (isset($newData[$field])) {
                $mergedData[$field] = $newData[$field];
            }
        }
        \Log::info('Preserved pricing data in draft merge', [
            'pricing_fields_in_new_data' => array_filter($pricingFields, function($field) use ($newData) {
                return isset($newData[$field]);
            }),
            'optional_extras_preserved' => isset($newData['optional_extras']),
            'optional_extras_count' => isset($newData['optional_extras']) ? count($newData['optional_extras']) : 0
        ]);
        // Provider types to check
        $providerTypes = ['selected_hotels', 'selected_flights', 'selected_transport'];
        foreach ($providerTypes as $providerType) {
            if (!isset($existingData[$providerType]) || !isset($newData[$providerType])) {
                continue;
            }
            $existingProviders = $existingData[$providerType];
            $newProviders = $newData[$providerType];
            // Merge providers, preserving approved statuses
            foreach ($newProviders as $index => &$newProvider) {
                // Find matching provider in existing data
                $matchingProvider = collect($existingProviders)->first(function ($existing) use ($newProvider) {
                    // Match by service_request_id or id
                    return (isset($existing['service_request_id']) && 
                            isset($newProvider['service_request_id']) &&
                            (string)$existing['service_request_id'] === (string)$newProvider['service_request_id']) ||
                           (isset($existing['id']) && 
                            isset($newProvider['id']) &&
                            (string)$existing['id'] === (string)$newProvider['id']);
                });
                if ($matchingProvider && 
                    isset($matchingProvider['service_request_status']) && 
                    $matchingProvider['service_request_status'] === 'approved') {
                    // Preserve approved status and related fields
                    $newProvider['service_request_status'] = 'approved';
                    $newProvider['approved_at'] = $matchingProvider['approved_at'] ?? null;
                    $newProvider['offered_price'] = $matchingProvider['offered_price'] ?? null;
                    $newProvider['currency'] = $matchingProvider['currency'] ?? null;
                    $newProvider['provider_notes'] = $matchingProvider['provider_notes'] ?? null;
                    $newProvider['terms_conditions'] = $matchingProvider['terms_conditions'] ?? null;
                    \Log::info('Preserved approved provider status in draft merge', [
                        'provider_type' => $providerType,
                        'provider_id' => $newProvider['id'] ?? 'unknown',
                        'service_request_id' => $newProvider['service_request_id'] ?? 'unknown',
                        'status' => 'approved'
                    ]);
                }
            }
            $mergedData[$providerType] = $newProviders;
        }
        return $mergedData;
    }
    /**
     * Additional safeguard to ensure images are never lost during draft updates
     */
    private function ensureImagesPreserved(&$mergedData, $existingData, $newData)
    {
        // If merged data doesn't have images but existing data does, restore them
        if ((!isset($mergedData['images']) || empty($mergedData['images'])) && 
            isset($existingData['images']) && !empty($existingData['images'])) {
            $mergedData['images'] = $existingData['images'];
            \Log::warning('Images were lost during merge - restored from existing data', [
                'restored_images_count' => count($existingData['images']),
                'merged_data_had_images' => isset($mergedData['images']),
                'existing_data_had_images' => isset($existingData['images']),
                'new_data_had_images' => isset($newData['images'])
            ]);
        }
        // Log final image state
        \Log::info('Final image preservation check', [
            'final_images_count' => isset($mergedData['images']) ? count($mergedData['images']) : 0,
            'has_images' => isset($mergedData['images']) && !empty($mergedData['images'])
        ]);
    }
    /**
     * Save package as draft during multi-step creation
     */
    public function saveDraft(Request $request): JsonResponse
    {
        try {
            $draftData = $request->all();
            $userId = Auth::id();
            // Log the incoming request data for debugging
            \Log::info('Draft save request received', [
                'user_id' => $userId,
                'current_step' => $request->get('current_step', 1),
                'draft_data_keys' => array_keys($draftData),
                'has_activities' => isset($draftData['activities']),
                'activities_count' => isset($draftData['activities']) ? count($draftData['activities']) : 0,
                'has_selected_hotels' => isset($draftData['selected_hotels']),
                'selected_hotels_count' => isset($draftData['selected_hotels']) ? count($draftData['selected_hotels']) : 0,
                'has_selected_flights' => isset($draftData['selected_flights']),
                'selected_flights_count' => isset($draftData['selected_flights']) ? count($draftData['selected_flights']) : 0,
                'has_selected_transport' => isset($draftData['selected_transport']),
                'selected_transport_count' => isset($draftData['selected_transport']) ? count($draftData['selected_transport']) : 0,
                'has_optional_extras' => isset($draftData['optional_extras']),
                'optional_extras_count' => isset($draftData['optional_extras']) ? count($draftData['optional_extras']) : 0,
                'has_images_in_request' => isset($draftData['images']),
                'request_images_count' => isset($draftData['images']) ? count($draftData['images']) : 0,
                'total_data_size' => strlen(json_encode($draftData)),
                'has_images_in_request' => isset($draftData['images']),
                'request_images_count' => isset($draftData['images']) ? count($draftData['images']) : 0,
                'selected_hotels_data' => isset($draftData['selected_hotels']) ? $draftData['selected_hotels'] : null,
                'selected_flights_data' => isset($draftData['selected_flights']) ? $draftData['selected_flights'] : null,
                'selected_transport_data' => isset($draftData['selected_transport']) ? $draftData['selected_transport'] : null,
            ]);
            // Remove non-draft specific data
            unset($draftData['_token']);
            unset($draftData['_method']);
            // Handle existing_images from form submission - convert back to images array
            if (isset($draftData['existing_images']) && is_array($draftData['existing_images'])) {
                $existingImages = [];
                foreach ($draftData['existing_images'] as $imageData) {
                    if (isset($imageData['id']) && !empty($imageData['id'])) {
                        // Convert boolean strings back to booleans
                        $imageData['is_main'] = ($imageData['is_main'] ?? '0') === '1';
                        $existingImages[] = $imageData;
                    }
                }
                $draftData['images'] = $existingImages;
                unset($draftData['existing_images']); // Remove the original field
                \Log::info('Converted existing_images from form submission', [
                    'converted_images_count' => count($existingImages)
                ]);
            }
            // Handle empty provider array indicators
            $this->processEmptyProviderArrays($draftData);
            // Clean up destinations to prevent duplication
            if (isset($draftData['destinations'])) {
                \Log::info('Processing destinations before cleaning', [
                    'original_destinations' => $draftData['destinations'],
                    'is_array' => is_array($draftData['destinations']),
                    'type' => gettype($draftData['destinations'])
                ]);
                if (is_array($draftData['destinations'])) {
                    $draftData['destinations'] = $this->cleanDestinations($draftData['destinations']);
                }
            }
            // Clean up categories to prevent duplication
            if (isset($draftData['categories']) && is_array($draftData['categories'])) {
                $draftData['categories'] = array_unique(array_filter($draftData['categories']));
            }
            // Extract and store pricing data separately
            $pricingFields = [
                'base_price', 'child_price', 'child_discount_percent', 'infant_price',
                'single_supplement', 'commission_rate', 'payment_terms', 'cancellation_policy',
                'min_booking_days', 'requires_deposit', 'deposit_amount', 'total_price',
                'child_price_disabled', 'child_discount_percent_disabled', 'optional_extras'
            ];
            $pricingData = [];
            foreach ($pricingFields as $field) {
                if (isset($draftData[$field])) {
                    $pricingData[$field] = $draftData[$field];
                }
            }
            // Handle optional extras specifically - clean up and save them
            if (isset($draftData['optional_extras']) && is_array($draftData['optional_extras'])) {
                $cleanExtras = [];
                foreach ($draftData['optional_extras'] as $extra) {
                    if (is_array($extra) && !empty($extra['name']) && isset($extra['price'])) {
                        $cleanExtras[] = [
                            'name' => trim($extra['name']),
                            'price' => floatval($extra['price']),
                            'type' => $extra['type'] ?? 'per_person'
                        ];
                    }
                }
                // Save cleaned extras directly in draft data
                $draftData['optional_extras'] = $cleanExtras;
                $pricingData['optional_extras'] = $cleanExtras;
            }
            \Log::info('Pricing data extracted', [
                'pricing_fields_found' => count($pricingData),
                'optional_extras_count' => isset($draftData['optional_extras']) ? count($draftData['optional_extras']) : 0
            ]);
            // Add pricing data to main draft data
            if (!empty($pricingData)) {
                $draftData['pricing_data'] = $pricingData;
            }
            // Generate a name for the draft
            $draftName = $draftData['name'] ?? 'Package Draft ' . now()->format('M j, Y g:i A');
            // Check if we're updating an existing draft
            // Prioritize package_draft_id over draft_id for consistency
            $draftId = $request->package_draft_id ?: $request->draft_id;
            \Log::info('About to save/update draft', [
                'has_draft_id' => $request->filled('draft_id'),
                'has_package_draft_id' => $request->filled('package_draft_id'),
                'draft_id' => $request->draft_id,
                'package_draft_id' => $request->package_draft_id,
                'resolved_draft_id' => $draftId,
                'draft_name' => $draftName
            ]);
            if ($draftId && is_numeric($draftId)) {
                \Log::info('Attempting to update existing draft', ['resolved_draft_id' => $draftId]);
                $draft = \App\Models\PackageDraft::where('id', $draftId)
                    ->where('user_id', $userId)
                    ->first();
                \Log::info('Draft lookup result', [
                    'draft_found' => !!$draft,
                    'draft_id_searched' => $draftId,
                    'user_id_searched' => $userId,
                    'existing_draft_id' => $draft ? $draft->id : null
                ]);
                if ($draft) {
                    \Log::info('Found existing draft, updating', ['draft_id' => $draft->id]);
                    try {
                        // Log existing draft data before merge
                        $existingImages = $draft->draft_data['images'] ?? [];
                        \Log::info('Before preserveApprovedProviders merge', [
                            'draft_id' => $draft->id,
                            'existing_images_count' => count($existingImages),
                            'request_images_count' => isset($draftData['images']) ? count($draftData['images']) : 0,
                            'existing_data_keys' => array_keys($draft->draft_data ?? []),
                            'request_data_keys' => array_keys($draftData)
                        ]);
                        // Preserve approved provider statuses when updating draft
                        $mergedData = $this->preserveApprovedProviders($draft->draft_data ?? [], $draftData);
                        // Additional safeguard: Ensure images are never lost
                        $this->ensureImagesPreserved($mergedData, $draft->draft_data ?? [], $draftData);
                        \Log::info('After preserveApprovedProviders merge', [
                            'draft_id' => $draft->id,
                            'merged_data_keys' => array_keys($mergedData),
                            'merged_data_has_images' => isset($mergedData['images']),
                            'merged_images_count' => isset($mergedData['images']) ? count($mergedData['images']) : 0,
                            'merged_data_has_optional_extras' => isset($mergedData['optional_extras']),
                            'merged_data_optional_extras_count' => isset($mergedData['optional_extras']) ? count($mergedData['optional_extras']) : 0,
                            'merged_data_has_pricing_data' => isset($mergedData['pricing_data']),
                            'merged_data_pricing_data_has_optional_extras' => isset($mergedData['pricing_data']) && isset($mergedData['pricing_data']['optional_extras']),
                            'merged_data_size' => strlen(json_encode($mergedData)),
                            'current_step' => $request->get('current_step', 1)
                        ]);
                        $result = $draft->update([
                            'name' => $draftName,
                            'draft_data' => $mergedData,
                            'current_step' => $request->get('current_step', 1),
                            'last_accessed_at' => now(),
                            'expires_at' => now()->addDays(7) // Extend expiry
                        ]);
                        \Log::info('Draft update result', [
                            'result' => $result, 
                            'draft_id' => $draft->id,
                            'affected_rows' => $result ? 1 : 0
                        ]);
                        if (!$result) {
                            \Log::error('Draft update returned false - no database changes made', [
                                'draft_id' => $draft->id,
                                'user_id' => $userId
                            ]);
                            throw new \Exception('Database update operation returned false - no changes made');
                        }
                    } catch (\Illuminate\Database\QueryException $e) {
                        \Log::error('Database error during draft update', [
                            'draft_id' => $draft->id,
                            'sql_error' => $e->getMessage(),
                            'sql_code' => $e->getCode(),
                            'bindings' => $e->getBindings()
                        ]);
                        throw $e;
                    }
                } else {
                    \Log::info('Draft not found, creating new one');
                    try {
                        // Create new draft if specified draft doesn't exist
                        $draft = \App\Models\PackageDraft::create([
                            'user_id' => $userId,
                            'name' => $draftName,
                            'slug' => \Illuminate\Support\Str::slug($draftName) . '-' . time(),
                            'draft_data' => $draftData,
                            'current_step' => $request->get('current_step', 1),
                            'step_status' => [],
                            'last_accessed_at' => now(),
                            'expires_at' => now()->addDays(7)
                        ]);
                        if (!$draft || !$draft->id) {
                            \Log::error('Draft creation failed - no draft object returned');
                            throw new \Exception('Failed to create draft record - create operation returned null or invalid object');
                        }
                        \Log::info('New draft created successfully', [
                            'draft_id' => $draft->id,
                            'draft_name' => $draft->name,
                            'user_id' => $userId
                        ]);
                    } catch (\Illuminate\Database\QueryException $e) {
                        \Log::error('Database error during new draft creation', [
                            'sql_error' => $e->getMessage(),
                            'sql_code' => $e->getCode(),
                            'bindings' => $e->getBindings(),
                            'user_id' => $userId,
                            'draft_name' => $draftName
                        ]);
                        throw $e;
                    }
                }
            } else {
                \Log::info('No draft_id provided, creating new draft');
                try {
                    // Create new draft
                    $draft = \App\Models\PackageDraft::create([
                        'user_id' => $userId,
                        'name' => $draftName,
                        'slug' => \Illuminate\Support\Str::slug($draftName) . '-' . time(),
                        'draft_data' => $draftData,
                        'current_step' => $request->get('current_step', 1),
                        'step_status' => [],
                        'last_accessed_at' => now(),
                        'expires_at' => now()->addDays(7)
                    ]);
                    if (!$draft || !$draft->id) {
                        \Log::error('Draft creation failed - no draft object returned');
                        throw new \Exception('Failed to create draft record - create operation returned null or invalid object');
                    }
                    \Log::info('New draft created successfully', [
                        'draft_id' => $draft->id,
                        'draft_name' => $draft->name,
                        'user_id' => $userId
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    \Log::error('Database error during new draft creation (no draft_id)', [
                        'sql_error' => $e->getMessage(),
                        'sql_code' => $e->getCode(),
                        'bindings' => $e->getBindings(),
                        'user_id' => $userId,
                        'draft_name' => $draftName
                    ]);
                    throw $e;
                }
            }
            \Log::info('Draft saved successfully', [
                'draft_id' => $draft->id,
                'draft_name' => $draft->name,
                'user_id' => $userId
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Draft saved successfully!',
                'draft_id' => $draft->id,
                'draft_name' => $draft->name
            ]);
        } catch (\Exception $e) {
            \Log::error('Draft save error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception_type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data_keys' => array_keys($request->all()),
                'request_size' => strlen(json_encode($request->all())),
                'has_draft_id' => $request->filled('draft_id'),
                'has_package_draft_id' => $request->filled('package_draft_id')
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save draft: ' . $e->getMessage(),
                'debug_info' => app()->environment('local') ? [
                    'exception_type' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }
    /**
     * Load package draft (AJAX endpoint)
     */
    public function loadDraft($draftId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $draft = \App\Models\PackageDraft::where('id', $draftId)
                ->where('user_id', $userId)
                ->active()
                ->first();
            if (!$draft) {
                return response()->json([
                    'success' => false,
                    'message' => 'Draft not found or expired'
                ], 404);
            }
            // Update last accessed time
            $draft->touch();
            return response()->json([
                'success' => true,
                'data' => $draft->draft_data,
                'draft_info' => [
                    'id' => $draft->id,
                    'name' => $draft->name,
                    'current_step' => $draft->current_step,
                    'last_accessed_at' => $draft->last_accessed_at,
                    'progress' => $draft->getProgressPercentage()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Draft load error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'draft_id' => $draftId
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load draft: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Continue editing a draft package (redirects to creation form)
     */
    public function continueDraft($draftId)
    {
        try {
            $userId = Auth::id();
            // Find the draft
            $draft = \App\Models\PackageDraft::where('id', $draftId)
                ->where('user_id', $userId)
                ->active()
                ->first();
            if (!$draft) {
                return redirect()->route('b2b.travel-agent.drafts')
                    ->with('error', 'Draft not found or has expired. It may have been deleted or reached its expiry date.');
            }
            // Update last accessed time and extend expiry
            $draft->update([
                'last_accessed_at' => now(),
                'expires_at' => now()->addDays(7) // Extend expiry by 7 days
            ]);
            // Prepare draft data for the creation form
            $draftData = [
                'id' => $draft->id,
                'name' => $draft->name,
                'current_step' => $draft->current_step,
                'data' => $draft->draft_data,
                'progress' => $draft->getProgressPercentage(),
                'last_accessed_at' => $draft->last_accessed_at->toDateTimeString(),
                'expires_at' => $draft->expires_at->toDateTimeString(),
                'step_status' => $draft->step_status ?? []
            ];
            // Log the draft continuation for debugging
            \Log::info('Draft continued successfully', [
                'user_id' => $userId,
                'draft_id' => $draft->id,
                'draft_name' => $draft->name,
                'current_step' => $draft->current_step,
                'progress' => $draft->getProgressPercentage(),
                'draft_data_keys' => is_array($draft->draft_data) ? array_keys($draft->draft_data) : 'not_array',
                'has_activities' => is_array($draft->draft_data) && isset($draft->draft_data['activities']),
                'activities_count' => is_array($draft->draft_data) && isset($draft->draft_data['activities']) ? count($draft->draft_data['activities']) : 0,
                'has_selected_providers' => is_array($draft->draft_data) && (isset($draft->draft_data['selected_hotels']) || isset($draft->draft_data['selected_flights']) || isset($draft->draft_data['selected_transport']))
            ]);
            // Redirect to package creation form with draft data
            return redirect()->route('b2b.travel-agent.packages.create')
                ->with('draft_data', $draftData)
                ->with('info', 'Continuing draft: ' . $draft->name);
        } catch (\Exception $e) {
            \Log::error('Draft continue error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'draft_id' => $draftId,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('b2b.travel-agent.drafts')
                ->with('error', 'Failed to load draft: ' . $e->getMessage());
        }
    }
    /**
     * Get detailed provider information for Step 5 review
     */
    public function getProviderDetails($draftId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $draft = \App\Models\PackageDraft::where('id', $draftId)
                ->where('user_id', $userId)
                ->first();
            if (!$draft) {
                return response()->json(['error' => 'Draft not found'], 404);
            }
            $draftData = $draft->draft_data ?? [];
            $details = [
                'hotels' => [],
                'flights' => [],
                'transport' => []
            ];
            // Get hotel details
            if (isset($draftData['selected_hotels']) && is_array($draftData['selected_hotels'])) {
                foreach ($draftData['selected_hotels'] as $hotel) {
                    if (isset($hotel['id'])) {
                        $hotelInfo = \App\Models\Hotel::find($hotel['id']);
                        if ($hotelInfo) {
                            $details['hotels'][] = [
                                'id' => $hotelInfo->id,
                                'name' => $hotelInfo->name,
                                'location' => $hotelInfo->location ?? 'Not specified',
                                'rating' => $hotelInfo->rating ?? 'N/A',
                                'price' => $hotelInfo->price_per_night ?? 0,
                                'nights' => $hotel['nights'] ?? 1,
                                'room_type' => $hotel['room_type'] ?? 'Standard',
                                'status' => $hotel['service_request_status'] ?? 'pending'
                            ];
                        }
                    }
                }
            }
            // Get flight details  
            if (isset($draftData['selected_flights']) && is_array($draftData['selected_flights'])) {
                foreach ($draftData['selected_flights'] as $flight) {
                    if (isset($flight['id'])) {
                        $flightInfo = \App\Models\Flight::find($flight['id']);
                        if ($flightInfo) {
                            $details['flights'][] = [
                                'id' => $flightInfo->id,
                                'airline' => $flightInfo->airline ?? 'Own Flight',
                                'flight_number' => $flightInfo->flight_number ?? 'N/A',
                                'departure' => $flightInfo->departure_airport ?? 'N/A',
                                'arrival' => $flightInfo->arrival_airport ?? 'N/A',
                                'departure_time' => $flightInfo->departure_time ?? 'N/A',
                                'price' => $flightInfo->price ?? 0,
                                'seats' => $flight['seats_allocated'] ?? 1,
                                'status' => $flight['service_request_status'] ?? 'pending'
                            ];
                        }
                    }
                }
            }
            // Get transport details
            if (isset($draftData['selected_transport']) && is_array($draftData['selected_transport'])) {
                foreach ($draftData['selected_transport'] as $transport) {
                    if (isset($transport['id'])) {
                        $transportInfo = \App\Models\TransportService::find($transport['id']);
                        if ($transportInfo) {
                            $details['transport'][] = [
                                'id' => $transportInfo->id,
                                'name' => $transportInfo->name ?? 'Transport Service',
                                'type' => $transportInfo->vehicle_type ?? 'Bus',
                                'capacity' => $transportInfo->capacity ?? 'N/A',
                                'pickup' => $transport['pickup_location'] ?? 'N/A',
                                'dropoff' => $transport['dropoff_location'] ?? 'N/A',
                                'price' => $transportInfo->price_per_day ?? 0,
                                'status' => $transport['service_request_status'] ?? 'pending'
                            ];
                        }
                    }
                }
            }
            return response()->json([
                'success' => true,
                'data' => $details
            ]);
        } catch (\Exception $e) {
            \Log::error('Provider details error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load provider details'], 500);
        }
    }
    /**
     * Validate individual step data via AJAX
     */
    public function validateStep(Request $request): JsonResponse
    {
        try {
            $step = (int) $request->get('step', 1);
            // Debug logging to see incoming data
            \Log::info('validateStep called', [
                'step' => $step,
                'request_data_keys' => array_keys($request->all()),
                'request_data' => $step == 4 ? $request->all() : 'not_step_4' // Only log full data for step 4
            ]);
            $rules = [];
            $messages = [];
            switch ($step) {
                case 1:
                    $rules = [
                        'name' => 'required|string|max:255',
                        'short_description' => 'required|string|max:300',
                        'detailed_description' => 'nullable|string|max:10000',
                        'package_type' => 'required|string|max:50',
                        'duration_days' => 'required|integer|min:1|max:365',
                        'duration_nights' => 'nullable|integer|min:0|max:364',
                        'date_range' => 'required|string',
                        'start_date' => 'required|date|after_or_equal:today',
                        'end_date' => 'required|date|after:start_date',
                        'max_participants' => 'required|integer|min:1|max:100',
                        'min_participants' => 'nullable|integer|min:1|lte:max_participants',
                        'currency' => 'required|string|size:3',
                        'destinations' => 'required|array|min:1',
                        'destinations.*' => 'required|string|max:255',
                        'difficulty_level' => 'nullable|in:easy,moderate,challenging,expert',
                        'status' => 'nullable|in:draft,active',
                        'categories' => 'nullable|array',
                        'categories.*' => 'string|max:50',
                        // Provider sources
                        'hotel_source' => 'required|in:platform,external,mixed',
                        'transport_source' => 'required|in:platform,external,mixed',
                        'flight_source' => 'required|in:own,platform,external,mixed',
                        // 'base_price' => 'required|numeric|min:0', // Moved to Step 4
                        // Special features (optional checkboxes)
                        'includes_meals' => 'nullable|boolean',
                        'includes_accommodation' => 'nullable|boolean',
                        'includes_transport' => 'nullable|boolean',
                        'includes_guide' => 'nullable|boolean',
                        'includes_flights' => 'nullable|boolean',
                        'includes_activities' => 'nullable|boolean',
                        'free_cancellation' => 'nullable|boolean',
                        'instant_confirmation' => 'nullable|boolean',
                    ];
                    $messages = [
                        'name.required' => 'Package name is required',
                        'short_description.required' => 'Package description is required',
                        'short_description.max' => 'Description must not exceed 300 characters',
                        'package_type.required' => 'Package type is required',
                        'duration_days.required' => 'Duration in days is required',
                        'duration_days.min' => 'Duration must be at least 1 day',
                        'duration_days.max' => 'Duration cannot exceed 365 days',
                        'date_range.required' => 'Please select trip dates',
                        'start_date.required' => 'Start date is required',
                        'start_date.after_or_equal' => 'Start date must be today or later',
                        'end_date.required' => 'End date is required',
                        'end_date.after' => 'End date must be after start date',
                        'max_participants.required' => 'Maximum participants is required',
                        'max_participants.min' => 'Maximum participants must be at least 1',
                        'max_participants.max' => 'Maximum participants cannot exceed 100',
                        'min_participants.lte' => 'Minimum participants must be less than or equal to maximum participants',
                        'currency.required' => 'Currency selection is required',
                        'currency.size' => 'Currency must be a 3-letter code',
                        'destinations.required' => 'At least one destination is required',
                        'destinations.min' => 'Please add at least one destination to the package',
                        'destinations.array' => 'Destinations must be provided as a list',
                        'difficulty_level.in' => 'Please select a valid difficulty level',
                        'hotel_source.required' => 'Hotel source selection is required',
                        'hotel_source.in' => 'Please select a valid hotel source',
                        'transport_source.required' => 'Transport source selection is required',
                        'transport_source.in' => 'Please select a valid transport source',
                        'flight_source.required' => 'Flight source selection is required',
                        'flight_source.in' => 'Please select a valid flight source',
                        // Pricing validation messages moved to Step 4
                        // 'base_price.required' => 'Base price is required',
                        // 'base_price.numeric' => 'Base price must be a valid number',
                        // 'base_price.min' => 'Base price must be greater than or equal to 0',
                    ];
                    break;
                case 2:
                    $rules = [
                        'activities' => 'nullable|array',
                        'activities.*.day_number' => 'required|integer|min:1',
                        'activities.*.activity_name' => 'required|string|max:255',
                        'activities.*.description' => 'required|string|max:1000',
                        'activities.*.start_time' => 'nullable|date_format:H:i',
                        'activities.*.end_time' => 'nullable|date_format:H:i|after:activities.*.start_time',
                        'activities.*.location' => 'nullable|string|max:255',
                        'activities.*.category' => 'required|in:' . implode(',', PackageActivity::CATEGORIES),
                        'activities.*.additional_cost' => 'nullable|numeric|min:0',
                    ];
                    break;
                case 3:
                    // Check if we're dealing with existing providers or new selections
                    $hasExistingProviders = $request->has('selected_hotels') || 
                                           $request->has('selected_transport') || 
                                           $request->has('selected_flights');
                    // Debug logging for step 3 validation
                    \Log::info('Step 3 validation debug', [
                        'has_selected_hotels' => $request->has('selected_hotels'),
                        'has_selected_transport' => $request->has('selected_transport'),
                        'has_selected_flights' => $request->has('selected_flights'),
                        'hasExistingProviders' => $hasExistingProviders,
                        'selected_hotels_data' => $request->get('selected_hotels'),
                        'selected_transport_data' => $request->get('selected_transport'),
                        'selected_flights_data' => $request->get('selected_flights'),
                    ]);
                    if ($hasExistingProviders) {
                        // More flexible validation for existing drafts with service request format
                        $rules = [
                            'selected_hotels' => 'nullable|array',
                            'selected_hotels.*.id' => 'nullable|string',
                            'selected_hotels.*.hotel_id' => 'nullable|exists:hotels,id',
                            'selected_hotels.*.type' => 'nullable|string',
                            'selected_hotels.*.provider_type' => 'nullable|string',
                            'selected_hotels.*.service_request_id' => 'nullable|string',
                            'selected_hotels.*.service_request_status' => 'nullable|string',
                            'selected_hotels.*.is_primary' => 'nullable|in:true,false,1,0',
                            'selected_hotels.*.nights' => 'nullable|integer|min:1',
                            'selected_hotels.*.room_type' => 'nullable|string|max:100',
                            'selected_hotels.*.rooms_needed' => 'nullable|integer|min:1',
                            'selected_hotels.*.markup_percentage' => 'nullable|numeric|min:0|max:100',
                            'selected_hotels.*.approved_at' => 'nullable',
                            'selected_hotels.*.offered_price' => 'nullable',
                            'selected_hotels.*.currency' => 'nullable|string',
                            'selected_hotels.*.provider_notes' => 'nullable|string',
                            'selected_hotels.*.terms_conditions' => 'nullable',
                            'selected_hotels.*.request_info' => 'nullable|string',
                            'selected_hotels.*.selected_at' => 'nullable|string',
                            'selected_hotels.*.from_draft' => 'nullable|string',
                            'selected_transport' => 'nullable|array',
                            'selected_transport.*.id' => 'nullable|string',
                            'selected_transport.*.transport_id' => 'nullable|exists:transport_services,id',
                            'selected_transport.*.type' => 'nullable|string',
                            'selected_transport.*.provider_type' => 'nullable|string',
                            'selected_transport.*.service_request_id' => 'nullable|string',
                            'selected_transport.*.service_request_status' => 'nullable|string',
                            'selected_transport.*.category' => 'nullable|string|max:50',
                            'selected_transport.*.pickup_location' => 'nullable|string|max:255',
                            'selected_transport.*.dropoff_location' => 'nullable|string|max:255',
                            'selected_transport.*.day_of_itinerary' => 'nullable|integer|min:1',
                            'selected_transport.*.markup_percentage' => 'nullable|numeric|min:0|max:100',
                            'selected_transport.*.approved_at' => 'nullable',
                            'selected_transport.*.offered_price' => 'nullable',
                            'selected_transport.*.currency' => 'nullable|string',
                            'selected_transport.*.provider_notes' => 'nullable|string',
                            'selected_transport.*.terms_conditions' => 'nullable',
                            'selected_transport.*.request_info' => 'nullable|string',
                            'selected_transport.*.selected_at' => 'nullable|string',
                            'selected_transport.*.from_draft' => 'nullable|string',
                            'selected_flights' => 'nullable|array',
                            'selected_flights.*.id' => 'nullable|string',
                            'selected_flights.*.flight_id' => 'nullable|exists:flights,id',
                            'selected_flights.*.type' => 'nullable|string',
                            'selected_flights.*.provider_type' => 'nullable|string',
                            'selected_flights.*.service_request_id' => 'nullable|string',
                            'selected_flights.*.service_request_status' => 'nullable|string',
                            'selected_flights.*.flight_type' => 'nullable|in:outbound,return,connecting',
                            'selected_flights.*.seats_allocated' => 'nullable|integer|min:1',
                            'selected_flights.*.markup_percentage' => 'nullable|numeric|min:0|max:100',
                            'selected_flights.*.approved_at' => 'nullable',
                            'selected_flights.*.offered_price' => 'nullable',
                            'selected_flights.*.currency' => 'nullable|string',
                            'selected_flights.*.provider_notes' => 'nullable|string',
                            'selected_flights.*.terms_conditions' => 'nullable',
                            'selected_flights.*.request_info' => 'nullable|string',
                            'selected_flights.*.selected_at' => 'nullable|string',
                            'selected_flights.*.from_draft' => 'nullable|string',
                        ];
                    } else {
                        // Original strict validation for new selections
                        $rules = [
                            'selected_hotels' => 'nullable|array',
                            'selected_hotels.*.hotel_id' => 'required|exists:hotels,id',
                            'selected_hotels.*.nights' => 'required|integer|min:1',
                            'selected_hotels.*.room_type' => 'required|string|max:100',
                            'selected_hotels.*.rooms_needed' => 'required|integer|min:1',
                            'selected_transport' => 'nullable|array',
                            'selected_transport.*.transport_id' => 'required|exists:transport_services,id',
                            'selected_transport.*.category' => 'required|string|max:50',
                            'selected_transport.*.pickup_location' => 'required|string|max:255',
                            'selected_transport.*.dropoff_location' => 'required|string|max:255',
                            'selected_transport.*.day_of_itinerary' => 'required|integer|min:1',
                            'selected_flights' => 'nullable|array',
                            'selected_flights.*.flight_id' => 'required|exists:flights,id',
                            'selected_flights.*.flight_type' => 'required|in:outbound,return,connecting',
                            'selected_flights.*.seats_allocated' => 'required|integer|min:1',
                        ];
                    }
                    break;
                case 4:
                    $rules = [
                        // Main pricing fields
                        'base_price' => 'required|numeric|min:0',
                        'child_price' => 'nullable|numeric|min:0',
                        'child_discount_percent' => 'nullable|numeric|min:0|max:100',
                        'infant_price' => 'nullable|numeric|min:0',
                        'single_supplement' => 'nullable|numeric|min:0',
                        'commission_rate' => 'required|numeric|min:0|max:100',
                        'total_price' => 'nullable|numeric|min:0', // Auto-calculated field
                        // Payment and booking terms
                        'payment_terms' => 'nullable|string|in:full_upfront,50_advance,30_advance',
                        'cancellation_policy' => 'nullable|string|in:flexible,moderate,strict',
                        'min_booking_days' => 'nullable|integer|min:0|max:365',
                        'requires_deposit' => 'nullable|boolean',
                        'deposit_amount' => 'nullable|numeric|min:0',
                        // Optional legacy fields for backwards compatibility
                        'deposit_percentage' => 'nullable|numeric|min:0|max:100',
                        'platform_commission' => 'nullable|numeric|min:0|max:50',
                        'min_participants' => 'nullable|integer|min:1',
                        'max_participants' => 'nullable|integer|min:1|gte:min_participants',
                        'pricing_breakdown' => 'nullable|array',
                        'pricing_breakdown.*.item' => 'required|string|max:255',
                        'pricing_breakdown.*.amount' => 'required|numeric|min:0',
                        'optional_addons' => 'nullable|array',
                        'optional_addons.*.name' => 'required|string|max:255',
                        'optional_addons.*.price' => 'required|numeric|min:0',
                    ];
                    $messages = [
                        'base_price.required' => 'Base price is required',
                        'base_price.numeric' => 'Base price must be a valid number',
                        'base_price.min' => 'Base price must be greater than or equal to 0',
                        'commission_rate.required' => 'Commission rate is required',
                        'commission_rate.numeric' => 'Commission rate must be a valid number',
                        'commission_rate.min' => 'Commission rate must be greater than or equal to 0',
                        'commission_rate.max' => 'Commission rate cannot exceed 100%',
                        'child_price.numeric' => 'Child price must be a valid number',
                        'child_price.min' => 'Child price must be greater than or equal to 0',
                        'child_discount_percent.numeric' => 'Child discount must be a valid number',
                        'child_discount_percent.min' => 'Child discount must be greater than or equal to 0',
                        'child_discount_percent.max' => 'Child discount cannot exceed 100%',
                        'infant_price.numeric' => 'Infant price must be a valid number',
                        'infant_price.min' => 'Infant price must be greater than or equal to 0',
                        'single_supplement.numeric' => 'Single supplement must be a valid number',
                        'single_supplement.min' => 'Single supplement must be greater than or equal to 0',
                        'min_booking_days.integer' => 'Minimum booking days must be a valid number',
                        'min_booking_days.min' => 'Minimum booking days must be 0 or greater',
                        'min_booking_days.max' => 'Minimum booking days cannot exceed 365',
                        'deposit_amount.numeric' => 'Deposit amount must be a valid number',
                        'deposit_amount.min' => 'Deposit amount must be greater than or equal to 0',
                        'payment_terms.in' => 'Please select a valid payment term option',
                        'cancellation_policy.in' => 'Please select a valid cancellation policy',
                    ];
                    break;
                case 5:
                    $rules = [
                        'final_confirmation' => 'required|accepted',
                        'terms_accepted' => 'required|accepted',
                    ];
                    $messages = [
                        'final_confirmation.required' => 'Please confirm the package details',
                        'final_confirmation.accepted' => 'Package confirmation is required',
                        'terms_accepted.required' => 'Please accept the terms and conditions',
                        'terms_accepted.accepted' => 'Terms acceptance is required to proceed',
                    ];
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid step number'
                    ], 400);
            }
            // Perform validation
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                // Log validation errors for debugging
                \Log::error('Step validation failed', [
                    'step' => $step,
                    'errors' => $validator->errors()->toArray(),
                    'first_error' => $validator->errors()->first(),
                    'validation_rules_count' => count($rules),
                    'request_data_sample' => $step == 3 ? [
                        'selected_hotels' => $request->get('selected_hotels'),
                        'selected_transport' => $request->get('selected_transport'),
                        'selected_flights' => $request->get('selected_flights')
                    ] : 'not_step_3'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->toArray(),
                    'first_error' => $validator->errors()->first()
                ], 422);
            }
            // Store validated data in session if validation passes
            session(["package_draft.step{$step}" => $validator->validated()]);
            return response()->json([
                'success' => true,
                'message' => "Step {$step} validated successfully",
                'validated_data' => $validator->validated()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Debug form data (temporary method for debugging)
     */
    public function debugFormData(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'all_data' => $request->all(),
            'destinations' => $request->get('destinations', 'NOT FOUND'),
            'files' => $request->files->all(),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'raw_input' => $request->getContent()
        ]);
    }
    // Private helper methods
    private function ensurePackageOwnership(Package $package): void
    {
        if ($package->creator_id !== Auth::id()) {
            abort(403, 'Unauthorized access to package.');
        }
    }
    private function getAvailableHotels($source): \Illuminate\Database\Eloquent\Collection
    {
        if ($source === Package::SOURCE_PLATFORM) {
            return Hotel::active()->with('provider')->get();
        }
        return collect(); // Return empty collection for external/mixed sources
    }
    private function getAvailableTransport($source): \Illuminate\Database\Eloquent\Collection
    {
        if ($source === Package::SOURCE_PLATFORM) {
            return TransportService::active()->with('provider')->get();
        }
        return collect();
    }
    private function getAvailableFlights($source): \Illuminate\Database\Eloquent\Collection
    {
        if ($source === Package::SOURCE_OWN) {
            return Flight::where('provider_id', Auth::id())->active()->get();
        } elseif ($source === Package::SOURCE_PLATFORM) {
            return Flight::where('provider_id', '!=', Auth::id())->active()->get();
        }
        return collect();
    }
    private function calculateEstimatedPrice($step1Data, $step2Data, $step3Data): float
    {
        $basePrice = $step1Data['base_price'];
        // Add estimated provider costs (simplified calculation)
        $providerCosts = 0;
        if (isset($step2Data['selected_hotels'])) {
            $providerCosts += count($step2Data['selected_hotels']) * 100; // Estimated hotel cost
        }
        if (isset($step2Data['selected_transport'])) {
            $providerCosts += count($step2Data['selected_transport']) * 50; // Estimated transport cost
        }
        if (isset($step2Data['selected_flights'])) {
            $providerCosts += count($step2Data['selected_flights']) * 200; // Estimated flight cost
        }
        // Add activity costs
        $activityCosts = 0;
        if (isset($step3Data['activities'])) {
            foreach ($step3Data['activities'] as $activity) {
                if (!($activity['is_included'] ?? true) && isset($activity['additional_cost'])) {
                    $activityCosts += $activity['additional_cost'];
                }
            }
        }
        return $basePrice + $providerCosts + $activityCosts;
    }
    private function handleImageUpdates(Request $request, Package $package): void
    {
        $currentImages = $package->images ?? [];
        // Remove selected images
        if ($request->filled('remove_images')) {
            $currentImages = array_diff($currentImages, $request->remove_images);
            // Delete files from storage
            foreach ($request->remove_images as $imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
        }
        // Add new images
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $image) {
                $path = $image->store('packages/' . $package->id, 'public');
                $currentImages[] = $path;
            }
        }
        $package->images = array_values($currentImages);
        $package->save();
    }
}

