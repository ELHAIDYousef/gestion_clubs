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

    public function index(Request $req)
    {
        try {

            $perPage = $req->query('per_page', 10);
            $page = $req->query('page', 1);


            $clubs = Club::orderBy('name', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

            return response()->json($clubs);

        } catch (Exception $e) {
            return response()->json([
                "message" => "Something went wrong",
                "error" => $e->getMessage()
            ]);
        }
    }

    public function indexSuperAdmin(Request $request, $clubId)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $page = $request->query('page', 1);
            $search = $request->query('email');

            $club = Club::find($clubId);

            if (!$club) {
                return response()->json(['message' => 'Club not found'], 404);
            }

            $query = $club->users(); // Get the users related to this specific club

            // Apply search filter if necessary
            if (!empty($search)) {
                $query->where('email', 'like', "%$search%");
            }

            $users = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

            // Format the response with the necessary details
            $formattedUsers = $users->map(function ($user) use ($club) {
                return [
                    'id' => $user->id,
                    'name' => $user->name, // Assuming the user has a 'name' field
                    'email' => $user->email,
                    'club_name' => $club->name, // Club name
                    'club_phone' => $club->phone, // Club phone
                    'club_email' => $club->email, // Club email
                    'status' => $user->status, // Assuming the 'status' field exists on the user
                ];
            });

            return response()->json($formattedUsers);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
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
                'linkedin' => $request->linkedin,
                'active' => $active,
            ]);

            //Log::info('Store one club');

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
                'linkedin' => 'nullable|string|max:255',
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

            //Log::info('Update one club');

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
     * Update the 'active' status of a specific club.
     */
    public function updateActiveStatus(Request $request, $id)
    {
        try {
            // Find the club by ID
            $club = Club::find($id);
            if (!$club) {
                return response()->json(['message' => 'Club not found'], 404);
            }

            $active = $club->active;

            if ($request->input('active')){
                $active = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);
            }

            // Validate the incoming request data
            $validatedData = $request->validate([
                'active' => 'required|string|in:true,false|max:5', // Ensure 'active' is provided and is a boolean
            ]);

            $validatedData['active'] = $active;

            // Update the 'active' field
            $club->update(['active' => $validatedData['active']]);

            // Return the updated club
            return response()->json([
                'message' => 'Club active status updated successfully',
                'club' => $this->formatClubResponse($club),
            ]);

        } catch (\Exception $e) {
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

            //Log::info('Delete one club');

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
            'linkedin' => $club->linkedin,
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
