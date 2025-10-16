<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InquiryService
{
    /**
     * Get all inquiries for admin.
     */
    public function getAllInquiries(array $filters = []): LengthAwarePaginator
    {
        $query = Inquiry::with(['property.images', 'property.agent'])
            ->when(isset($filters['status']), function ($q) use ($filters) {
                return $q->where('status', $filters['status']);
            })
            ->when(isset($filters['property_id']), function ($q) use ($filters) {
                return $q->where('property_id', $filters['property_id']);
            })
            ->when(isset($filters['date_from']), function ($q) use ($filters) {
                return $q->where('created_at', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']), function ($q) use ($filters) {
                return $q->where('created_at', '<=', $filters['date_to']);
            });

        return $query->byPriority()->paginate(15);
    }

    /**
     * Get inquiries for agent.
     */
    public function getAgentInquiries(int $agentId, array $filters = []): LengthAwarePaginator
    {
        $query = Inquiry::with(['property.images'])
            ->whereHas('property', function ($q) use ($agentId) {
                $q->where('agent_id', $agentId);
            })
            ->when(isset($filters['status']), function ($q) use ($filters) {
                return $q->where('status', $filters['status']);
            })
            ->when(isset($filters['property_id']), function ($q) use ($filters) {
                return $q->where('property_id', $filters['property_id']);
            })
            ->when(isset($filters['date_from']), function ($q) use ($filters) {
                return $q->where('created_at', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']), function ($q) use ($filters) {
                return $q->where('created_at', '<=', $filters['date_to']);
            });

        return $query->byPriority()->paginate(15);
    }

    /**
     * Create a new inquiry.
     */
    public function createInquiry(array $data): Inquiry
    {
        return DB::transaction(function () use ($data) {
            $inquiry = Inquiry::create($data);

            // Send notification to property agent
            // $inquiry->property->agent->notify(new NewInquiryNotification($inquiry));

            return $inquiry->load(['property.images', 'property.agent']);
        });
    }

    /**
     * Update an inquiry.
     */
    public function updateInquiry(Inquiry $inquiry, array $data): Inquiry
    {
        return DB::transaction(function () use ($inquiry, $data) {
            $inquiry->update($data);

            return $inquiry->fresh(['property.images', 'property.agent']);
        });
    }

    /**
     * Delete an inquiry.
     */
    public function deleteInquiry(Inquiry $inquiry): bool
    {
        return DB::transaction(function () use ($inquiry) {
            return $inquiry->delete();
        });
    }

    /**
     * Get inquiry statistics.
     */
    public function getStatistics(User $user): array
    {
        if ($user->role === 'admin') {
            return Inquiry::getStatistics();
        } else {
            return Inquiry::getStatistics(null, $user->id);
        }
    }

    /**
     * Get recent inquiries.
     */
    public function getRecentInquiries(User $user, int $limit = 10)
    {
        if ($user->role === 'admin') {
            return Inquiry::getRecentInquiries($limit);
        } else {
            return Inquiry::getRecentInquiries($limit, $user->id);
        }
    }

    /**
     * Bulk update inquiry status.
     */
    public function bulkUpdateStatus(array $inquiryIds, string $status): int
    {
        return DB::transaction(function () use ($inquiryIds, $status) {
            return Inquiry::whereIn('id', $inquiryIds)->update(['status' => $status]);
        });
    }

    /**
     * Search inquiries by email or name.
     */
    public function searchInquiries(string $search, ?User $user = null): LengthAwarePaginator
    {
        $query = Inquiry::with(['property.images', 'property.agent'])
            ->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });

        if ($user && $user->role === 'agent') {
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('agent_id', $user->id);
            });
        }

        return $query->byPriority()->paginate(15);
    }
}