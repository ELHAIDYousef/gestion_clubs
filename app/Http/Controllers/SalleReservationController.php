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
    // public function index()
    // {
    //     try {
    //         $reservations = Salle_Reservation::with(['club', 'salle'])->get()->map(function ($reservation) {
    //             // Update status if time has passed
    //             $currentTime = now();
    //             $endTime = $reservation->date . ' ' . $reservation->end_time;

    //             if ($currentTime > $endTime && $reservation->status != 'finished') {
    //                 $reservation->status = 'finished';
    //                 $reservation->save();
    //             }

    //             return $this->formatReservationResponse($reservation);
    //         });

    //         return response()->json($reservations);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Something went wrong!',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function index(Request $request)
    {
        try {
            // Récupérer les paramètres de pagination
            $perPage = $request->query('per_page', 10);
            $page = $request->query('page', 1);

            // Récupérer les réservations avec pagination (avec relations)
            $reservationsQuery = Salle_Reservation::with(['club', 'salle']);

            // Obtenir les résultats paginés
            $reservationsPaginated = $reservationsQuery->paginate($perPage, ['*'], 'page', $page);

            // Transformer chaque élément + mise à jour du statut
            $reservationsPaginated->getCollection()->transform(function ($reservation) {
                $currentTime = now();
                $endTime = $reservation->date . ' ' . $reservation->end_time;

                if ($currentTime > $endTime && $reservation->status != 'finished') {
                    $reservation->status = 'finished';
                    $reservation->save();
                }

                return $this->formatReservationResponse($reservation);
            });

            return response()->json($reservationsPaginated);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created salle reservation in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'salle_id' => 'required|exists:salles,id',
                'club_id' => 'required|exists:clubs,id',
                'reason' => 'required|string|max:255',
                'date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'status' => 'nullable|in:pending,accepted,rejected,finished',
            ]);

            // Check overlapping reservations
            $overlap = Salle_Reservation::where('salle_id', $validatedData['salle_id'])
                ->where('date', $validatedData['date'])
                ->where(function ($query) use ($validatedData) {
                    $query->whereBetween('start_time', [$validatedData['start_time'], $validatedData['end_time']])
                        ->orWhereBetween('end_time', [$validatedData['start_time'], $validatedData['end_time']])
                        ->orWhere(function ($q) use ($validatedData) {
                            $q->where('start_time', '<=', $validatedData['start_time'])
                                ->where('end_time', '>=', $validatedData['end_time']);
                        });
                })
                ->where('status', '!=', 'rejected')
                ->exists();

            if ($overlap) {
                return response()->json(['message' => 'Salle already reserved in this time range'], 409);
            }

            $validatedData['status'] = $validatedData['status'] ?? 'pending';

            $reservation = Salle_Reservation::create($validatedData);

            return response()->json([
                'message' => 'Reservation created',
                'reservation' => $this->formatReservationResponse($reservation->load(['club', 'salle']))
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error storing reservation: ' . $e->getMessage());
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified salle reservation.
     */
    public function show($id)
    {
        $reservation = Salle_Reservation::with(['club', 'salle'])->find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $this->autoFinishReservation($reservation);

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

            $validatedData = $request->validate([
                'salle_id' => 'nullable|exists:salles,id',
                'club_id' => 'nullable|exists:clubs,id',
                'reason' => 'nullable|string|max:255',
                'date' => 'nullable|date',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after:start_time',
            ]);

            $salleId = $validatedData['salle_id'] ?? $reservation->salle_id;
            $date = $validatedData['date'] ?? $reservation->date;
            $startTime = $validatedData['start_time'] ?? $reservation->start_time;
            $endTime = $validatedData['end_time'] ?? $reservation->end_time;

            // Check for time conflict
            $conflictExists = Salle_Reservation::where('salle_id', $salleId)
                ->where('date', $date)
                ->where('id', '!=', $id) // exclude current reservation
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function ($q) use ($startTime, $endTime) {
                            $q->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                        });
                })
                ->where('status', '!=', 'rejected')
                ->exists();

            if ($conflictExists) {
                return response()->json([
                    'message' => 'The salle is already reserved for the selected time range.',
                ], 409);
            }

            // Apply updates
            $reservation->salle_id = $salleId;
            $reservation->club_id = $validatedData['club_id'] ?? $reservation->club_id;
            $reservation->reason = $validatedData['reason'] ?? $reservation->reason;
            $reservation->date = $date;
            $reservation->start_time = $startTime;
            $reservation->end_time = $endTime;

            $reservation->save();

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


    public function updateStatus(Request $request, $id)
    {
        try {
            $reservation = Salle_Reservation::with(['club', 'salle'])->find($id); // eager load
            if (!$reservation) {
                return response()->json(['message' => 'Salle reservation not found'], 404);
            }

            $validatedData = $request->validate([
                'status' => 'required|in:pending,accepted,rejected,finished',
            ]);

            $reservation->status = $validatedData['status'];
            $reservation->save();

            // Re-fetch with relations if you didn't load earlier
            $reservation->load(['club', 'salle']);

            return response()->json([
                'message' => 'Status updated successfully',
                'reservation' => $this->formatReservationResponse($reservation),
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating salle reservation status: ' . $e->getMessage(), [
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
    public function getClubReservationsByStatus($clubId, Request $request)
    {
        try {
            $status = $request->query('status', 'pending'); // Default to 'pending' if no status is provided
            $perPage = $request->query('per_page', 10); // Number of items per page, default to 10
            $page = $request->query('page', 1); // Page number, default to 1

            // Get the reservations for the club with the given status
            $reservations = Salle_Reservation::with(['club', 'salle'])
                ->where('club_id', $clubId)
                ->where('status', $status)
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Check if there are any reservations
            if ($reservations->isEmpty()) {
                return response()->json(['message' => 'No reservations found for this status'], 404);
            }

            // Format each reservation to include club and salle names
            $formattedReservations = $reservations->getCollection()->map(function ($reservation) {
                return $this->formatReservationResponse($reservation);
            });

            // Replace the collection with the formatted data
            $reservations->setCollection($formattedReservations);

            // Return the formatted response
            return response()->json([
                'reservations' => $reservations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
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
            'salle_name' => $reservation->salle?->name,
            'club_id' => $reservation->club_id,
            'club_name' => $reservation->club?->name,
            'reason' => $reservation->reason,
            'date' => $reservation->date,
            'start_time' => $reservation->start_time,
            'end_time' => $reservation->end_time,
            'status' => $reservation->status,
            'created_at' => $reservation->created_at,
            'updated_at' => $reservation->updated_at,
        ];
    }
}
