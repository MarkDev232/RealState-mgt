<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AppointmentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private AppointmentService $appointmentService)
    {
       
    }

    /**
     * Display a listing of appointments.
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $user = $request->user();
            
            if ($user->role === 'agent') {
                $appointments = $this->appointmentService->getAgentAppointments($user->id, $request->all());
            } else {
                $appointments = $this->appointmentService->getUserAppointments($user->id, $request->all());
            }

            return AppointmentResource::collection($appointments);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch appointments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created appointment.
     */
    public function store(AppointmentRequest $request): JsonResponse
    {
        try {
            $appointment = $this->appointmentService->createAppointment(
                $request->validated(), 
                $request->user()
            );

            return response()->json([
                'message' => 'Appointment scheduled successfully',
                'appointment' => new AppointmentResource($appointment),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to schedule appointment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment): JsonResponse
    {
        try {
            $this->authorize('view', $appointment);

            $appointment->load(['property.images', 'user', 'agent']);

            return response()->json([
                'appointment' => new AppointmentResource($appointment),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch appointment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified appointment.
     */
    public function update(AppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        try {
            $this->authorize('update', $appointment);

            $updatedAppointment = $this->appointmentService->updateAppointment(
                $appointment, 
                $request->validated()
            );

            return response()->json([
                'message' => 'Appointment updated successfully',
                'appointment' => new AppointmentResource($updatedAppointment),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update appointment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy(Appointment $appointment): JsonResponse
    {
        try {
            $this->authorize('delete', $appointment);

            $this->appointmentService->deleteAppointment($appointment);

            return response()->json([
                'message' => 'Appointment cancelled successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel appointment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm an appointment.
     */
    public function confirm(Appointment $appointment): JsonResponse
    {
        try {
            $this->authorize('update', $appointment);

            if ($appointment->confirm()) {
                return response()->json([
                    'message' => 'Appointment confirmed successfully',
                    'appointment' => new AppointmentResource($appointment->fresh()),
                ]);
            }

            return response()->json([
                'message' => 'Unable to confirm appointment',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to confirm appointment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel an appointment.
     */
    public function cancel(Appointment $appointment, Request $request): JsonResponse
    {
        try {
            $this->authorize('update', $appointment);

            if ($appointment->cancel($request->reason)) {
                return response()->json([
                    'message' => 'Appointment cancelled successfully',
                    'appointment' => new AppointmentResource($appointment->fresh()),
                ]);
            }

            return response()->json([
                'message' => 'Unable to cancel appointment',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel appointment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete an appointment.
     */
    public function complete(Appointment $appointment): JsonResponse
    {
        try {
            $this->authorize('update', $appointment);

            if ($appointment->complete()) {
                return response()->json([
                    'message' => 'Appointment marked as completed',
                    'appointment' => new AppointmentResource($appointment->fresh()),
                ]);
            }

            return response()->json([
                'message' => 'Unable to complete appointment',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to complete appointment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available time slots for an agent.
     */
    public function availableSlots(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'agent_id' => 'required|exists:users,id',
                'date' => 'required|date',
            ]);

            $slots = $this->appointmentService->getAvailableSlots(
                $request->agent_id,
                $request->date
            );

            return response()->json([
                'slots' => $slots,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch available slots',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get appointment statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $stats = $this->appointmentService->getStatistics($user);

            return response()->json([
                'statistics' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch appointment statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}