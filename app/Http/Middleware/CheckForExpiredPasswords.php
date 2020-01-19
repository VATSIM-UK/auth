<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckForExpiredPasswords
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        if ((! $user->hasPassword() && $user->requiresPassword()) || $user->passwordHasExpired()) {
            return redirect()->route('login.set_password');
        }

        return $next($request);
    }
}
