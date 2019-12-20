<?php

namespace App\Http\Middleware;

use Closure;

class RequirePassword extends \Illuminate\Auth\Middleware\RequirePassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if (! $request->user()->hasPassword()) {
            return $next($request);
        }

        return parent::handle($request, $next, $redirectToRoute);
    }
}
