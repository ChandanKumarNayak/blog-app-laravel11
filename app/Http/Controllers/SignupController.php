<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Jobs\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            'role' => 'required|in:admin,user,editor',
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

            // Send account verification email
            $insert->notify(new MailNotification($verificationLink));

            return redirect()->route('login')
                ->with('success', 'You have registered successfully. Please check your email for verification.');

        } catch (\Exception $e) {
            if (isset($insert)) {
                $insert->delete();
            }
            //store log
            // Store log of the exception
            Log::error('Error occurred: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);

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
            return redirect()->route('login')->with('success', 'Email already verified.');
        }

        // Mark as verified
        $user->email_verified_at = now();
        $user->save();

        //send welcome email
        WelcomeEmail::dispatch($user);

        return redirect()->route('login')->with('success', 'You have successfully verified your email address.');
    }

}
