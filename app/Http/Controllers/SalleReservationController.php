<?php

namespace App\Http\Controllers;

use App\Models\Salle_Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SalleReservationController extends Controller
{
    /**
     * Display a listing of all salle reservations.
     */
    public function index()
    {
        $reservations = Salle_Reservation::with(['club', 'salle'])->get()->map(function ($reservation) {
            return $this->formatReservationResponse($reservation);
        });

        return response()->json($reservations);
    }

    /**
     * Store a newly created salle reservation in storage.
     */
    public function store(Request $request)
    {
        Log::info('Incoming Request Data:', $request->all());



        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'salle_id' => 'required|exists:salles,id',
                'club_id' => 'required|exists:clubs,id',
                'reason' => 'required|string|max:255',
                'date' => 'required|date',
                'start_time' => 'required|date_format:H:i', // Start time in HH:mm format
                'end_time' => 'required|date_format:H:i|after:start_time', // End time must be after start time
                'status' => 'nullable|in:pending,accepted,rejected', // Optional status field
            ]);

            // Default status is 'pending'
            $status = $validatedData['status'] ?? 'pending';

            // Extract validated data
            $salleId = $validatedData['salle_id'];
            $date = $validatedData['date'];
            $startTime = $validatedData['start_time'];
            $endTime = $validatedData['end_time'];


            // Check if the salle is already reserved for the requested time range
            $isReserved = Salle_Reservation::where('salle_id', $salleId)
                ->where('date', $date)
                ->where(function ($query) use ($startTime, $endTime) {
                    // Overlap conditions:
                    // 1. New reservation starts during an existing reservation
                    // 2. New reservation ends during an existing reservation
                    // 3. New reservation fully encompasses an existing reservation
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function ($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                        });
                })
                ->where('status', '!=', 'rejected') // Ignore rejected reservations
                ->exists();



            if ($isReserved) {
                return response()->json([
                    'message' => 'The salle is already reserved for the requested time range.',
                ], 409); // HTTP 409 Conflict
            }

            // Create the salle reservation
            $reservation = Salle_Reservation::create([
                'salle_id' => $salleId,
                'club_id' => $validatedData['club_id'],
                'reason' => $validatedData['reason'],
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $status,
            ]);


            // Return JSON response
            return response()->json([
                'message' => 'Salle reservation created successfully',
                'reservation' => $this->formatReservationResponse($reservation),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating salle reservation: ' . $e->getMessage(), [
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified salle reservation.
     */
    public function show($id)
    {
        $reservation = Salle_Reservation::with(['club', 'salle'])->find($id);
        if (!$reservation) {
            return response()->json(['message' => 'Salle reservation not found'], 404);
        }

        return response()->json($this->formatReservationResponse($reservation));
    }

    /**
     * Update the specified salle reservation in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $reservation = Salle_Reservation::find($id);
            if (!$reservation) {
                return response()->json(['message' => 'Salle reservation not found'], 404);
            }

            // Validate the incoming request data
            $validatedData = $request->validate([
                'salle_id' => 'nullable|exists:salles,id',
                'club_id' => 'nullable|exists:clubs,id',
                'reason' => 'nullable|string|max:255',
                'date' => 'nullable|date',
                'status' => 'nullable|in:pending,accepted,rejected',
            ]);

            // Update fields
            $reservation->salle_id = $validatedData['salle_id'] ?? $reservation->salle_id;
            $reservation->club_id = $validatedData['club_id'] ?? $reservation->club_id;
            $reservation->reason = $validatedData['reason'] ?? $reservation->reason;
            $reservation->date = $validatedData['date'] ?? $reservation->date;
            $reservation->status = $validatedData['status'] ?? $reservation->status;

            $reservation->save();

            // Return the updated reservation
            return response()->json([
                'message' => 'Salle reservation updated successfully',
                'reservation' => $this->formatReservationResponse($reservation),
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating salle reservation: ' . $e->getMessage(), [
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified salle reservation from storage.
     */
    public function destroy($id)
    {
        try {
            $reservation = Salle_Reservation::find($id);
            if (!$reservation) {
                return response()->json(['message' => 'Salle reservation not found'], 404);
            }

            $reservation->delete();

            return response()->json(['message' => 'Salle reservation deleted successfully']);

        } catch (\Exception $e) {
            Log::error('Error deleting salle reservation: ' . $e->getMessage());
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format the salle reservation response for consistency.
     */
    private function formatReservationResponse($reservation)
    {
        return [
            'id' => $reservation->id,
            'salle_id' => $reservation->salle_id,
            'salle_name' => $reservation->salle?->name, // Include the salle name for convenience
            'club_id' => $reservation->club_id,
            'club_name' => $reservation->club?->name, // Include the club name for convenience
            'reason' => $reservation->reason,
            'start-time' => $reservation->start_time,
            'end-time' => $reservation->end_time,
            'date' => $reservation->date,
            'status' => $reservation->status,
            'created_at' => $reservation->created_at,
            'updated_at' => $reservation->updated_at,
        ];
    }
}
