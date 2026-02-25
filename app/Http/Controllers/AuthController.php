<?php

namespace App\Http\Controllers;

use App\Mail\VerificationEmail;
use App\Models\User;
use App\Models\Student;
use App\Constants\AppConstants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
            return back()->withErrors(['email' => 'Invalid email or password']);
        }

        if (!$user->is_active) {
            return back()->withErrors(['email' => 'Account is deactivated']);
        }

        Auth::login($user, true);

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isGuard()) {
            return redirect()->route('guard.dashboard');
        }

        return redirect()->route('student.dashboard');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'min:' . AppConstants::PASSWORD_MIN_LENGTH,
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
            'student_id_no' => 'required|unique:students,student_id_no',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'suffix' => 'nullable|string|max:10',
            'department' => 'required|string|max:150',
            'program' => 'required|string|max:150',
            'school_university' => 'required|string|max:200',
            'contact_no' => 'nullable|string|max:20',
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*#?&).',
        ]);

        $userId = Str::uuid();
        $token = Str::random(64);
        
        $user = User::create([
            'id' => $userId,
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role' => 'student',
            'is_active' => true,
            'email_verified' => false,
            'email_verification_token' => hash('sha256', $token),
        ]);

        Student::create([
            'id' => Str::uuid(),
            'user_id' => $userId,
            'student_id_no' => $validated['student_id_no'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'],
            'department' => $validated['department'],
            'program' => $validated['program'],
            'school_university' => $validated['school_university'],
            'contact_no' => $validated['contact_no'],
            'status' => 'active',
        ]);

        $verificationUrl = route('verification.verify', ['token' => $token]);
        Mail::to($user->email)->send(new VerificationEmail($verificationUrl, $validated['first_name']));

        Auth::login($user, true);

        return redirect()->route('verification.notice')->with('success', 'Registration successful! Please verify your email.');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
