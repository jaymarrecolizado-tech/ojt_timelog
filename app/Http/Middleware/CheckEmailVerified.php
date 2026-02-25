<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Skip verification check for admin and guard roles
        if ($user->isAdmin() || $user->isGuard()) {
            return $next($request);
        }
        
        // Check if email is verified
        if (!$user->email_verified) {
            return redirect()->route('verification.notice');
        }
        
        return $next($request);
    }
}
