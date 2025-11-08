<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAdRequest;
use App\Http\Requests\UpdateAdRequest;
use App\Http\Requests\UploadAdImageRequest;
use App\Models\Ad;
use App\Services\AdImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdController extends Controller
{
    public function __construct(
        protected AdImageService $imageService
    ) {
    }

    /**
     * Display a listing of ads
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Ad::class);

        $query = Ad::query()->with(['owner', 'product', 'approver']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by owner (for B2B users to see their own ads)
        if (!auth()->user()->isAdmin()) {
            $query->byOwner(auth()->id(), get_class(auth()->user()));
        }

        // Filter by product type
        if ($request->has('product_type')) {
            $query->ofProductType($request->product_type);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Order by priority and created date
        $query->byPriority('desc')->latest();

        // Paginate
        $perPage = min($request->input('per_page', 15), 50);
        $ads = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $ads,
        ]);
    }

    /**
     * Store a newly created ad
     */
    public function store(CreateAdRequest $request): JsonResponse
    {
        try {
            $ad = DB::transaction(function () use ($request) {
                $ad = Ad::create($request->validatedWithOwner());
                
                Log::info("Ad created", [
                    'ad_id' => $ad->id,
                    'user_id' => auth()->id(),
                    'title' => $ad->title,
                ]);

                return $ad;
            });

            return response()->json([
                'success' => true,
                'message' => 'Ad created successfully.',
                'data' => $ad->load(['owner', 'product']),
            ], 201);

        } catch (\Exception $e) {
            Log::error("Failed to create ad", [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create ad. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Display the specified ad
     */
    public function show(Ad $ad): JsonResponse
    {
        $this->authorize('view', $ad);

        $ad->load(['owner', 'product', 'approver']);

        return response()->json([
            'success' => true,
            'data' => $ad,
        ]);
    }

    /**
     * Update the specified ad
     */
    public function update(UpdateAdRequest $request, Ad $ad): JsonResponse
    {
        try {
            $ad = DB::transaction(function () use ($request, $ad) {
                $ad->update($request->validated());
                
                Log::info("Ad updated", [
                    'ad_id' => $ad->id,
                    'user_id' => auth()->id(),
                ]);

                return $ad;
            });

            return response()->json([
                'success' => true,
                'message' => 'Ad updated successfully.',
                'data' => $ad->fresh(['owner', 'product', 'approver']),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to update ad", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update ad. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Remove the specified ad
     */
    public function destroy(Ad $ad): JsonResponse
    {
        $this->authorize('delete', $ad);

        try {
            DB::transaction(function () use ($ad) {
                // Delete associated images
                if ($ad->hasImage()) {
                    $this->imageService->deleteImage($ad->image_path, $ad->image_variants);
                }

                $ad->delete();
                
                Log::info("Ad deleted", [
                    'ad_id' => $ad->id,
                    'user_id' => auth()->id(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Ad deleted successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to delete ad", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete ad. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Upload image for ad
     */
    public function uploadImage(UploadAdImageRequest $request, Ad $ad): JsonResponse
    {
        try {
            $result = $this->imageService->uploadImage(
                $request->file('image'),
                $ad->owner_id
            );

            // Delete old image if exists
            if ($ad->hasImage()) {
                $this->imageService->deleteImage($ad->image_path, $ad->image_variants);
            }

            // Update ad with new image paths
            $ad->update([
                'image_path' => $result['original_path'],
                'image_variants' => $result['variants'],
            ]);

            Log::info("Ad image uploaded", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully.',
                'data' => [
                    'image_path' => $ad->image_path,
                    'image_url' => $ad->image_url,
                    'image_variants' => $ad->image_variant_urls,
                    'metadata' => [
                        'dimensions' => $result['dimensions'],
                        'size' => $result['size'],
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to upload ad image", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image. ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Submit ad for approval
     */
    public function submit(Ad $ad): JsonResponse
    {
        $this->authorize('submit', $ad);

        try {
            if (!$ad->hasImage()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot submit ad without an image.',
                ], 422);
            }

            $success = $ad->submitForApproval();

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ad is not in draft status and cannot be submitted.',
                ], 422);
            }

            Log::info("Ad submitted for approval", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ad submitted for approval successfully.',
                'data' => $ad->fresh(['owner', 'product']),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to submit ad", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit ad. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Withdraw ad from approval
     */
    public function withdraw(Ad $ad): JsonResponse
    {
        $this->authorize('withdraw', $ad);

        try {
            $success = $ad->withdraw();

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ad is not pending and cannot be withdrawn.',
                ], 422);
            }

            Log::info("Ad withdrawn from approval", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ad withdrawn successfully.',
                'data' => $ad->fresh(['owner', 'product']),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to withdraw ad", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to withdraw ad. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Approve ad (Admin only)
     */
    public function approve(Request $request, Ad $ad): JsonResponse
    {
        $this->authorize('approve', $ad);

        try {
            $success = $ad->approve(auth()->user());

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ad is not pending and cannot be approved.',
                ], 422);
            }

            // Update priority if provided by admin
            if ($request->has('priority')) {
                $ad->update(['priority' => $request->priority]);
            }

            Log::info("Ad approved", [
                'ad_id' => $ad->id,
                'approved_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ad approved successfully.',
                'data' => $ad->fresh(['owner', 'product', 'approver']),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to approve ad", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve ad. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Reject ad (Admin only)
     */
    public function reject(Request $request, Ad $ad): JsonResponse
    {
        $this->authorize('reject', $ad);

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $success = $ad->reject(auth()->user(), $request->reason);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ad is not pending and cannot be rejected.',
                ], 422);
            }

            Log::info("Ad rejected", [
                'ad_id' => $ad->id,
                'rejected_by' => auth()->id(),
                'reason' => $request->reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ad rejected successfully.',
                'data' => $ad->fresh(['owner', 'product', 'approver']),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to reject ad", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject ad. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Toggle ad active status
     */
    public function toggleActive(Ad $ad): JsonResponse
    {
        $this->authorize('toggleActive', $ad);

        try {
            $ad->is_active = !$ad->is_active;
            $ad->save();

            Log::info("Ad active status toggled", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
                'is_active' => $ad->is_active,
            ]);

            return response()->json([
                'success' => true,
                'message' => $ad->is_active ? 'Ad activated successfully.' : 'Ad deactivated successfully.',
                'data' => $ad->fresh(['owner', 'product']),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to toggle ad active status", [
                'ad_id' => $ad->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle ad status. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get audit logs for ad
     */
    public function auditLogs(Ad $ad): JsonResponse
    {
        $this->authorize('viewAuditLogs', $ad);

        $logs = $ad->auditLogs()
                    ->with('user')
                    ->latest()
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Get active ads for public display
     */
    public function active(Request $request): JsonResponse
    {
        $query = Ad::query()
                    ->active()
                    ->with(['owner', 'product'])
                    ->byPriority('desc');

        // Filter by product type if provided
        if ($request->has('product_type')) {
            $query->ofProductType($request->product_type);
        }

        // Limit results
        $limit = min($request->input('limit', 10), 50);
        $ads = $query->take($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $ads,
        ]);
    }
}
