<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{

    public function login()
    {
        return view('auth.login');
    }

    public function doLogin(Request $request)
    {
        // Step 1: Validate input
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Step 2: Check if user exists and is verified
        $user = User::where('email', $validated['email'])
            ->whereNotNull('email_verified_at')
            ->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Account does not exist or has not been verified.');
        }

        // Step 3: Check if password matches
        if (!Hash::check($validated['password'], $user->password)) {
            return redirect()->back()->with('error', 'Credentials do not match.');
        }

        // Step 4: Login the user
        Auth::login($user, true);

        return redirect()->route('home')->with('success', 'Logged in successfully!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have logged out successfully');
    }

}
