<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FeaturedRequest;
use App\Models\Flight;
use App\Models\Hotel;
use App\Models\Package;

class FeaturedRequestPolicy
{
    /**
     * Determine if the user can view any featured requests.
     */
    public function viewAny(User $user): bool
    {
        // Admin can view all requests
        if ($user->isAdmin()) {
            return true;
        }

        // B2B users can view their own requests
        return $user->isB2BUser();
    }

    /**
     * Determine if the user can view the featured request.
     */
    public function view(User $user, FeaturedRequest $featuredRequest): bool
    {
        // Admin can view all
        if ($user->isAdmin()) {
            return true;
        }

        // User can view their own requests
        return $user->id === $featuredRequest->requested_by;
    }

    /**
     * Determine if the user can create a featured request for a product.
     */
    public function create(User $user, string $productType, int $productId): bool
    {
        // Only B2B users can request features
        if (!$user->isB2BUser()) {
            return false;
        }

        // Check ownership based on product type
        switch ($productType) {
            case FeaturedRequest::PRODUCT_TYPE_FLIGHT:
                $flight = Flight::find($productId);
                return $flight && $flight->provider_id === $user->id;

            case FeaturedRequest::PRODUCT_TYPE_HOTEL:
                $hotel = Hotel::find($productId);
                return $hotel && $hotel->provider_id === $user->id;

            case FeaturedRequest::PRODUCT_TYPE_PACKAGE:
                $package = Package::find($productId);
                return $package && $package->creator_id === $user->id;

            default:
                return false;
        }
    }

    /**
     * Determine if the user can update the featured request.
     * Only admins can update (approve/reject)
     */
    public function update(User $user, FeaturedRequest $featuredRequest): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can approve the featured request.
     */
    public function approve(User $user, FeaturedRequest $featuredRequest): bool
    {
        return $user->isAdmin() && $featuredRequest->isPending();
    }

    /**
     * Determine if the user can reject the featured request.
     */
    public function reject(User $user, FeaturedRequest $featuredRequest): bool
    {
        return $user->isAdmin() && $featuredRequest->isPending();
    }

    /**
     * Determine if the user can delete the featured request.
     */
    public function delete(User $user, FeaturedRequest $featuredRequest): bool
    {
        // Admin can delete any request
        if ($user->isAdmin()) {
            return true;
        }

        // User can delete their own pending requests
        return $user->id === $featuredRequest->requested_by && 
               $featuredRequest->isPending();
    }

    /**
     * Determine if the user can manually feature a product.
     * This is for direct admin featuring without a request
     */
    public function manualFeature(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can unfeature a product.
     */
    public function unfeature(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine if the user can manage all featured products.
     */
    public function manageFeatured(User $user): bool
    {
        return $user->isAdmin();
    }
}
