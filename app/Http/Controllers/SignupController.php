<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SignupController extends Controller
{
    public function signup() {
        return view('auth.signup');
    }

    public function doRegister(Request $request) {
        $validated = $request->validate([
            'name' => 'required|regex:/^[a-zA-Z\s]+$/|max:45',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed'
        ]);

        $insert = User::create($validated);

        if($insert) {
            return redirect()->route('auth.login')->with('success', 'You have registered successfully');
        } else {
            return redirect()->back()->with('error', 'Unable to register');
        }
    }
}
