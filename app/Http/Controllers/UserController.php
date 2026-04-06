<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Afficher la liste des utilisateurs avec pagination
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $search = $request->query('email');

        $query = User::query();

        if (!empty($search)) {
            $query->where('email', 'like', "%$search%");
        }

        $users = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

        return response()->json($users);
    }


    // Créer un nouvel utilisateur
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => ['required', Rule::in(['super_admin', 'admin_club'])],
            'club_id' => 'nullable|exists:clubs,id'
        ]);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'club_id' => $request->club_id
        ]);

        return response()->json(['message' => 'User created', 'user' => $user], 201);
    }

    // Afficher un utilisateur spécifique
    public function show(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    // Mettre à jour un utilisateur
    /*public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'email' => ['email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:6',
            'role' => [Rule::in(['super_admin', 'admin_club'])],
            'club_id' => 'nullable|exists:clubs,id'
        ]);

        $user->update([
            'email' => $request->email ?? $user->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'role' => $request->role ?? $user->role,
            'club_id' => $request->club_id ?? $user->club_id
        ]);

        return response()->json(['message' => 'User updated', 'user' => $user]);
    }
    */

    // Supprimer un utilisateur
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
}
