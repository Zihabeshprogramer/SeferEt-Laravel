<?php

namespace App\Http\Controllers\B2B\API;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Activity API Controller - AJAX Activity Management
 * 
 * Handles CRUD operations for package activities via AJAX requests
 * for the dynamic itinerary builder interface.
 */
class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:travel_agent']);
    }

    /**
     * Store a new activity for a package
     */
    public function store(Request $request, Package $package): JsonResponse
    {
        // Ensure package ownership
        if ($package->creator_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to package.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'day_number' => 'required|integer|min:1|max:365',
                'activity_name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'detailed_description' => 'nullable|string|max:10000',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after:start_time',
                'duration_minutes' => 'nullable|integer|min:1|max:1440',
                'location' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'category' => 'required|in:' . implode(',', PackageActivity::CATEGORIES),
                'difficulty_level' => 'nullable|in:easy,moderate,challenging,expert',
                'is_included' => 'nullable|boolean',
                'additional_cost' => 'nullable|numeric|min:0',
                'is_optional' => 'nullable|boolean',
                'requires_booking' => 'nullable|boolean',
                'min_participants' => 'nullable|integer|min:1',
                'max_participants' => 'nullable|integer|min:1|gte:min_participants',
                'guide_included' => 'nullable|boolean',
                'guide_cost' => 'nullable|numeric|min:0',
                'display_order' => 'nullable|integer|min:0',
                'is_highlight' => 'nullable|boolean',
                'photo_opportunities' => 'nullable|boolean',
                'shopping_available' => 'nullable|boolean',
                'meals_included' => 'nullable|boolean',
                
                // Arrays
                'highlights' => 'nullable|array',
                'highlights.*' => 'string|max:255',
                'required_items' => 'nullable|array',
                'required_items.*' => 'string|max:255',
                'recommended_items' => 'nullable|array',
                'recommended_items.*' => 'string|max:255',
                'amenities' => 'nullable|array',
                'amenities.*' => 'string|max:100',
                'images' => 'nullable|array',
                'images.*' => 'url|max:500',
            ]);

            // Set default values
            $validated['package_id'] = $package->id;
            $validated['is_active'] = true;
            $validated['availability_status'] = PackageActivity::AVAILABILITY_AVAILABLE;
            $validated['currency'] = $package->currency;

            // Calculate display order if not provided
            if (!isset($validated['display_order'])) {
                $lastOrder = PackageActivity::where('package_id', $package->id)
                    ->where('day_number', $validated['day_number'])
                    ->max('display_order');
                $validated['display_order'] = ($lastOrder ?? 0) + 1;
            }

            // Calculate duration if times are provided
            if ($validated['start_time'] && $validated['end_time'] && !$validated['duration_minutes']) {
                $start = \Carbon\Carbon::createFromFormat('H:i', $validated['start_time']);
                $end = \Carbon\Carbon::createFromFormat('H:i', $validated['end_time']);
                $validated['duration_minutes'] = $start->diffInMinutes($end);
            }

            $activity = PackageActivity::create($validated);

            // Update package pricing
            $package->updatePricing();

            return response()->json([
                'success' => true,
                'message' => 'Activity added successfully!',
                'data' => [
                    'id' => $activity->id,
                    'activity_name' => $activity->activity_name,
                    'day_number' => $activity->day_number,
                    'display_order' => $activity->display_order,
                    'category' => $activity->category,
                    'is_included' => $activity->is_included,
                    'is_optional' => $activity->is_optional,
                    'is_highlight' => $activity->is_highlight,
                    'formatted_duration' => $activity->formatted_duration,
                    'display_name' => $activity->display_name,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create activity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing activity
     */
    public function update(Request $request, Package $package, PackageActivity $activity): JsonResponse
    {
        // Ensure package ownership
        if ($package->creator_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to package.'
            ], 403);
        }

        // Ensure activity belongs to package
        if ($activity->package_id !== $package->id) {
            return response()->json([
                'success' => false,
                'message' => 'Activity does not belong to this package.'
            ], 400);
        }

        try {
            $validated = $request->validate([
                'day_number' => 'sometimes|integer|min:1|max:365',
                'activity_name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string|max:1000',
                'detailed_description' => 'nullable|string|max:10000',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after:start_time',
                'duration_minutes' => 'nullable|integer|min:1|max:1440',
                'location' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'category' => 'sometimes|in:' . implode(',', PackageActivity::CATEGORIES),
                'difficulty_level' => 'nullable|in:easy,moderate,challenging,expert',
                'is_included' => 'nullable|boolean',
                'additional_cost' => 'nullable|numeric|min:0',
                'is_optional' => 'nullable|boolean',
                'requires_booking' => 'nullable|boolean',
                'min_participants' => 'nullable|integer|min:1',
                'max_participants' => 'nullable|integer|min:1|gte:min_participants',
                'guide_included' => 'nullable|boolean',
                'guide_cost' => 'nullable|numeric|min:0',
                'display_order' => 'nullable|integer|min:0',
                'is_highlight' => 'nullable|boolean',
                'photo_opportunities' => 'nullable|boolean',
                'shopping_available' => 'nullable|boolean',
                'meals_included' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                
                // Arrays
                'highlights' => 'nullable|array',
                'highlights.*' => 'string|max:255',
                'required_items' => 'nullable|array',
                'required_items.*' => 'string|max:255',
                'recommended_items' => 'nullable|array',
                'recommended_items.*' => 'string|max:255',
                'amenities' => 'nullable|array',
                'amenities.*' => 'string|max:100',
                'images' => 'nullable|array',
                'images.*' => 'url|max:500',
            ]);

            // Recalculate duration if times are provided
            if (isset($validated['start_time']) && isset($validated['end_time'])) {
                $start = \Carbon\Carbon::createFromFormat('H:i', $validated['start_time']);
                $end = \Carbon\Carbon::createFromFormat('H:i', $validated['end_time']);
                $validated['duration_minutes'] = $start->diffInMinutes($end);
            }

            $activity->update($validated);

            // Update package pricing if cost-related fields changed
            if (isset($validated['additional_cost']) || isset($validated['is_included'])) {
                $package->updatePricing();
            }

            return response()->json([
                'success' => true,
                'message' => 'Activity updated successfully!',
                'data' => [
                    'id' => $activity->id,
                    'activity_name' => $activity->activity_name,
                    'day_number' => $activity->day_number,
                    'display_order' => $activity->display_order,
                    'category' => $activity->category,
                    'is_included' => $activity->is_included,
                    'is_optional' => $activity->is_optional,
                    'is_highlight' => $activity->is_highlight,
                    'is_active' => $activity->is_active,
                    'formatted_duration' => $activity->formatted_duration,
                    'display_name' => $activity->display_name,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update activity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an activity
     */
    public function destroy(Package $package, PackageActivity $activity): JsonResponse
    {
        // Ensure package ownership
        if ($package->creator_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to package.'
            ], 403);
        }

        // Ensure activity belongs to package
        if ($activity->package_id !== $package->id) {
            return response()->json([
                'success' => false,
                'message' => 'Activity does not belong to this package.'
            ], 400);
        }

        try {
            $activityName = $activity->activity_name;
            $activity->delete();

            // Update package pricing
            $package->updatePricing();

            return response()->json([
                'success' => true,
                'message' => "Activity '{$activityName}' deleted successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete activity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder activities within a day or across days
     */
    public function reorder(Request $request, Package $package): JsonResponse
    {
        // Ensure package ownership
        if ($package->creator_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to package.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'activities' => 'required|array',
                'activities.*.id' => 'required|exists:package_activities,id',
                'activities.*.day_number' => 'required|integer|min:1',
                'activities.*.display_order' => 'required|integer|min:0',
            ]);

            DB::beginTransaction();

            foreach ($validated['activities'] as $activityData) {
                $activity = PackageActivity::where('id', $activityData['id'])
                    ->where('package_id', $package->id)
                    ->first();

                if ($activity) {
                    $activity->update([
                        'day_number' => $activityData['day_number'],
                        'display_order' => $activityData['display_order']
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Activities reordered successfully!'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder activities: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get activities for a specific day (used for AJAX loading)
     */
    public function getByDay(Request $request, Package $package, int $day): JsonResponse
    {
        // Ensure package ownership
        if ($package->creator_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to package.'
            ], 403);
        }

        try {
            $activities = $package->getActivitiesByDay($day)->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'activity_name' => $activity->activity_name,
                    'description' => $activity->description,
                    'start_time' => $activity->start_time?->format('H:i'),
                    'end_time' => $activity->end_time?->format('H:i'),
                    'duration' => $activity->formatted_duration,
                    'location' => $activity->location,
                    'category' => $activity->category,
                    'is_included' => $activity->is_included,
                    'additional_cost' => $activity->additional_cost,
                    'is_optional' => $activity->is_optional,
                    'is_highlight' => $activity->is_highlight,
                    'display_order' => $activity->display_order,
                    'coordinates' => $activity->coordinates,
                    'main_image' => $activity->main_image,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $activities
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load activities: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate an activity (useful for creating similar activities)
     */
    public function duplicate(Package $package, PackageActivity $activity): JsonResponse
    {
        // Ensure package ownership
        if ($package->creator_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to package.'
            ], 403);
        }

        // Ensure activity belongs to package
        if ($activity->package_id !== $package->id) {
            return response()->json([
                'success' => false,
                'message' => 'Activity does not belong to this package.'
            ], 400);
        }

        try {
            $newActivity = $activity->replicate();
            $newActivity->activity_name = $activity->activity_name . ' (Copy)';
            
            // Get next display order
            $lastOrder = PackageActivity::where('package_id', $package->id)
                ->where('day_number', $activity->day_number)
                ->max('display_order');
            $newActivity->display_order = ($lastOrder ?? 0) + 1;
            
            $newActivity->save();

            return response()->json([
                'success' => true,
                'message' => 'Activity duplicated successfully!',
                'data' => [
                    'id' => $newActivity->id,
                    'activity_name' => $newActivity->activity_name,
                    'day_number' => $newActivity->day_number,
                    'display_order' => $newActivity->display_order,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate activity: ' . $e->getMessage()
            ], 500);
        }
    }
}