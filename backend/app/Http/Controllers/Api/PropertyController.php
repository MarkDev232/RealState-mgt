<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class PropertyController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private PropertyService $propertyService)
    {
        
    }

    /**
     * Display a listing of the properties.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $properties = $this->propertyService->getAllProperties($request->all());

        return PropertyResource::collection($properties);
    }

    /**
     * Store a newly created property.
     */
    public function store(PropertyRequest $request): JsonResponse
    {
        try {
            $this->authorize('create', Property::class);
            
            $property = $this->propertyService->createProperty(
                $request->validated(), 
                $request->user()
            );

            return response()->json([
                'message' => 'Property created successfully',
                'property' => new PropertyResource($property),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create property',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified property.
     */
    public function show(Property $property): JsonResponse
    {
        try {
            $property->load(['agent', 'images', 'inquiries']);

            return response()->json([
                'property' => new PropertyResource($property),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch property',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified property.
     */
    public function update(PropertyRequest $request, Property $property): JsonResponse
    {
        try {
            $this->authorize('update', $property);

            $updatedProperty = $this->propertyService->updateProperty(
                $property, 
                $request->validated()
            );

            return response()->json([
                'message' => 'Property updated successfully',
                'property' => new PropertyResource($updatedProperty),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update property',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified property.
     */
    public function destroy(Property $property): JsonResponse
    {
        try {
            $this->authorize('delete', $property);

            $this->propertyService->deleteProperty($property);

            return response()->json([
                'message' => 'Property deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete property',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get featured properties.
     */
    public function featured(): AnonymousResourceCollection
    {
        $properties = $this->propertyService->getFeaturedProperties();

        return PropertyResource::collection($properties);
    }

    /**
     * Get properties by agent.
     */
    public function agentProperties(Request $request): AnonymousResourceCollection
    {
        try {
            $properties = $this->propertyService->getPropertiesByAgent(
                $request->user(), 
                $request->all()
            );

            return PropertyResource::collection($properties);
        } catch (\Exception $e) {
            

             Log::error('Failed to fetch agent properties: ' . $e->getMessage());

            // Return empty collection but you might want to handle this differently
            // in your frontend to show error messages
            $emptyPaginator = new LengthAwarePaginator([], 0, 15);
            return PropertyResource::collection($emptyPaginator);
        }
    }

    /**
     * Update property status.
     */
    public function updateStatus(Property $property, Request $request): JsonResponse
    {
        try {
            $this->authorize('update', $property);

            $request->validate([
                'status' => 'required|in:available,sold,pending,rented',
            ]);

            $property->update(['status' => $request->status]);

            return response()->json([
                'message' => 'Property status updated successfully',
                'property' => new PropertyResource($property->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update property status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle property featured status.
     */
    public function toggleFeatured(Property $property): JsonResponse
    {
        try {
            $this->authorize('update', $property);

            $property->update(['featured' => !$property->featured]);

            return response()->json([
                'message' => 'Property featured status updated',
                'property' => new PropertyResource($property->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update featured status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}