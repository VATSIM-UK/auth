<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as Auth;
use Laravel\Passport\Exceptions\MissingScopeException;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

/*
 * Note: This middleware is a merge of the \Illuminate\Auth\Middleware\Authenticate and Laravel\Passport\Http\Middleware\CheckClientCredentials middlewares
 */

class ApiClientOrUserAuthentication
{
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;
    /**
     * The Resource Server instance.
     *
     * @var \League\OAuth2\Server\ResourceServer
     */
    protected $server;

    private $exceptions = [];

    /**
     * Create a new middleware instance.
     *
     * @param \Illuminate\Contracts\Auth\Factory $auth
     * @param ResourceServer $server
     */
    public function __construct(Auth $auth, ResourceServer $server)
    {
        $this->auth = $auth;
        $this->server = $server;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next)
    {
        // Check if basic authentication is met
        if($this->authenticate($request, ["api"]) !== false){
            return $next($request);
        }

        $psr = (new DiactorosFactory)->createRequest($request);

        try {
            $psr = $this->server->validateAuthenticatedRequest($psr);
        } catch (OAuthServerException $e) {
            throw new AuthenticationException;
        }

        return $next($request);
    }

    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        $this->exceptions[] = new AuthenticationException(
            'Unauthenticated.', $guards
        );
        return false;
    }
}
