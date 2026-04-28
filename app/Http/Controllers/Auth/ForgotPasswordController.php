<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'username' => 'required|string|exists:users,username',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'username' => [__('We cannot find a user with that username.')],
            ]);
        }

        // Update password directly
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('login')->with('success', 
            app()->getLocale() === 'ar' ? 'تم تغيير كلمة المرور بنجاح.' : 'Password has been changed successfully.'
        );
    }
}
