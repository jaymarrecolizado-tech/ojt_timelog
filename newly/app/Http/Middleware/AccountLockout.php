<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

class AccountLockout
{
    /**
     * Maximum number of failed attempts before lockout
     */
    private int $maxAttempts = 5;
    
    /**
     * Lockout duration in minutes
     */
    private int $lockoutDuration = 30;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to login attempts
        if ($request->is('login') && $request->isMethod('post')) {
            $email = $request->input('email');
            $lockoutKey = 'login_lockout:' . $email;
            $attemptsKey = 'login_attempts:' . $email;
            
            // Check if account is locked
            if (Cache::has($lockoutKey)) {
                $remainingTime = Cache::get($lockoutKey);
                return back()->withErrors([
                    'email' => "Account is temporarily locked. Please try again in {$remainingTime} minutes."
                ])->withInput($request->except('password'));
            }
            
            // Process the request
            $response = $next($request);
            
            // Check if login failed
            if ($response->isRedirect() && session()->has('errors')) {
                $attempts = Cache::get($attemptsKey, 0) + 1;
                Cache::put($attemptsKey, $attempts, now()->addHours(1));
                
                // Lock account if max attempts reached
                if ($attempts >= $this->maxAttempts) {
                    Cache::put($lockoutKey, $this->lockoutDuration, now()->addMinutes($this->lockoutDuration));
                    Cache::forget($attemptsKey);
                    
                    return back()->withErrors([
                        'email' => "Account has been locked for {$this->lockoutDuration} minutes due to multiple failed login attempts."
                    ])->withInput($request->except('password'));
                }
                
                $remainingAttempts = $this->maxAttempts - $attempts;
                return back()->withErrors([
                    'email' => "Invalid credentials. {$remainingAttempts} attempts remaining before lockout."
                ])->withInput($request->except('password'));
            }
            
            // Clear attempts on successful login
            if (auth()->check()) {
                Cache::forget($attemptsKey);
            }
            
            return $response;
        }
        
        return $next($request);
    }
}
