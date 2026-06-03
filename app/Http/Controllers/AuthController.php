<?php

namespace App\Http\Controllers;

use App\Mail\SendOTPMail;
use App\Models\EmailVerification;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function sendOTP(Request $request)
    {
        // Checkpoint 1: Request received
        Log::info('--- OTP SEND PROCESS START ---');
        Log::info('Incoming Request Email: ' . $request->input('email'));

        $validated = $request->validate([
            'email' => 'required|email'
        ]);

        if (User::query()->where('email', $validated['email'])->exists()) {
            Log::warning('OTP aborted: Email already registered: ' . $validated['email']);
            throw ValidationException::withMessages([
                'email' => 'This email address is already registered with an account.'
            ]);
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Log::info('Generated OTP Code: ' . $code);

        // Checkpoint 2: Inspecting active system configuration at runtime
        Log::info('Runtime MAIL_MAILER: ' . config('mail.default'));
        Log::info('Runtime MAIL_HOST: ' . config('mail.mailers.smtp.host'));
        Log::info('Runtime MAIL_FROM: ' . config('mail.from.address'));

        try {
            Log::info('Attempting network dispatch via Mail::to()...');
            
            Mail::to($validated['email'])->send(new SendOTPMail($code));
            
            Log::info('Mail::send completed successfully without crashing.');

            EmailVerification::query()->updateOrCreate(
                ['email' => $validated['email']],
                [
                    'code' => $code,
                    'expires_at' => now()->addMinutes(10),
                    'is_verified' => false,
                ]
            );
            
            Log::info('Database record updated/created successfully.');
            Log::info('--- OTP SEND PROCESS END (SUCCESS) ---');

            return response()->json(['message' => 'Verification code has been sent to your email address'], 201);
        } catch (Exception $e) {
            // Checkpoint 3: Catch any silent network/SMTP errors
            Log::error('--- OTP SEND PROCESS FAILED ---');
            Log::error('Error Message: ' . $e->getMessage());
            Log::error('Error Trace: ' . $e->getTraceAsString());

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


        if ($verificationRecord->code !== $validated['code']) {
            return response()->json(['message' => 'Invalid verification code. Please try again.'], 422);
        }


        $verificationRecord->update(['is_verified' => true]);

        return response()->json(['message' => 'Email verified successfully. You can now proceed to registration.'], 200);
    }
}
