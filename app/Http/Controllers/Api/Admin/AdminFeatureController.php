<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedRequest;
use App\Models\Flight;
use App\Models\Hotel;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class AdminFeatureController extends Controller
{
    /**
     * Display a listing of all feature requests.
     */
    public function requests(Request $request)
    {
        Gate::authorize('manageFeatured', FeaturedRequest::class);

        $query = FeaturedRequest::with(['requester', 'approver'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by product type
        if ($request->has('product_type')) {
            $query->where('product_type', $request->product_type);
        }

        // Search by requester
        if ($request->has('search')) {
            $query->whereHas('requester', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $requests = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }

    /**
     * Approve a feature request.
     */
    public function approve(Request $request, FeaturedRequest $featuredRequest)
    {
        Gate::authorize('approve', $featuredRequest);

        $validated = $request->validate([
            'priority_level' => 'nullable|integer|min:1|max:100',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Update the featured request
            $featuredRequest->update([
                'status' => FeaturedRequest::STATUS_APPROVED,
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'priority_level' => $validated['priority_level'] ?? $featuredRequest->priority_level,
                'start_date' => $validated['start_date'] ?? $featuredRequest->start_date,
                'end_date' => $validated['end_date'] ?? $featuredRequest->end_date,
                'notes' => $validated['notes'] ?? $featuredRequest->notes,
            ]);

            // Feature the product
            $this->featureProduct(
                $featuredRequest->product_type,
                $featuredRequest->product_id,
                $validated['start_date'] ?? $featuredRequest->start_date,
                $validated['end_date'] ?? $featuredRequest->end_date
            );

            DB::commit();

            // Clear featured packages cache
            $this->clearFeaturedCache();

            return response()->json([
                'success' => true,
                'message' => 'Feature request approved and product featured successfully.',
                'data' => $featuredRequest->fresh(['requester', 'approver'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve feature request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a feature request.
     */
    public function reject(Request $request, FeaturedRequest $featuredRequest)
    {
        Gate::authorize('reject', $featuredRequest);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $featuredRequest->update([
            'status' => FeaturedRequest::STATUS_REJECTED,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Clear cache in case this affects featured products list
        $this->clearFeaturedCache();

        return response()->json([
            'success' => true,
            'message' => 'Feature request rejected.',
            'data' => $featuredRequest->fresh(['requester', 'approver'])
        ]);
    }

    /**
     * Manually feature a product without a request.
     */
    public function manualFeature(Request $request)
    {
        Gate::authorize('manualFeature', FeaturedRequest::class);

        $validated = $request->validate([
            'product_id' => 'required|integer',
            'product_type' => ['required', Rule::in(FeaturedRequest::PRODUCT_TYPES)],
            'priority_level' => 'nullable|integer|min:1|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $product = $this->getProduct($validated['product_type'], $validated['product_id']);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Create an auto-approved featured request
            $featuredRequest = FeaturedRequest::create([
                'product_id' => $validated['product_id'],
                'product_type' => $validated['product_type'],
                'requested_by' => $request->user()->id, // Admin is the requester
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'status' => FeaturedRequest::STATUS_APPROVED,
                'priority_level' => $validated['priority_level'] ?? 1,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'notes' => $validated['notes'] ?? 'Manually featured by admin',
            ]);

            // Feature the product
            $this->featureProduct(
                $validated['product_type'],
                $validated['product_id'],
                $validated['start_date'] ?? null,
                $validated['end_date'] ?? null
            );

            DB::commit();

            // Clear featured packages cache
            $this->clearFeaturedCache();

            return response()->json([
                'success' => true,
                'message' => 'Product featured successfully.',
                'data' => $featuredRequest->load(['requester', 'approver'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to feature product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unfeature a product.
     */
    public function unfeature(Request $request)
    {
        Gate::authorize('unfeature', FeaturedRequest::class);

        $validated = $request->validate([
            'product_id' => 'required|integer',
            'product_type' => ['required', Rule::in(FeaturedRequest::PRODUCT_TYPES)],
        ]);

        $product = $this->getProduct($validated['product_type'], $validated['product_id']);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        // Unfeature the product
        $this->unfeatureProduct($validated['product_type'], $validated['product_id']);

        // Clear featured packages cache
        $this->clearFeaturedCache();

        return response()->json([
            'success' => true,
            'message' => 'Product unfeatured successfully.'
        ]);
    }

    /**
     * Get all currently featured products.
     */
    public function featured(Request $request)
    {
        Gate::authorize('manageFeatured', FeaturedRequest::class);

        $query = FeaturedRequest::with(['requester', 'approver'])
            ->active()
            ->byPriority('desc');

        // Filter by product type
        if ($request->has('product_type')) {
            $query->where('product_type', $request->product_type);
        }

        $featured = $query->paginate($request->per_page ?? 15);

        // Load the actual products
        $featured->getCollection()->transform(function ($item) {
            $item->product_data = $this->getProduct($item->product_type, $item->product_id);
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $featured
        ]);
    }

    /**
     * Get statistics for featured products.
     */
    public function statistics()
    {
        Gate::authorize('manageFeatured', FeaturedRequest::class);

        $stats = [
            'total_requests' => FeaturedRequest::count(),
            'pending_requests' => FeaturedRequest::pending()->count(),
            'approved_requests' => FeaturedRequest::approved()->count(),
            'rejected_requests' => FeaturedRequest::rejected()->count(),
            'active_featured' => FeaturedRequest::active()->count(),
            'expired_featured' => FeaturedRequest::expired()->count(),
            'by_product_type' => [
                'flights' => FeaturedRequest::ofType(FeaturedRequest::PRODUCT_TYPE_FLIGHT)->active()->count(),
                'hotels' => FeaturedRequest::ofType(FeaturedRequest::PRODUCT_TYPE_HOTEL)->active()->count(),
                'packages' => FeaturedRequest::ofType(FeaturedRequest::PRODUCT_TYPE_PACKAGE)->active()->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Update priority of a featured product.
     */
    public function updatePriority(Request $request, FeaturedRequest $featuredRequest)
    {
        Gate::authorize('update', $featuredRequest);

        $validated = $request->validate([
            'priority_level' => 'required|integer|min:1|max:100',
        ]);

        $featuredRequest->update([
            'priority_level' => $validated['priority_level'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Priority updated successfully.',
            'data' => $featuredRequest
        ]);
    }

    /**
     * Feature a product in the database.
     */
    private function featureProduct(string $productType, int $productId, $startDate = null, $endDate = null): void
    {
        $updateData = [
            'is_featured' => true,
            'featured_at' => now(),
            'featured_expires_at' => $endDate ? \Carbon\Carbon::parse($endDate) : null,
        ];

        match ($productType) {
            FeaturedRequest::PRODUCT_TYPE_FLIGHT => Flight::where('id', $productId)->update($updateData),
            FeaturedRequest::PRODUCT_TYPE_HOTEL => Hotel::where('id', $productId)->update($updateData),
            FeaturedRequest::PRODUCT_TYPE_PACKAGE => Package::where('id', $productId)->update($updateData),
            default => null
        };
    }

    /**
     * Unfeature a product in the database.
     */
    private function unfeatureProduct(string $productType, int $productId): void
    {
        $updateData = [
            'is_featured' => false,
            'featured_at' => null,
            'featured_expires_at' => null,
        ];

        match ($productType) {
            FeaturedRequest::PRODUCT_TYPE_FLIGHT => Flight::where('id', $productId)->update($updateData),
            FeaturedRequest::PRODUCT_TYPE_HOTEL => Hotel::where('id', $productId)->update($updateData),
            FeaturedRequest::PRODUCT_TYPE_PACKAGE => Package::where('id', $productId)->update($updateData),
            default => null
        };
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
     * Clear featured packages cache for B2C platform.
     */
    private function clearFeaturedCache(): void
    {
        try {
            $cacheKeys = [
                'b2c_featured_packages_4',
                'b2c_featured_packages_8',
                'b2c_featured_packages_12',
                'b2c_package_statistics',
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to clear featured cache: ' . $e->getMessage());
        }
    }
}
