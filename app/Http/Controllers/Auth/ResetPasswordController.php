<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function index()
    {
        if (!session('reset_user_id')) {
            return redirect('/');
        }

        return inertia('Auth/ResetPassword');
    }

   public function reset(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed'
        ]);

        $user = User::find(session('reset_user_id'));

        if (!$user) {
            return redirect('/');
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        UserOtp::where('user_id', $user->id)->delete();

        session()->forget([
            'otp_user_id',
            'otp_token',
            'reset_user_id'
        ]);

        return inertia('Auth/ResetPassword', [
            'reset_success' => true
        ]);
    }
}
