<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends AppBaseController
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'login' => 'required|string',
                'password' => 'required|string',
            ]);

            $key = 'login_attempts:' . $request->ip();
            if (RateLimiter::tooManyAttempts($key, 5)) {
                return $this->sendError('Too many login attempts. Try again later.', 429);
            }

            $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            $user = User::where($field, $request->login)
                ->where('status', 'Active')
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                RateLimiter::hit($key, 60);
                return $this->sendError('These credentials do not match our records.', 401);
            }

            RateLimiter::clear($key);

            // JWT token create
            $token = JWTAuth::fromUser($user);

            return $this->sendResponse([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => $user
            ], 'Login successful.');

        } catch (Exception $e) {
            // Log the error
            Log::error('Error in get File: ', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError('Something went wrong!!!', 500);
        }
    }

    // OTP দিয়ে লগইন করলে (যদি OTP চালু থাকে)
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'otp' => 'required|digits:6',
        ]);

        $key = 'otp_verify_attempts:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return $this->sendError('অনেকবার ভুল OTP দিয়েছেন। একটু পরে চেষ্টা করুন।', 429);
        }

        $user = User::where('username', $request->username)
            ->where('otp', $request->otp)
            ->first();

        if (!$user) {
            RateLimiter::hit($key, 60);
            return $this->sendError('OTP ভুল হয়েছে।', 401);
        }

        // OTP ক্লিয়ার করো
        $user->update(['otp' => null]);
        RateLimiter::clear($key);

        // JWT টোকেন দাও
        $token = JWTAuth::fromUser($user);

        return $this->sendResponse([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ], 'OTP যাচাই সফল। লগইন হয়েছে।');
    }

    // লগআউট (JWT এর ক্ষেত্রে টোকেন ইনভ্যালিড করে দিতে হয়)
    public function logout(Request $request)
    {
        try {
            // Present token -> blacklist/Invalid
            JWTAuth::invalidate(JWTAuth::getToken());

            return $this->sendSuccess('Logout Successful.');
        } catch (Exception $e) {
            Log::error('Error logout: ' , [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError('Something went wrong!!!', 500);
        }
    }

    // অপশনাল: টোকেন রিফ্রেশ করার জন্য
    public function refresh()
    {
        return $this->sendResponse([
            'token' => JWTAuth::refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ], 'টোকেন রিফ্রেশ হয়েছে।');
    }
}
