<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check if user has the required role
        if ($role === 'admin' && !$user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        if ($role === 'super_admin' && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        if ($role === 'student' && !$user->isStudent()) {
            abort(403, 'Unauthorized access.');
        }

        if ($role === 'guard' && !$user->isGuard()) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
