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
    public function index()
    {
        $reservations = Material_Reservation::with('club')->get()->map(function ($reservation) {
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
    public function update(Request $request, $id)
    {
        try {
            $reservation = Material_Reservation::find($id);
            if (!$reservation) {
                return response()->json(['message' => 'Material reservation not found'], 404);
            }

            $validatedData = $request->validate([
                'club_id' => 'nullable|exists:clubs,id',
                'pdf_demande' => 'nullable|file|mimes:pdf|max:2048',
                'status' => 'nullable|in:pending,approved,rejected', // Validate status
            ]);

            // Handle PDF upload if a new file is provided
            if ($request->hasFile('pdf_demande')) {
                $this->deleteOldPdf($reservation);
                $reservation->pdf_demande = $this->handlePdfUpload($request);
            }

            // Update fields
            $reservation->club_id = $validatedData['club_id'] ?? $reservation->club_id;
            $reservation->status = $validatedData['status'] ?? $reservation->status; // Update status
            $reservation->save();

            return response()->json([
                'message' => 'Material reservation updated successfully',
                'reservation' => $this->formatReservationResponse($reservation),
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating material reservation: ' . $e->getMessage(), [
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
