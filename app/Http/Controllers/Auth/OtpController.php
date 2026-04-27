<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class OtpController extends Controller
{
    public function index()
    {
        if (!session('otp_user_id')) {
            return redirect('/');
        }

        return inertia('Auth/OtpVerify', [
            'resend_available_at' => session('otp_resend_available_at')
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required'
        ]);

        $otp = UserOtp::where('token', session('otp_token'))
            ->where('otp_code', $request->otp)
            ->first();

        if (!$otp) {
            return back()->withErrors(['otp' => 'Invalid OTP']);
        }

        if (!$otp->isValid()) {
            return redirect('/')->withErrors(['otp' => 'OTP expired']);
        }

        $otp->update(['is_used' => true]);

        session([
            'reset_user_id' => $otp->user_id
        ]);

        return redirect('/reset-password');
    }

    // 🔥 RESEND OTP
    public function resend(Request $request)
    {
        $userId = session('otp_user_id');

        if (!$userId) {
            return redirect('/');
        }

        // 🔥 cooldown check
        $cooldown = session('otp_resend_available_at');

        if ($cooldown && now()->timestamp < $cooldown) {
            return back()->withErrors([
                'otp' => 'Please wait before requesting another OTP.'
            ]);
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect('/');
        }

        // invalidate old OTPs
        UserOtp::where('user_id', $userId)->update([
            'is_used' => true
        ]);

        // create new OTP
        $otpCode = rand(100000, 999999);
        $token = Str::uuid();

        UserOtp::create([
            'user_id' => $userId,
            'otp_code' => $otpCode,
            'token' => $token,
            'expires_at' => now()->addMinutes(15),
        ]);

        Mail::to($user->company_email)->send(new OtpMail($otpCode));

        session([
            'otp_token' => $token,
            'otp_resend_available_at' => now()->addMinutes(2)->timestamp
        ]);

        return back()->with('success', 'OTP resent successfully');
    }
}
