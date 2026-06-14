<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Traits\NormalizeIraqiPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuperAdminController extends Controller
{
    use NormalizeIraqiPhone;

    public function createUser(CreateUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $this->normalizeIraqiPhone($validated['phone_number']),
            'password' => bcrypt($validated['password']),
            'email_verified_at' => now(),
            'last_login_at' => now()
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

    public function updateUser(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

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

    public function createAdmin(CreateUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $this->normalizeIraqiPhone($validated['phone_number']),
            'password' => bcrypt($validated['password']),
            'email_verified_at' => now(),
            'last_login_at' => now(),
            'role' => 'admin'
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User has been created successfully',
            'admin' => $user,
            'token' => $token,
        ], 201);
    }

    public function indexAdmins(Request $request)
    {
        $users = User::where('role', 'admin')->paginate($this->perPage($request));

        return response()->json($users, 200);
    }

    public function showAdmin(User $user)
    {
        
        return response()->json([
            'user' => $user,
        ], 200);
    }

    public function updateAdmin(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

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

    public function deleteAdmin(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 403);
        }
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
}
