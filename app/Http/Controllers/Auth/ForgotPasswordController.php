<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class ForgotPasswordController extends Controller
{
    public function index()
    {
        return inertia('Auth/ForgotPassword');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('company_email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email not found']);
        }

        // 🔥 check 2-minute cooldown
        $latestOtp = UserOtp::where('user_id', $user->id)
            ->latest()
            ->first();

        if ($latestOtp && $latestOtp->created_at->gt(now()->subMinutes(2)) && !$latestOtp->is_used) {
            return back()->withErrors([
                'email' => 'Please wait 2 minutes before requesting another OTP.'
            ]);
        }

        $otp = rand(100000, 999999);
        $token = Str::uuid();

        UserOtp::create([
            'user_id' => $user->id,
            'otp_code' => $otp,
            'token' => $token,
            'expires_at' => now()->addMinutes(15),
        ]);

        Mail::to($user->company_email)->send(new OtpMail($otp));

        session([
            'otp_user_id' => $user->id,
            'otp_token' => $token
        ]);

        return redirect('/otp-verify');
    }
}
