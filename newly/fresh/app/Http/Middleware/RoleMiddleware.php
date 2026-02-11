<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        switch ($role) {
            case 'admin':
                if (!$user->isAdmin() && !$user->isSuperAdmin()) {
                    abort(403, 'Unauthorized access.');
                }
                break;
            case 'super_admin':
                if (!$user->isSuperAdmin()) {
                    abort(403, 'Super admin access required.');
                }
                break;
            case 'student':
                if (!$user->isStudent()) {
                    abort(403, 'Student access required.');
                }
                break;
        }

        return $next($request);
    }
}
