<?php

namespace App\Http\Controllers;

use App\Mail\VerificationEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class VerificationController extends Controller
{
    public function showVerify()
    {
        $user = Auth::user();
        
        if ($user->email_verified) {
            if ($user->isGuard()) {
                return redirect()->route('guard.dashboard');
            } elseif ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('student.dashboard');
        }

        return view('auth.verify');
    }

    public function resend(Request $request)
    {
        $user = Auth::user();
        
        if ($user->email_verified) {
            if ($user->isGuard()) {
                return redirect()->route('guard.dashboard');
            } elseif ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('student.dashboard');
        }

        $token = Str::random(64);
        
        User::where('id', $user->id)->update([
            'email_verification_token' => hash('sha256', $token),
        ]);

        $verificationUrl = route('verification.verify', ['token' => $token]);
        $userName = $user->student ? $user->student->first_name : $user->email;

        Mail::to($user->email)->send(new VerificationEmail($verificationUrl, $userName));

        return back()->with('status', 'Verification link has been sent to your email.');
    }

    public function verify(Request $request, $token)
    {
        $user = User::where('email_verification_token', hash('sha256', $token))->first();

        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid or expired verification token']);
        }

        User::where('id', $user->id)->update([
            'email_verified' => true,
            'email_verified_at' => now(),
            'email_verification_token' => null,
        ]);

        Auth::login($user, true);

        if ($user->isGuard()) {
            return redirect()->route('guard.dashboard')->with('success', 'Email verified successfully!');
        } elseif ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')->with('success', 'Email verified successfully!');
        }

        return redirect()->route('student.dashboard')->with('success', 'Email verified successfully!');
    }
}
