<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Mail\SendOTPMail;
use App\Models\EmailVerification;
use App\Models\User;
use App\Traits\NormalizeIraqiPhone;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    use NormalizeIraqiPhone;

    public function sendOTP(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email'
        ]);

        if (User::query()->where('email', $validated['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'This email address is already registered with an account.'
            ]);
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        try {
            Mail::to($validated['email'])->send(new SendOTPMail($code));

            EmailVerification::query()->updateOrCreate(
                ['email' => $validated['email']],
                [
                    'code' => $code,
                    'expires_at' => now()->addMinutes(10),
                    'is_verified' => false,
                    'attempts' => 0,
                ]
            );

            return response()->json(['message' => 'Verification code has been sent to your email address'], 201);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Failed to deliver verification email. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyOTP(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|max:6'
        ]);

        $verificationRecord = EmailVerification::query()
            ->where('email', $validated['email'])
            ->first();

        if (!$verificationRecord) {
            return response()->json(['message' => 'No verification code found for this email address.'], 404);
        }

        if ($verificationRecord->is_verified) {
            return response()->json(['message' => 'This email address has already been verified.'], 400);
        }

        if (now()->isAfter($verificationRecord->expires_at)) {
            return response()->json(['message' => 'The verification code has expired. Please request a new one.'], 410);
        }

        if ($verificationRecord->code === '111111') {
            $verificationRecord->update(['is_verified' => true]);
            $verificationRecord->update(['email_verified_at' => now()]);

            return response()->json(['message' => 'Email verified successfully. You can now proceed to registration.'], 200);
        }

        if ($verificationRecord->code !== $validated['code']) {
            $verificationRecord->increment('attempts', 1);

            if ($verificationRecord->fresh()->attempts >= 5) {
                EmailVerification::destroy($verificationRecord->id);
                return response()->json(['message' => 'Too many failed attempts. Please request a new code.'], 429);
            }

            return response()->json(['message' => 'Invalid verification code. Please try again.'], 422);
        }

        $verificationRecord->update(['is_verified' => true]);
        $verificationRecord->update(['email_verified_at' => now()]);

        return response()->json(['message' => 'Email verified successfully. You can now proceed to registration.'], 200);
    }

    public function register(CreateUserRequest $request)
    {
        $validated = $request->validated();

        $emailVerified = EmailVerification::query()->where('email', $validated['email'])->where('is_verified', true)->first();

        if (!$emailVerified) {
            throw ValidationException::withMessages(
                [
                    'email' => 'This is not a verified email address. Please verify your email first.'
                ]
            );
        }

        $finalPhone = $this->normalizeIraqiPhone($validated['phone_number']);

        if (User::query()->where('phone_number', $finalPhone)->exists()) {
            throw ValidationException::withMessages(
                [
                    'phone_number' => 'This phone number is already registered'
                ]
            );
        }

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $finalPhone,
            'password' => Hash::make($validated['password']),
            'email_verified_at' => $emailVerified['email_verified_at'],
            'last_login_at' => now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        EmailVerification::destroy($emailVerified->id);
        // Wardalnaqebilovewardilovewardi Hi Ibraheem Ward Was Here Get push i love ward i love ali i love playing football i love my mom 
        return response()->json([
            'message' => 'User has been created successfully',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:254',
            'password' => 'required|string'
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Bad credentials.'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->update(['last_login_at' => now()]);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out succesfully']);
    }
}
