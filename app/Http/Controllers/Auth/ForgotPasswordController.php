<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Models\Setting;
use App\Services\SmsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Controllers\AppBaseController;


class ForgotPasswordController extends AppBaseController
{
    /**
     * Send Reset Request (OTP if enabled, otherwise email)
     */
    public function sendResetRequest(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
        ]);

        // Find user by username or email
        $user = User::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if (!$user) {
            return $this->sendError('User not found.', 404);
        }

        if ($user->otp_enabled) {
            // OTP-based reset
            $otp = rand(100000, 999999);
            $user->update(['otp' => $otp]);

            // Assume sendOtp() method sends OTP to the user
            $user->sendOtp();

            return $this->sendSuccess('OTP sent to your phone number.');
        } else {
            // Email-based reset
            $status = Password::sendResetLink(['email' => $user->email]);

            return $status === Password::RESET_LINK_SENT
                ? $this->sendSuccess('Password reset link sent to your email.')
                : $this->sendError('Failed to send reset link.', 500);
        }
    }

    /**
     * Verify OTP for Password Reset (if OTP Enabled)
     */
    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('username', $request->username)
            ->where('otp', $request->otp)
            ->first();

        if (!$user) {
            return $this->sendError('Invalid OTP.', 401);
        }

        // Clear OTP after successful verification
        $user->update(['otp' => null]);

        return $this->sendSuccess('OTP verified. You can now reset your password.');
    }

    /**
     * Reset Password (OTP or Email)
     */
    public function resetPasswords(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('username', $request->username)
            ->first();

        if (!$user) {
            return $this->sendError('User not found.', 404);
        }

        // Update new password
        $user->update(['password' => Hash::make($request->password)]);

        return $this->sendSuccess('Password reset successfully.');
    }

    /**
     * Handle Password Reset via Email (Laravel's Built-in Flow)
     */
    public function resetPasswordWithToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->sendSuccess('Password reset successful.')
            : $this->sendError('Invalid token.', 400);
    }

    /**
     * Step 1: Request OTP
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // email or phone
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        /*
        $request->validate([
            'identifier' => 'required|string', // email or phone
        ]);
        */

        DB::beginTransaction();
        try {
            $user = User::where('email', $request->identifier)
                ->orWhere('phone', $request->identifier)
                ->first();

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'User not found.'], 404);
            }

            // ✅ Reset count if last request was before today
            if (!$user->otp_last_request_date || $user->otp_last_request_date->isBefore(now()->startOfDay())) {
                $user->otp_request_count = 0;
            }

            $setting = Setting::first();
            $otpRequestLimit = $setting && $setting->fpass_limit_per_day ? $setting->fpass_limit_per_day : 0;

            // ✅ Check daily limit
            if ($user->otp_request_count >= $otpRequestLimit) {
                return response()->json([
                    'status' => false,
                    'message' => "You have reached the maximum number of OTP requests($otpRequestLimit) for today. Try again tomorrow."
                ], 429);
            }

            // generate OTP
            $otp = rand(1000, 9999);
            $hashedOtp = Hash::make($otp);
            $expiresAt = Carbon::now()->addMinutes(10);

            $user->update([
                'otp' => $hashedOtp,
                'otp_expires_at' => $expiresAt,
                'otp_request_count' => $user->otp_request_count + 1,
                'otp_last_request_date' => now(),
            ]);

            // Send OTP via Email or SMS
            if (filter_var($request->identifier, FILTER_VALIDATE_EMAIL)) {
                /*
                Mail::raw("Your OTP code is: {$otp}", function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Password Reset OTP');
                });
                */
            } else {
                $sms = app(SmsService::class);
                $formattedPhone = formatBangladeshPhone($user->phone);
                # $response = $sms->send($formattedPhone, "Your OTP is $otp");
                $response = $sms->send($user->phone, "Your OTP is: $otp");
                if (!$response) {
                    Log::error('Failed to send OTP SMS', ['response' => $response]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'OTP sent successfully.',
                # 'expires_in' => '10 minutes ' . $otp,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            // Log the error
            Log::error('Error in updating Register: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!!!',
            ], 500);
        }
    }

    /**
     * Step 2: Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'otp' => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        // ✅ Verify hashed OTP
        if (!Hash::check($request->otp, $user->otp)) {
            return response()->json(['status' => false, 'message' => 'Invalid OTP.'], 400);
        }

        /*
        if (Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['status' => false, 'message' => 'OTP expired.'], 400);
        }
        */

        return response()->json([
            'status' => true,
            'message' => 'OTP verified successfully.',
            'otp' => $request->otp,
        ]);
    }

    /**
     * Step 3: Reset Password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'otp' => 'required|digits:4',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        if (!$user || !Hash::check($request->otp, $user->otp)) {
            return response()->json(['status' => false, 'message' => 'Invalid OTP.'], 400);
        }

        /*
        if (Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['status' => false, 'message' => 'OTP expired.'], 400);
        }
        */

        // Reset password
        $user->password = Hash::make($request->new_password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successful.',
        ]);
    }
}
