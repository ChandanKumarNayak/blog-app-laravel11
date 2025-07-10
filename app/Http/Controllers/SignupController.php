<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Notifications\MailNotification;

class SignupController extends Controller
{
    public function signup()
    {
        return view('auth.signup');
    }

    public function doRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|regex:/^[a-zA-Z\s]+$/|max:45',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed',
        ]);

        try {
            $insert = User::create($validated);

            // Generate signed email verification link (1 minute validity)
            $verificationLink = URL::temporarySignedRoute(
                'verify.email',
                now()->addMinutes(1),
                ['id' => $insert->id]
            );

            // Send email notification
            $insert->notify(new MailNotification($verificationLink));

            return redirect()->route('auth.login')
                ->with('success', 'You have registered successfully. Please check your email for verification.');

        } catch (\Exception $e) {

            if (isset($insert)) {
                $insert->delete();
            }

            return redirect()->back()->with('error', 'Registration failed. Please try again.');
        }
    }

    public function verifyEmail(Request $request, $id)
    {
        // Check if the link is valid and not expired
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired verification link.');
        }

        // Find the user
        $user = User::findOrFail($id);

        // Check if already verified
        if ($user->email_verified_at) {
            return redirect()->route('auth.login')->with('success', 'Email already verified.');
        }

        // Mark as verified
        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('auth.login')->with('success', 'You have successfully verified your email address.');
    }

}
