<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\NormalizeIraqiPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'phone_number' => $this->normalizeIraqiPhone($validated['phone_number']),
            'password' => bcrypt($validated['password']),
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
        $users = User::paginate($this->perPage($request));

        return response()->json($users, 200);
    }

    public function showUser(User $user)
    {
        return response()->json([
            'user' => $user,
        ], 200);
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone_number' => [
                'sometimes',
                'string',
                'regex:/^(964|0)?7[5789]\d{8}$/',
                'max:14'
            ],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_url) {
                $oldPath = str_replace(config('filesystems.disks.r2.url') . '/', '', $user->avatar_url);
                Storage::disk('r2')->delete($oldPath);
            }
            $path = $request->file('avatar')->store('avatars', 'r2');
            $validated['avatar_url'] = Storage::disk('r2')->url($path);
        }

        unset($validated['avatar']);
        if (isset($validated['phone_number'])) {
            $validated['phone_number'] = $this->normalizeIraqiPhone($validated['phone_number']);
        }
        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => $user->fresh(),
        ]);
    }

    public function deleteUser(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 403);
        }
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
}
