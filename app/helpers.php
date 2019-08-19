<?php

function authenticatedOnAnyGuard()
{
    $guards = array_keys(config('auth.guards'));
    foreach ($guards as $guard) {
        if(Auth::guard($guard)->check()) return true;
    }
    return false;
}
