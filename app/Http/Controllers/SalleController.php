<?php

namespace App\Http\Controllers;

use App\Models\Salle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SalleController extends Controller
{
    /**
     * Display a listing of all salles.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        $salles = Salle::orderBy('name', 'asc')  // Sort salles by name alphabetically
        ->paginate($perPage, ['*'], 'page', $page);

        // Apply custom formatting
        $salles->getCollection()->transform(function ($salle) {
            return $this->formatSalleResponse($salle);
        });

        return response()->json($salles);
    }

    /**
     * Store a newly created salle in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'name' => 'required|string|unique:salles|max:255',
                'availability' => 'nullable|string|in:true,false|max:5',
            ]);

            // Convert 'availability' field to boolean
            $availability = filter_var($request->input('availability'), FILTER_VALIDATE_BOOLEAN);

            // Create the salle
            $salle = Salle::create([
                'name' => $validatedData['name'],
                'availability' => $availability,
            ]);

            // Return JSON response
            return response()->json([
                'message' => 'Salle created successfully',
                'salle' => $this->formatSalleResponse($salle),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating salle: ' . $e->getMessage(), [
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAvailableSalles(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        $salles = Salle::where('availability', true)  // Only available salles
        ->orderBy('name', 'asc')  // Sort by name alphabetically
        ->paginate($perPage, ['*'], 'page', $page);

        // Apply custom formatting
        $salles->getCollection()->transform(function ($salle) {
            return $this->formatSalleResponse($salle);
        });

        return response()->json($salles);
    }

    /**
     * Display the specified salle.
     */
    public function show($id)
    {
        $salle = Salle::find($id);
        if (!$salle) {
            return response()->json(['message' => 'Salle not found'], 404);
        }

        return response()->json($this->formatSalleResponse($salle));
    }

    /**
     * Update the specified salle in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Find the salle by ID
            $salle = Salle::find($id);
            if (!$salle) {
                return response()->json(['message' => 'Salle not found'], 404);
            }

            // Validate the incoming request data
            $validatedData = $request->validate([
                'name' => 'nullable|string|unique:salles|max:255',
                'availability' => 'nullable|string|in:true,false|max:5',
            ]);

            // Convert 'availability' field to boolean
            $availability = $salle->availability; // Default to the current value
            if ($request->has('availability')) {
                $availability = filter_var($request->input('availability'), FILTER_VALIDATE_BOOLEAN);
            }

            // Update the salle
            $salle->update([
                'name' => $validatedData['name'] ?? $salle->name,
                'availability' => $availability,
            ]);

            // Return the updated salle
            return response()->json([
                'message' => 'Salle updated successfully',
                'salle' => $this->formatSalleResponse($salle),
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating salle: ' . $e->getMessage(), [
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified salle from storage.
     */
    public function destroy($id)
    {
        try {
            $salle = Salle::find($id);
            if (!$salle) {
                return response()->json(['message' => 'Salle not found'], 404);
            }

            // Delete the salle
            $salle->delete();

            return response()->json(['message' => 'Salle deleted successfully']);

        } catch (\Exception $e) {
            Log::error('Error deleting salle: ' . $e->getMessage());
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format the salle response for consistency.
     */
    private function formatSalleResponse($salle)
    {
        return [
            'id' => $salle->id,
            'name' => $salle->name,
            'availability' => (bool) $salle->availability,
            'created_at' => $salle->created_at,
            'updated_at' => $salle->updated_at,
        ];
    }
}
