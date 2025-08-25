<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Jobs\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\MailNotification;
use Laravel\Socialite\Facades\Socialite;

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

    public function googleLogin()
    {
        return Socialite::driver('google')->redirect();
    }

    public function googleCallback()
    {
        $googleUserData = Socialite::driver('google')->stateless()->user();

        //signup if new - signin if existing
        $google_id = $googleUserData->id;
        $name = $googleUserData->name;
        $email = $googleUserData->email;
        $email_verified = $googleUserData->user['email_verified'];

        try {
            $checkUserExist = User::where('google_id', $google_id)
                ->whereNotNull('email_verified_at')
                ->first();

            if ($checkUserExist) {
                //login
                Auth::login($checkUserExist, true);

                return redirect()->route('home')->with('success', 'Logged in successfully');
            } else {
                //signup & login
                $insertData = [
                    'name' => $name,
                    'email' => $email,
                    'role' => 'user',
                    'google_id' => $google_id
                ];

                //check verified email or not
                if ($email_verified === true) {
                    $insertData['email_verified_at'] = now();

                    //save & send welcome email

                    $insert = User::create($insertData);

                    Auth::login($insert, true);

                    WelcomeEmail::dispatch($insert);

                    return redirect()->route('home')->with('success', 'You have logged in successfully');
                } else {
                    $insert = User::create($insertData);

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
                }
            }
        } catch (\Exception $e) {
            if (isset($insert)) {
                $insert->delete();
            }
            // Store log of the exception
            Log::error('Error occurred: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have logged out successfully');
    }

}
