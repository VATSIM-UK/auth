<?php

use App\Http\Controllers\Auth\LoginController;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Returns if the user is authenticated on any of the defined guards.
 *
 * @return bool
 */
function authenticatedOnAnyGuard(): bool
{
    $guards = array_keys(config('auth.guards'));
    foreach ($guards as $guard) {
        if (Auth::guard($guard)->check()) {
            return true;
        }
    }

    return false;
}

/**
 * Returns user logged in on the full guard.
 *
 * @return User|null
 */
function userOnFullGuard(): ?User
{
    return Auth::guard(LoginController::FULL_GUARD)->user();
}

/**
 * Returns user logged in on the SSO guard.
 *
 * @return User|null
 */
function userOnSSOGuard(): ?User
{
    return Auth::guard(LoginController::SSO_GUARD)->user();
}

/**
 * Returns if the user is authenticated on the full guard.
 *
 * @return bool
 */
function authenticatedOnFullGuard(): bool
{
    return Auth::guard(LoginController::FULL_GUARD)->check();
}

/**
 * Returns if the user is authenticated on the SSO guard.
 *
 * @return bool
 */
function authenticatedOnSSOGuard(): bool
{
    return Auth::guard(LoginController::SSO_GUARD)->check();
}

/**
 * @param $exception Throwable?
 * @param $message string?
 */
function reportException(?Throwable $exception, $message = null): void
{
    Log::error('Exception thrown, and handled.', [
        'message' => $message,
        'exception' => $exception,
    ]);

    //TODO: Install BugSnag and Implement Here
}
