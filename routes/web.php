<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\QRController;
use App\Http\Controllers\VerificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Apply security headers to all routes
    Route::middleware(['security', 'xss'])->group(function () {

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isGuard()) {
            return redirect()->route('guard.dashboard');
        }
        if ($user->isStudent()) {
            return redirect()->route('student.dashboard');
        }
    }
    return redirect()->route('login');
});

// Home route - redirects based on role
Route::get('/home', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->isGuard()) {
            return redirect()->route('guard.dashboard');
        }
        if ($user->isStudent()) {
            return redirect()->route('student.dashboard');
        }
    }
    return redirect()->route('login');
})->name('home');

// Authentication Routes with Rate Limiting and Account Lockout
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware(['throttle:5,1', 'lockout']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,60');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Email Verification Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', [VerificationController::class, 'showVerify'])->name('verification.notice');
    Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
    Route::get('/email/verify/{token}', [VerificationController::class, 'verify'])->name('verification.verify');
});

// Student Routes
Route::middleware(['auth', 'xss', 'email.verified'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/logs', [StudentController::class, 'logs'])->name('logs');
    Route::get('/scan', [StudentController::class, 'scan'])->name('scan');
    Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
});

// Admin Routes (accessible by admin and guard)
Route::middleware(['auth', 'role:admin,guard'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Students
    Route::get('/students', [AdminController::class, 'students'])->name('students');
    Route::post('/students', [AdminController::class, 'createStudent'])->name('students.create');
    Route::get('/students/{id}', [AdminController::class, 'studentDetail'])->name('students.detail');
    Route::put('/students/{id}', [AdminController::class, 'updateStudent'])->name('students.update');
    
    // Manual Logs
    Route::post('/students/{id}/logs', [AdminController::class, 'addManualLog'])->name('students.logs.create');
    
    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    
    // Settings (Super Admin only)
    Route::middleware(['role:super_admin'])->group(function () {
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::get('/locations', [AdminController::class, 'locations'])->name('locations');
        Route::post('/locations', [AdminController::class, 'createLocation'])->name('locations.create');
    });
});

// Guard Routes
Route::middleware(['auth', 'role:guard'])->prefix('guard')->name('guard.')->group(function () {
    Route::get('/dashboard', [QRController::class, 'guardQR'])->name('dashboard');
    Route::get('/qr', [QRController::class, 'guardQR'])->name('qr');
    Route::get('/qr/refresh', [QRController::class, 'guardRefresh'])->name('qr.refresh');
});

// QR API Routes
Route::post('/api/qr/validate', [QRController::class, 'validate'])->name('qr.validate');
Route::get('/api/qr/generate', [QRController::class, 'generate'])->name('qr.generate');

}); // End of security middleware group
