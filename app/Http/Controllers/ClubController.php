<?php

namespace App\Http\Controllers;

use App\Models\Club;
use Illuminate\Http\Request;
use App\Http\Requests\StoreClubRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClubController extends Controller
{
    /**
     * Display a listing of all clubs.
     */
    public function index()
    {
        // Retrieve all clubs and transform the data
        $clubs = Club::all()->map(function ($club) {
            return $this->formatClubResponse($club);
        });

        Log::info('Get all clubs');

        return response()->json($clubs);
    }

    /**
     * Store a newly created club in storage.
     */
    public function store(StoreClubRequest $request)
    {
        try {
            // Handle logo upload
            $logoPath = $this->handleLogoUpload($request);

            // Convert 'active' field to boolean
            $active = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);

//            if ($request->input('active')=='true' || $request->input('active')=='false') {
//                $active = (boolean)($request->input('active'));
//            }

            // Create the club
            $club = Club::create([
                'name' => $request->name,
                'logo' => $logoPath,
                'description' => $request->description,
                'email' => $request->email,
                'phone' => $request->phone,
                'facebook' => $request->facebook,
                'instagram' => $request->instagram,
                'active' => $active,
            ]);

            Log::info('Store one club');

            // Return JSON response
            return response()->json([
                'message' => 'Club created successfully',
                'club' => $this->formatClubResponse($club),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating club: ' . $e->getMessage(), [
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified club.
     */
    public function show($id)
    {
        $club = Club::find($id);
        if (!$club) {
            return response()->json(['message' => 'Club not found'], 404);
        }
        Log::info('Show club');

        return response()->json($this->formatClubResponse($club));
    }

    /**
     * Update the specified club in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Find the club by ID
            $club = Club::find($id);
            if (!$club) {
                return response()->json(['message' => 'Club not found'], 404);
            }

            $active = $club->active;
//            if ($request->input('active')=='true' || $request->input('active')=='false') {
//                $active = (boolean)($request->input('active'));
//            }

            if ($request->input('active')){
                $active = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);
            }


            // Validate the incoming request data
            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'description' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255,' ,
                'phone' => 'nullable|string|max:20',
                'facebook' => 'nullable|string|max:255',
                'instagram' => 'nullable|string|max:255',
                'active' => 'nullable|string|in:true,false|max:5',
            ]);

            $validatedData['active'] = $active;

            // Handle logo upload if a new file is provided
            if ($request->hasFile('logo')) {
                $this->deleteOldLogo($club); // Delete the old logo if it exists
                $club->logo = $this->handleLogoUpload($request);
                $club->save();
            }

            // Update other fields with validated data
            unset($validatedData['logo']);
            $club->update($validatedData);

            Log::info('Update one club');

            // Return the updated club
            return response()->json([
                'message' => 'Club updated successfully',
                'club' => $this->formatClubResponse($club),
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating club: ' . $e->getMessage(), [
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified club from storage.
     */
    public function destroy($id)
    {
        try {
            $club = Club::find($id);
            if (!$club) {
                return response()->json(['message' => 'Club not found'], 404);
            }

            // Delete the logo file if it exists
            $this->deleteOldLogo($club);

            // Delete the club
            $club->delete();

            Log::info('Delete one club');

            return response()->json(['message' => 'Club deleted successfully']);

        } catch (\Exception $e) {
            Log::error('Error deleting club: ' . $e->getMessage());
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Search for clubs based on query parameters.
     */
    public function search(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255', // Search by club name
                'email' => 'nullable|email|max:255', // Search by email
                'active' => 'nullable|boolean',      // Filter by active status
            ]);

            // Build the query dynamically based on the provided filters
            $query = Club::query();

            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->input('name') . '%');
            }

            if ($request->has('email')) {
                $query->where('email', '=', $request->input('email'));
            }

            if ($request->has('active')) {
                $active = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);
                $query->where('active', '=', $active);
            }

            // Execute the query and retrieve matching clubs
            $clubs = $query->get()->map(function ($club) {
                return $this->formatClubResponse($club);
            });

            Log::info('Search clubs', ['filters' => $validatedData]);

            return response()->json($clubs);

        } catch (\Exception $e) {
            Log::error('Error searching clubs: ' . $e->getMessage(), [
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format the club response for consistency.
     */
    private function formatClubResponse($club)
    {
        return [
            'id' => $club->id,
            'name' => $club->name,
            'logo' => $club->logo ? url(Storage::url($club->logo)) : null, // Full URL for the logo
            'description' => $club->description,
            'email' => $club->email,
            'phone' => $club->phone,
            'facebook' => $club->facebook,
            'instagram' => $club->instagram,
            'active' => (bool) $club->active,
        ];
    }

    /**
     * Handle logo upload and return the path.
     */
    private function handleLogoUpload(Request $request)
    {
        if ($request->hasFile('logo')) {
            $logo = uniqid() . '.' . $request->logo->getClientOriginalExtension();
            $logoPath = 'logos/' . $logo;

            // Save the logo to storage
            Storage::disk('public')->put($logoPath, file_get_contents($request->logo));

            return $logoPath;
        }

        return null;
    }

    /**
     * Delete the old logo file if it exists.
     */
    private function deleteOldLogo($club)
    {
        if ($club->logo && Storage::disk('public')->exists($club->logo)) {
            Storage::disk('public')->delete($club->logo);
        }
    }
}
