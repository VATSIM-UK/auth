<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Validation\UnauthorizedException;

class PasswordConfirmation extends RequirePassword
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \Throwable UnauthorizedException
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        throw_if(! auth()->user(), new UnauthorizedException());

        // If the user doesn't have a password, don't require confirmation
        if (! auth()->user()->hasPassword()) {
            return $next($request);
        }

        return parent::handle($request, $next, $redirectToRoute);
    }
}
