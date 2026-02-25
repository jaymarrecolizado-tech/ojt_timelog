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
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $hasRole = false;

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($role === 'admin' && $user->isAdmin()) {
                $hasRole = true;
                break;
            }

            if ($role === 'super_admin' && $user->isSuperAdmin()) {
                $hasRole = true;
                break;
            }

            if ($role === 'student' && $user->isStudent()) {
                $hasRole = true;
                break;
            }

            if ($role === 'guard' && $user->isGuard()) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
