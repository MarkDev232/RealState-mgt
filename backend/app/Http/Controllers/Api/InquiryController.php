<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InquiryRequest;
use App\Http\Resources\InquiryResource;
use App\Models\Inquiry;
use App\Services\InquiryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class InquiryController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private InquiryService $inquiryService)
    {
        
    }

    /**
     * Display a listing of inquiries.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        try {
            $user = $request->user();

            if ($user->role === 'admin') {
                $inquiries = $this->inquiryService->getAllInquiries($request->all());
            } else {
                $inquiries = $this->inquiryService->getAgentInquiries($user->id, $request->all());
            }

            return InquiryResource::collection($inquiries);
        } catch (\Exception $e) {
            Log::error('Failed to fetch inquiries: ' . $e->getMessage());

            // Return empty collection but you might want to handle this differently
            // in your frontend to show error messages
            $emptyPaginator = new LengthAwarePaginator([], 0, 15);
            return InquiryResource::collection($emptyPaginator);
        }
    }

    /**
     * Store a newly created inquiry.
     */
    public function store(InquiryRequest $request): JsonResponse
    {
        try {
            $inquiry = $this->inquiryService->createInquiry($request->validated());

            return response()->json([
                'message' => 'Inquiry submitted successfully',
                'inquiry' => new InquiryResource($inquiry),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to submit inquiry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified inquiry.
     */
    public function show(Inquiry $inquiry): JsonResponse
    {
        try {
            $this->authorize('view', $inquiry);

            $inquiry->load(['property.images', 'property.agent']);

            return response()->json([
                'inquiry' => new InquiryResource($inquiry),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch inquiry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified inquiry.
     */
    public function update(InquiryRequest $request, Inquiry $inquiry): JsonResponse
    {
        try {
            $this->authorize('update', $inquiry);

            $updatedInquiry = $this->inquiryService->updateInquiry(
                $inquiry,
                $request->validated()
            );

            return response()->json([
                'message' => 'Inquiry updated successfully',
                'inquiry' => new InquiryResource($updatedInquiry),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update inquiry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified inquiry.
     */
    public function destroy(Inquiry $inquiry): JsonResponse
    {
        try {
            $this->authorize('delete', $inquiry);

            $this->inquiryService->deleteInquiry($inquiry);

            return response()->json([
                'message' => 'Inquiry deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete inquiry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark inquiry as contacted.
     */
    public function markContacted(Inquiry $inquiry, Request $request): JsonResponse
    {
        try {
            $this->authorize('update', $inquiry);

            if ($inquiry->markAsContacted($request->notes)) {
                return response()->json([
                    'message' => 'Inquiry marked as contacted',
                    'inquiry' => new InquiryResource($inquiry->fresh()),
                ]);
            }

            return response()->json([
                'message' => 'Unable to mark inquiry as contacted',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update inquiry status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark inquiry for follow-up.
     */
    public function markFollowUp(Inquiry $inquiry, Request $request): JsonResponse
    {
        try {
            $this->authorize('update', $inquiry);

            if ($inquiry->markForFollowUp($request->reason)) {
                return response()->json([
                    'message' => 'Inquiry marked for follow-up',
                    'inquiry' => new InquiryResource($inquiry->fresh()),
                ]);
            }

            return response()->json([
                'message' => 'Unable to mark inquiry for follow-up',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update inquiry status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Close inquiry.
     */
    public function close(Inquiry $inquiry, Request $request): JsonResponse
    {
        try {
            $this->authorize('update', $inquiry);

            if ($inquiry->close($request->resolution)) {
                return response()->json([
                    'message' => 'Inquiry closed successfully',
                    'inquiry' => new InquiryResource($inquiry->fresh()),
                ]);
            }

            return response()->json([
                'message' => 'Unable to close inquiry',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to close inquiry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reopen closed inquiry.
     */
    public function reopen(Inquiry $inquiry): JsonResponse
    {
        try {
            $this->authorize('update', $inquiry);

            if ($inquiry->reopen()) {
                return response()->json([
                    'message' => 'Inquiry reopened successfully',
                    'inquiry' => new InquiryResource($inquiry->fresh()),
                ]);
            }

            return response()->json([
                'message' => 'Unable to reopen inquiry',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reopen inquiry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get inquiry statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $stats = $this->inquiryService->getStatistics($user);

            return response()->json([
                'statistics' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch inquiry statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent inquiries.
     */
    public function recent(Request $request): AnonymousResourceCollection
    {
        try {
            $user = $request->user();
            $inquiries = $this->inquiryService->getRecentInquiries($user);

            return InquiryResource::collection($inquiries);
        } catch (\Exception $e) {
            
            // Log the error
            Log::error('Failed to fetch recent inquiries: ' . $e->getMessage());

            // Return empty collection but you might want to handle this differently
            // in your frontend to show error messages
            $emptyPaginator = new LengthAwarePaginator([], 0, 15);
            return InquiryResource::collection($emptyPaginator);
        }
    }
}
