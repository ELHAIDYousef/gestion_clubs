<?php

namespace App\Http\Controllers;

use App\Models\Material_Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MaterialReservationController extends Controller
{
    /**
     * Display a listing of all material reservations.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        $reservations = Material_Reservation::with('club')
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Formatage des données paginées
        $reservations->getCollection()->transform(function ($reservation) {
            return $this->formatReservationResponse($reservation);
        });

        return response()->json($reservations);
    }


    /**
     * Store a newly created material reservation in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'club_id' => 'required|exists:clubs,id',
                'pdf_demande' => 'required|file|mimes:pdf|max:2048',
            ]);

            $pdfPath = $this->handlePdfUpload($request);

            $reservation = Material_Reservation::create([
                'club_id' => $validatedData['club_id'],
                'pdf_demande' => $pdfPath,
                'status' => 'pending', // Default status
            ]);

            return response()->json([
                'message' => 'Material reservation created successfully',
                'reservation' => $this->formatReservationResponse($reservation),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating material reservation: ' . $e->getMessage(), [
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified material reservation.
     */
    public function show($id)
    {
        $reservation = Material_Reservation::with('club')->find($id);
        if (!$reservation) {
            return response()->json(['message' => 'Material reservation not found'], 404);
        }

        return response()->json($this->formatReservationResponse($reservation));
    }

    /**
     * Update the specified material reservation in storage.
     */
    /**
     * Update only the 'pdf_demande' of a specific material reservation.
     */
    public function update(Request $request, $id)
    {
        try {
            // Find the reservation by ID
            $reservation = Material_Reservation::find($id);
            if (!$reservation) {
                return response()->json(['message' => 'Material reservation not found'], 404);
            }

            // Validate the incoming request data
            $validatedData = $request->validate([
                'pdf_demande' => 'required|file|mimes:pdf|max:2048', // Ensure a valid PDF file is provided
            ]);

            // Handle PDF upload
            $this->deleteOldPdf($reservation); // Delete the old PDF if it exists
            $reservation->pdf_demande = $this->handlePdfUpload($request);

            // Save the updated reservation
            $reservation->save();

            Log::info('Updated pdf_demande for material reservation', ['reservation_id' => $id]);

            // Return the updated reservation
            return response()->json([
                'message' => 'Material reservation PDF updated successfully',
                'reservation' => $this->formatReservationResponse($reservation),
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating material reservation PDF: ' . $e->getMessage(), [
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the 'status' of a specific material reservation.
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            // Find the reservation by ID
            $reservation = Material_Reservation::find($id);
            if (!$reservation) {
                return response()->json(['message' => 'Material reservation not found'], 404);
            }

            // Validate the incoming request data
            $validatedData = $request->validate([
                'status' => 'required|in:approved,rejected', // Only allow "approved" or "rejected"
            ]);

            // Update the 'status' field
            $reservation->update(['status' => $validatedData['status']]);

            Log::info('Updated status for material reservation', ['reservation_id' => $id, 'status' => $validatedData['status']]);

            // Return the updated reservation
            return response()->json([
                'message' => 'Material reservation status updated successfully',
                'reservation' => $this->formatReservationResponse($reservation),
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating material reservation status: ' . $e->getMessage(), [
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified material reservation from storage.
     */
    public function destroy($id)
    {
        try {
            $reservation = Material_Reservation::find($id);
            if (!$reservation) {
                return response()->json(['message' => 'Material reservation not found'], 404);
            }

            // Delete the PDF file if it exists
            $this->deleteOldPdf($reservation);

            // Delete the reservation
            $reservation->delete();

            return response()->json(['message' => 'Material reservation deleted successfully']);

        } catch (\Exception $e) {
            Log::error('Error deleting material reservation: ' . $e->getMessage());
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format the material reservation response for consistency.
     */
    private function formatReservationResponse($reservation)
    {
        return [
            'id' => $reservation->id,
            'club_id' => $reservation->club_id,
            'club_name' => $reservation->club?->name,
            'pdf_demande' => $reservation->pdf_demande ? url(Storage::url($reservation->pdf_demande)) : null,
            'status' => $reservation->status, // Include status
            'created_at' => $reservation->created_at,
            'updated_at' => $reservation->updated_at,
        ];
    }

    /**
     * Handle PDF upload and return the path.
     */
    private function handlePdfUpload(Request $request)
    {
        if ($request->hasFile('pdf_demande')) {
            $pdf = uniqid() . '.' . $request->pdf_demande->getClientOriginalExtension();
            $pdfPath = 'pdfs/' . $pdf;

            // Save the PDF to storage
            Storage::disk('public')->put($pdfPath, file_get_contents($request->pdf_demande));

            return $pdfPath;
        }

        return null;
    }

    /**
     * Delete the old PDF file if it exists.
     */
    private function deleteOldPdf($reservation)
    {
        if ($reservation->pdf_demande && Storage::disk('public')->exists($reservation->pdf_demande)) {
            Storage::disk('public')->delete($reservation->pdf_demande);
        }
    }
}
