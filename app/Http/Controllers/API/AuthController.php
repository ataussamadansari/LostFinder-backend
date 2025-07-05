<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Create or fetch user
        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            ['role' => null] // role null initially, to be set during profile completion
        );

        // Generate OTP (test purpose)
        $otp = rand(1000, 9999);
        $user->otp = $otp;
        $user->save();

        // TODO: Send OTP via SMS gateway (Twilio, Fast2SMS, etc.)
        Log::info("OTP for {$user->phone}: $otp");

        return response()->json([
            'status' => true,
            'OTP' => $otp,
            'message' => 'OTP sent successfully',
        ]);
    }

    // 🔹 Step 2: Verify OTP and Login
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|digits:10',
            'otp' => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)
            ->where('otp', $request->otp)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP or phone number.',
            ], 401);
        }

        // OTP verified - reset it
        $user->otp = null;
        $user->save();


        // ✅ Delete old tokens
        $user->tokens()->delete();

        // Create Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }
}
