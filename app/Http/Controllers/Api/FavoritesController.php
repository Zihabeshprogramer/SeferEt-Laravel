<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserFavorite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FavoritesController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get all favorites for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $type = $request->query('type');
            $perPage = $request->query('per_page', 15);

            $query = UserFavorite::forUser($user->id)->recent();

            if ($type && in_array($type, UserFavorite::TYPES)) {
                $query->ofType($type);
            }

            $favorites = $query->paginate($perPage);

            // Transform the favorites data
            $transformedData = $favorites->through(function ($favorite) {
                return [
                    'id' => $favorite->id,
                    'type' => $favorite->type,
                    'title' => $favorite->title,
                    'item_data' => $favorite->formatted_item_data,
                    'is_available' => $favorite->is_available,
                    'notes' => $favorite->notes,
                    'created_at' => $favorite->created_at->toISOString(),
                    'updated_at' => $favorite->updated_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Favorites retrieved successfully',
                'data' => $transformedData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve favorites',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add an item to favorites
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => ['required', 'string', 'in:' . implode(',', UserFavorite::TYPES)],
                'item_data' => 'required|array',
                'reference_id' => 'nullable|integer',
                'title' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = Auth::user();
            $data = $validator->validated();

            // Check if favorite already exists
            if (UserFavorite::existsForUser($user->id, $data['type'], $data['reference_id'] ?? null)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item is already in favorites',
                ], 409);
            }

            // Create the favorite
            $favorite = UserFavorite::addFavorite(
                $user->id,
                $data['type'],
                $data['item_data'],
                $data['reference_id'] ?? null,
                $data['title'] ?? null,
                $data['notes'] ?? null
            );

            if (!$favorite) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add favorite',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Item added to favorites successfully',
                'data' => [
                    'id' => $favorite->id,
                    'type' => $favorite->type,
                    'title' => $favorite->title,
                    'item_data' => $favorite->formatted_item_data,
                    'is_available' => $favorite->is_available,
                    'notes' => $favorite->notes,
                    'created_at' => $favorite->created_at->toISOString(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add favorite',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific favorite
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $favorite = UserFavorite::forUser($user->id)->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Favorite retrieved successfully',
                'data' => [
                    'id' => $favorite->id,
                    'type' => $favorite->type,
                    'title' => $favorite->title,
                    'item_data' => $favorite->formatted_item_data,
                    'is_available' => $favorite->is_available,
                    'notes' => $favorite->notes,
                    'created_at' => $favorite->created_at->toISOString(),
                    'updated_at' => $favorite->updated_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Favorite not found',
            ], 404);
        }
    }

    /**
     * Update favorite notes
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'notes' => 'nullable|string|max:1000',
                'is_available' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = Auth::user();
            $favorite = UserFavorite::forUser($user->id)->findOrFail($id);
            
            $data = $validator->validated();
            $favorite->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Favorite updated successfully',
                'data' => [
                    'id' => $favorite->id,
                    'type' => $favorite->type,
                    'title' => $favorite->title,
                    'item_data' => $favorite->formatted_item_data,
                    'is_available' => $favorite->is_available,
                    'notes' => $favorite->notes,
                    'updated_at' => $favorite->updated_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update favorite',
            ], 500);
        }
    }

    /**
     * Remove an item from favorites
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $favorite = UserFavorite::forUser($user->id)->findOrFail($id);
            
            $favorite->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item removed from favorites successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Favorite not found',
            ], 404);
        }
    }

    /**
     * Check if an item is in favorites
     */
    public function check(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => ['required', 'string', 'in:' . implode(',', UserFavorite::TYPES)],
                'reference_id' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = Auth::user();
            $data = $validator->validated();

            $exists = UserFavorite::existsForUser($user->id, $data['type'], $data['reference_id'] ?? null);

            return response()->json([
                'success' => true,
                'is_favorite' => $exists,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check favorite status',
            ], 500);
        }
    }

    /**
     * Get favorites count by type
     */
    public function counts(): JsonResponse
    {
        try {
            $user = Auth::user();

            $counts = [];
            foreach (UserFavorite::TYPES as $type) {
                $counts[$type] = UserFavorite::forUser($user->id)->ofType($type)->count();
            }

            $counts['total'] = UserFavorite::forUser($user->id)->count();

            return response()->json([
                'success' => true,
                'data' => $counts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get favorites count',
            ], 500);
        }
    }

    /**
     * Remove multiple favorites
     */
    public function destroyMultiple(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = Auth::user();
            $ids = $validator->validated()['ids'];

            $deletedCount = UserFavorite::forUser($user->id)->whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} items removed from favorites successfully",
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove favorites',
            ], 500);
        }
    }
}