<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ClubAdminCredentials;

class UserManagementController extends Controller
{
    public function createClubAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'club_id' => 'required|exists:clubs,id',
        ]);

        $password = Str::random(10); // Mot de passe auto

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => 'admin_club',
            'club_id' => $request->club_id,
        ]);

        Mail::to($request->email)->send(new ClubAdminCredentials($request->email, $password));

        return response()->json([
            'message' => 'Admin club created and password sent to email.',
            'user_id' => $user->id,
            'email_sent_to' => $request->email
        ]);
    }
}
