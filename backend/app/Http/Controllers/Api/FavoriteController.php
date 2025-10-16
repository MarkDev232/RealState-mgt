<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    public function __construct() {}

    /**
     * Display a listing of user's favorite properties.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        try {
            $favorites = $request->user()
                ->favorites()
                ->with(['property.images', 'property.agent'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            $properties = $favorites->getCollection()->map->property;

            return PropertyResource::collection($properties);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to fetch favorites: ' . $e->getMessage());

            // Return empty collection but you might want to handle this differently
            // in your frontend to show error messages
            $emptyPaginator = new LengthAwarePaginator([], 0, 15);
            return PropertyResource::collection($emptyPaginator);
        }
    }

    /**
     * Toggle favorite status for a property.
     */
    public function toggle(Property $property, Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $isFavorited = \App\Models\Favorite::toggle($user->id, $property->id);

            return response()->json([
                'message' => $isFavorited ? 'Property added to favorites' : 'Property removed from favorites',
                'is_favorite' => $isFavorited,
                'favorites_count' => \App\Models\Favorite::countForProperty($property->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle favorite',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if property is favorited by user.
     */
    public function check(Property $property, Request $request): JsonResponse
    {
        try {
            $isFavorited = \App\Models\Favorite::exists($request->user()->id, $property->id);

            return response()->json([
                'is_favorite' => $isFavorited,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to check favorite status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove property from favorites.
     */
    public function destroy(Property $property, Request $request): JsonResponse
    {
        try {
            $favorite = $request->user()
                ->favorites()
                ->where('property_id', $property->id)
                ->first();

            if ($favorite) {
                $favorite->delete();
            }

            return response()->json([
                'message' => 'Property removed from favorites',
                'favorites_count' => \App\Models\Favorite::countForProperty($property->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove from favorites',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's favorite count.
     */
    public function count(Request $request): JsonResponse
    {
        try {
            $count = $request->user()->favorites()->count();

            return response()->json([
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get favorites count',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
