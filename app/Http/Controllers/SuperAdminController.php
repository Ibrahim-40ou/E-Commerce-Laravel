<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\NormalizeIraqiPhone;
use Illuminate\Http\Request;

// CRUD on admins
// CRUD on users

class SuperAdminController extends Controller
{
    use NormalizeIraqiPhone;

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:254',
            'phone_number' => [
                'required',
                'string',
                'regex:/^(964|0)?7[5789]\d{8}$/',
                'max:14'
            ],
            'password' => 'required|string|confirmed|min:8|max:128'
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'password' => $validated['password'],
            'email_verified_at' => now(),
            'last_login_at' => now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User has been created successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function indexUsers(Request $request)
    {
        $users = User::all();

        return response()->json(['users' => [$users], 200]);
    }

    public function showUser(User $user)
    {
        return response()->json([
            'user' => $user,
        ], 200);
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate(
            [
                'name' => 'sometimes|string',
                'avatar_url' => 'sometimes|string',
                'phone_number' => [
                    'required',
                    'string',
                    'regex:/^(964|0)?7[5789]\d{8}$/',
                    'max:14'
                ],
            ]
        );
    }
}
