<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeaturedRequest;
use App\Models\Flight;
use App\Models\Hotel;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class FeatureRequestController extends Controller
{
    /**
     * Display a listing of the user's feature requests.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', FeaturedRequest::class);

        $query = FeaturedRequest::with(['requester', 'approver'])
            ->where('requested_by', $request->user()->id)
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by product type
        if ($request->has('product_type')) {
            $query->where('product_type', $request->product_type);
        }

        $requests = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    /**
     * Store a newly created feature request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'product_type' => ['required', Rule::in(FeaturedRequest::PRODUCT_TYPES)],
            'notes' => 'nullable|string|max:1000',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        // Authorization check for product ownership
        Gate::authorize('create', [
            FeaturedRequest::class,
            $validated['product_type'],
            $validated['product_id']
        ]);

        // Check if product exists and is active
        $product = $this->getProduct($validated['product_type'], $validated['product_id']);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        // Check if there's already a pending or approved request for this product
        $existingRequest = FeaturedRequest::where('product_id', $validated['product_id'])
            ->where('product_type', $validated['product_type'])
            ->whereIn('status', [FeaturedRequest::STATUS_PENDING, FeaturedRequest::STATUS_APPROVED])
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'A feature request already exists for this product.',
                'existing_request' => $existingRequest
            ], 422);
        }

        // Create the feature request
        $featuredRequest = FeaturedRequest::create([
            'product_id' => $validated['product_id'],
            'product_type' => $validated['product_type'],
            'requested_by' => $request->user()->id,
            'notes' => $validated['notes'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => FeaturedRequest::STATUS_PENDING,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feature request submitted successfully.',
            'data' => $featuredRequest->load(['requester'])
        ], 201);
    }

    /**
     * Display the specified feature request.
     */
    public function show(FeaturedRequest $featuredRequest)
    {
        Gate::authorize('view', $featuredRequest);

        $featuredRequest->load(['requester', 'approver']);

        return response()->json([
            'success' => true,
            'data' => $featuredRequest
        ]);
    }

    /**
     * Get feature status for a specific product.
     */
    public function status(Request $request, string $productType, int $productId)
    {
        $validated = $request->validate([
            'product_type' => ['required', Rule::in(FeaturedRequest::PRODUCT_TYPES)],
        ]);

        $product = $this->getProduct($productType, $productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        // Check if user owns this product
        $isOwner = $this->checkOwnership($request->user(), $productType, $product);

        if (!$isOwner && !$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $featuredRequest = FeaturedRequest::where('product_id', $productId)
            ->where('product_type', $productType)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'is_featured' => $product->is_featured ?? false,
                'featured_at' => $product->featured_at ?? null,
                'featured_expires_at' => $product->featured_expires_at ?? null,
                'has_request' => $featuredRequest !== null,
                'request' => $featuredRequest,
            ]
        ]);
    }

    /**
     * Cancel a pending feature request.
     */
    public function destroy(FeaturedRequest $featuredRequest)
    {
        Gate::authorize('delete', $featuredRequest);

        if (!$featuredRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be cancelled.'
            ], 422);
        }

        $featuredRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feature request cancelled successfully.'
        ]);
    }

    /**
     * Get product model instance.
     */
    private function getProduct(string $productType, int $productId)
    {
        return match ($productType) {
            FeaturedRequest::PRODUCT_TYPE_FLIGHT => Flight::find($productId),
            FeaturedRequest::PRODUCT_TYPE_HOTEL => Hotel::find($productId),
            FeaturedRequest::PRODUCT_TYPE_PACKAGE => Package::find($productId),
            default => null
        };
    }

    /**
     * Check if user owns the product.
     */
    private function checkOwnership($user, string $productType, $product): bool
    {
        return match ($productType) {
            FeaturedRequest::PRODUCT_TYPE_FLIGHT => $product->provider_id === $user->id,
            FeaturedRequest::PRODUCT_TYPE_HOTEL => $product->provider_id === $user->id,
            FeaturedRequest::PRODUCT_TYPE_PACKAGE => $product->creator_id === $user->id,
            default => false
        };
    }
}
