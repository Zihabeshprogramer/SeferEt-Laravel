<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAdRequest;
use App\Http\Requests\UpdateAdRequest;
use App\Models\Ad;
use App\Models\Flight;
use App\Models\Hotel;
use App\Models\Package;
use App\Models\Offer;
use App\Services\AdImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class AdController extends Controller
{
    public function __construct(
        protected AdImageService $imageService
    ) {
    }
    /**
     * Display ads list page
     */
    public function index()
    {
        return view('b2b.common.ads.index');
    }

    /**
     * Get datatable data for ads listing
     */
    public function getData(Request $request)
    {
        $query = Ad::query()
            ->with(['product', 'approver'])
            ->byOwner(auth()->id(), get_class(auth()->user()))
            ->latest();

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('thumbnail', function ($ad) {
                if ($ad->hasImage()) {
                    return '<img src="' . $ad->image_url . '" alt="' . e($ad->title) . '" class="img-thumbnail" style="max-width: 80px; max-height: 60px; object-fit: cover;">';
                }
                return '<div class="bg-secondary text-white text-center p-3" style="width: 80px; height: 60px;"><i class="fas fa-image"></i></div>';
            })
            ->addColumn('product_info', function ($ad) {
                if ($ad->product) {
                    // Get product name based on type
                    $productName = 'N/A';
                    if ($ad->product_type === 'flight') {
                        $productName = $ad->product->airline . ' ' . $ad->product->flight_number;
                    } else {
                        $productName = $ad->product->name ?? $ad->product->title ?? 'N/A';
                    }
                    
                    $productType = ucfirst($ad->product_type);
                    return '<div class="text-sm"><strong>' . e($productName) . '</strong><br><span class="badge badge-secondary badge-sm">' . $productType . '</span></div>';
                }
                return '<span class="text-muted">No product</span>';
            })
            ->addColumn('status_badge', function ($ad) {
                $badges = [
                    'draft' => 'secondary',
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                ];
                $color = $badges[$ad->status] ?? 'secondary';
                $statusText = ucfirst($ad->status);
                
                $html = '<span class="badge badge-' . $color . '">' . $statusText . '</span>';
                
                if ($ad->isApproved()) {
                    $activeIcon = $ad->is_active 
                        ? '<i class="fas fa-toggle-on text-success ml-1" title="Active"></i>' 
                        : '<i class="fas fa-toggle-off text-muted ml-1" title="Inactive"></i>';
                    $html .= ' ' . $activeIcon;
                    
                    if ($ad->isExpired()) {
                        $html .= ' <span class="badge badge-danger badge-sm ml-1">Expired</span>';
                    }
                }
                
                return $html;
            })
            ->addColumn('schedule', function ($ad) {
                if (!$ad->start_at && !$ad->end_at) {
                    return '<span class="text-muted">Always</span>';
                }
                
                $start = $ad->start_at ? $ad->start_at->format('M d, Y') : 'Now';
                $end = $ad->end_at ? $ad->end_at->format('M d, Y') : '∞';
                
                return '<small>' . $start . ' <i class="fas fa-arrow-right"></i> ' . $end . '</small>';
            })
            ->addColumn('stats', function ($ad) {
                $ctr = $ad->impressions_count > 0 ? number_format($ad->ctr, 2) : '0.00';
                return '<div class="text-sm">
                    <i class="fas fa-eye text-info"></i> ' . number_format($ad->impressions_count) . ' 
                    <i class="fas fa-mouse-pointer text-success ml-2"></i> ' . number_format($ad->clicks_count) . '
                    <br><span class="text-muted">CTR: ' . $ctr . '%</span>
                </div>';
            })
            ->addColumn('actions', function ($ad) {
                $actions = '<div class="btn-group btn-group-sm">';
                
                // View/Preview
                $actions .= '<a href="' . route('b2b.ads.show', $ad) . '" class="btn btn-info btn-sm" title="View"><i class="fas fa-eye"></i></a>';
                
                // Edit (only for draft and rejected)
                if ($ad->isDraft() || $ad->isRejected()) {
                    $actions .= '<a href="' . route('b2b.ads.edit', $ad) . '" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>';
                }
                
                // Delete (only for draft and rejected)
                if ($ad->isDraft() || $ad->isRejected()) {
                    $actions .= '<button type="button" class="btn btn-danger btn-sm delete-ad" data-id="' . $ad->id . '" title="Delete"><i class="fas fa-trash"></i></button>';
                }
                
                // Toggle active (only for approved)
                if ($ad->isApproved()) {
                    $toggleClass = $ad->is_active ? 'btn-secondary' : 'btn-success';
                    $toggleIcon = $ad->is_active ? 'fa-toggle-off' : 'fa-toggle-on';
                    $toggleTitle = $ad->is_active ? 'Deactivate' : 'Activate';
                    $actions .= '<button type="button" class="btn ' . $toggleClass . ' btn-sm toggle-active" data-id="' . $ad->id . '" title="' . $toggleTitle . '"><i class="fas ' . $toggleIcon . '"></i></button>';
                }
                
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['thumbnail', 'product_info', 'status_badge', 'schedule', 'stats', 'actions'])
            ->make(true);
    }

    /**
     * Show create ad form
     */
    public function create()
    {
        // Get user's products based on their role
        $products = $this->getUserProducts();
        
        return view('b2b.common.ads.create', compact('products'));
    }

    /**
     * Show specific ad
     */
    public function show(Ad $ad)
    {
        $this->authorize('view', $ad);
        
        $ad->load(['product', 'approver', 'auditLogs.user']);
        
        return view('b2b.common.ads.show', compact('ad'));
    }

    /**
     * Show edit ad form
     */
    public function edit(Ad $ad)
    {
        $this->authorize('update', $ad);
        
        // Only allow editing draft or rejected ads
        if (!$ad->isDraft() && !$ad->isRejected()) {
            return redirect()->route('b2b.ads.show', $ad)
                ->with('error', 'You can only edit draft or rejected ads.');
        }
        
        $ad->load(['product']);
        $products = $this->getUserProducts();
        
        return view('b2b.common.ads.edit', compact('ad', 'products'));
    }

    /**
     * Store a newly created ad
     */
    public function store(CreateAdRequest $request)
    {
        try {
            $ad = DB::transaction(function () use ($request) {
                // Handle image upload first
                $imageData = null;
                if ($request->hasFile('image')) {
                    $imageData = $this->imageService->uploadImage(
                        $request->file('image'),
                        auth()->id()
                    );
                }
                
                // Create ad with validated data
                $adData = $request->validatedWithOwner();
                
                // Add image paths if uploaded
                if ($imageData) {
                    $adData['image_path'] = $imageData['original_path'];
                    $adData['image_variants'] = $imageData['variants'];
                }
                
                $ad = Ad::create($adData);
                
                Log::info("Ad created via B2B", [
                    'ad_id' => $ad->id,
                    'user_id' => auth()->id(),
                    'title' => $ad->title,
                    'has_image' => !empty($adData['image_path']),
                ]);

                return $ad;
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ad created successfully.',
                    'data' => $ad->load(['owner', 'product']),
                ]);
            }

            return redirect()->route('b2b.ads.index')
                ->with('success', 'Ad created successfully.');

        } catch (\Exception $e) {
            Log::error("Failed to create ad via B2B", [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create ad. Please try again.',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to create ad. Please try again.');
        }
    }

    /**
     * Update the specified ad
     */
    public function update(UpdateAdRequest $request, Ad $ad)
    {
        try {
            $ad = DB::transaction(function () use ($request, $ad) {
                $ad->update($request->validated());
                
                Log::info("Ad updated via B2B", [
                    'ad_id' => $ad->id,
                    'user_id' => auth()->id(),
                ]);

                return $ad;
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ad updated successfully.',
                    'data' => $ad->fresh(['owner', 'product', 'approver']),
                ]);
            }

            return redirect()->route('b2b.ads.show', $ad)
                ->with('success', 'Ad updated successfully.');

        } catch (\Exception $e) {
            Log::error("Failed to update ad via B2B", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update ad. Please try again.',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to update ad. Please try again.');
        }
    }

    /**
     * Remove the specified ad
     */
    public function destroy(Request $request, Ad $ad)
    {
        $this->authorize('delete', $ad);

        try {
            DB::transaction(function () use ($ad) {
                // Delete associated images
                if ($ad->hasImage()) {
                    $this->imageService->deleteImage($ad->image_path, $ad->image_variants);
                }

                $ad->delete();
                
                Log::info("Ad deleted via B2B", [
                    'ad_id' => $ad->id,
                    'user_id' => auth()->id(),
                ]);
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ad deleted successfully.',
                ]);
            }

            return redirect()->route('b2b.ads.index')
                ->with('success', 'Ad deleted successfully.');

        } catch (\Exception $e) {
            Log::error("Failed to delete ad via B2B", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete ad. Please try again.',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }

            return back()->with('error', 'Failed to delete ad. Please try again.');
        }
    }

    /**
     * Submit ad for approval
     */
    public function submitForApproval(Request $request, Ad $ad)
    {
        $this->authorize('update', $ad);

        // Only draft or rejected ads can be submitted
        if (!$ad->isDraft() && !$ad->isRejected()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft or rejected ads can be submitted for approval.',
                ], 422);
            }

            return back()->with('error', 'Only draft or rejected ads can be submitted for approval.');
        }

        try {
            DB::transaction(function () use ($ad) {
                $success = $ad->submitForApproval();
                
                if (!$success) {
                    throw new \Exception('Failed to change ad status to pending.');
                }
                
                Log::info("Ad submitted for approval via B2B", [
                    'ad_id' => $ad->id,
                    'user_id' => auth()->id(),
                    'title' => $ad->title,
                ]);
                
                // Notify admins about new ad submission
                $admins = \App\Models\User::role('admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\AdSubmittedNotification($ad));
                }
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ad submitted for approval successfully. You will be notified once it is reviewed.',
                    'data' => $ad->fresh(['owner', 'product', 'approver']),
                ]);
            }

            return redirect()->route('b2b.ads.show', $ad)
                ->with('success', 'Ad submitted for approval successfully. You will be notified once it is reviewed.');

        } catch (\Exception $e) {
            Log::error("Failed to submit ad for approval via B2B", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to submit ad for approval. Please try again.',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }

            return back()->with('error', 'Failed to submit ad for approval. Please try again.');
        }
    }

    /**
     * Toggle ad active status
     */
    public function toggleActive(Request $request, Ad $ad)
    {
        $this->authorize('update', $ad);

        // Only approved ads can have their active status toggled
        if (!$ad->isApproved()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only approved ads can be activated or deactivated.',
                ], 422);
            }

            return back()->with('error', 'Only approved ads can be activated or deactivated.');
        }

        try {
            $ad->is_active = !$ad->is_active;
            $ad->save();

            Log::info("Ad active status toggled via B2B", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
                'is_active' => $ad->is_active,
            ]);

            $message = $ad->is_active ? 'Ad activated successfully.' : 'Ad deactivated successfully.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $ad->fresh(['owner', 'product']),
                ]);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error("Failed to toggle ad active status via B2B", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to toggle ad status. Please try again.',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }

            return back()->with('error', 'Failed to toggle ad status. Please try again.');
        }
    }

    /**
     * Get statistics for ads dashboard
     */
    public function getStats()
    {
        $user = auth()->user();
        
        $stats = [
            'total' => Ad::byOwner($user->id, get_class($user))->count(),
            'draft' => Ad::byOwner($user->id, get_class($user))->draft()->count(),
            'pending' => Ad::byOwner($user->id, get_class($user))->pending()->count(),
            'approved' => Ad::byOwner($user->id, get_class($user))->approved()->count(),
            'rejected' => Ad::byOwner($user->id, get_class($user))->rejected()->count(),
            'active' => Ad::byOwner($user->id, get_class($user))->active()->count(),
            'total_impressions' => Ad::byOwner($user->id, get_class($user))->sum('impressions_count'),
            'total_clicks' => Ad::byOwner($user->id, get_class($user))->sum('clicks_count'),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get user's products based on their role
     */
    protected function getUserProducts()
    {
        $user = auth()->user();
        $products = [];
        
        try {
            // Travel Agent - can advertise their packages and flights
            if ($user->hasRole('travel_agent')) {
                $packages = Package::where('creator_id', $user->id)
                    ->where('status', 'active')
                    ->select('id', 'name', DB::raw("'package' as type"))
                    ->get();
                    
                $flights = Flight::where('provider_id', $user->id)
                    ->where('is_active', true)
                    ->select('id', DB::raw("CONCAT(airline, ' ', flight_number, ' - ', departure_airport, ' to ', arrival_airport) as name"), DB::raw("'flight' as type"))
                    ->get();
                    
                $products = $packages->concat($flights);
            }
            
            // Hotel Provider - can advertise their hotels
            if ($user->hasRole('hotel_provider')) {
                $hotels = Hotel::where('provider_id', $user->id)
                    ->where('is_active', true)
                    ->select('id', 'name', DB::raw("'hotel' as type"))
                    ->get();
                    
                $products = $hotels;
            }
            
            // Transport Provider - can advertise special offers
            if ($user->hasRole('transport_provider')) {
                $offers = Offer::where('provider_id', $user->id)
                    ->where('is_active', true)
                    ->select('id', 'title as name', DB::raw("'offer' as type"))
                    ->get();
                    
                $products = $offers;
            }
        } catch (\Exception $e) {
            Log::error('Error fetching user products for ads', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        return $products;
    }
}
