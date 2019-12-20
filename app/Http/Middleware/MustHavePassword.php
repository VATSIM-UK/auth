<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class MustHavePassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user() ? Auth::user() : Auth::guard('partial_web')->user();
        if (! $user->hasPassword()) {
            return redirect('/');
        }

        return $next($request);
    }
}
