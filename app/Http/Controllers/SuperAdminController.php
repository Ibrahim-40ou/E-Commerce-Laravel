<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Traits\NormalizeIraqiPhone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuperAdminController extends Controller
{
    use NormalizeIraqiPhone;

    private function createAccount(array $validated, string $role): User
    {
        return User::query()->create(
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone_number' => $this->normalizeIraqiPhone($validated['phone_number']),
                'password' => bcrypt($validated['password']),
                'email_verified_at' => now(),
                'last_login_at' => now(),
                'role' => $role,
            ]
        );
    }

    private function updateAccount(UpdateUserRequest $request, User $user): User
    {
        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $this->deleteAvatar($user);
            $path = $request->file('avatar')->store('avatars', 'r2');
            $validated['avatar_url'] = Storage::disk('r2')->url($path);
        }

        unset($validated['avatar']);

        if (isset($validated['phone_number'])) {
            $validated['phone_number'] = $this->normalizeIraqiPhone($validated['phone_number']);
        }
        $user->update($validated);

        return $user->fresh();
    }

    private function deleteAccount(Request $request, User $user): JsonResponse
    {
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'You can not delete your own account.'], 403);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Account deleted successfully.']);
    }

    private function deleteAvatar(User $user): void
    {
        if ($user->avatar_url) {
            $oldPath = str_replace(config('filesystems.disks.r2.url').'/', '', $user->avatar_url);
            Storage::disk('r2')->delete($oldPath);
        }
    }

    public function createUser(CreateUserRequest $request): JsonResponse
    {
        $user = $this->createAccount($request->validated(), 'user');

        return response()->json(
            [
                'message' => 'User created successfully.',
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
            ],
            201
        );
    }

    public function indexUsers(Request $request): JsonResponse
    {
        $users = User::where('role', 'user')->paginate($this->perPage($request));

        return response()->json($users);
    }

    public function showUser(User $user): JsonResponse
    {
        abort_unless($user->role === 'user', 404);

        return response()->json(['user' => $user]);
    }

    public function updateUser(UpdateUserRequest $request, User $user): JsonResponse
    {
        abort_unless($user->role === 'user', 404);

        return response()->json(
            [
                'message' => 'User updated successfully.',
                'user' => $this->updateAccount($request, $user),
            ],
        );
    }

    public function deleteUser(Request $request, User $user): JsonResponse
    {
        abort_unless($user->role === 'user', 404);

        return $this->deleteAccount($request, $user);
    }

    public function createAdmin(CreateUserRequest $request): JsonResponse
    {
        $user = $this->createAccount($request->validated(), 'admin');
        return response()->json(
            [
                'message' => 'User created successfully',
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken
            ],
            201
        );
    }

    public function indexAdmins(Request $request): JsonResponse
    {
        $users = User::where('role', 'admin')->paginate($this->perPage($request));

        return response()->json($users);
    }

    public function showAdmin(User $user): JsonResponse
    {
        abort_unless($user->role === 'admin', 404);

        return response()->json(['user' => $user]);
    }

    public function updateAdmin(UpdateUserRequest $request, User $user): JsonResponse
    {
        abort_unless($user->role === 'admin', 404);

        return response()->json(
            [
                'message' => 'Admin updated successfully.',
                'user' => $this->updateAccount($request, $user),
            ],
        );
    }

    public function deleteAdmin(Request $request, User $user): JsonResponse
    {
        abort_unless($user->role === 'admin', 404);

        return $this->deleteAccount($request, $user);
    }
}
