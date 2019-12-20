<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;

class MustHavePassword
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
        $user = Auth::user() ?? Auth::guard('partial_web')->user();
        throw_if(!$user, new UnauthorizedException());

        if (!$user->hasPassword()) {
            return redirect('/');
        }

        return $next($request);
    }
}
