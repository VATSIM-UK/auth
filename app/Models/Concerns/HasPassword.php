<?php

namespace App\Models\Concerns;

use App\Events\User\PasswordChanged;
use App\Events\User\PasswordRemoved;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Laravel\Passport\Token;

trait HasPassword
{
    /**
     * Verify if supplied password is correct for user.
     *
     * @param $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        if ($this->password === sha1(sha1($password))) {
            $this->password = $password;
            $this->save();
        }

        return Hash::check($password, $this->password);
    }

    /**
     * Set the password attribute correctly.
     *
     * Will hash the password, or set it as null if required.
     *
     * @param null|string $password The password value to set.
     */
    public function setPasswordAttribute($password): void
    {
        // if password is null, remove the current password
        // elseif password is already hashed, store it as provided
        // else password needs hashing, hash and store it
        if ($password === null) {
            $this->attributes['password'] = null;
        } elseif (! Hash::needsRehash($password)) {
            $this->attributes['password'] = $password;
        } else {
            $this->attributes['password'] = Hash::make($password);
        }
    }

    /**
     * Returns the Carbon instance for when the set password expires.
     *
     * @return Carbon|null
     */
    public function getPasswordExpiresAtAttribute(): ?Carbon
    {
        $rate = $this->roles()->forcesPassword()
            ->whereNotNull('password_refresh_rate')
            ->orderBy('password_refresh_rate', 'asc')
            ->pluck('password_refresh_rate')->first();

        if (! $rate || ! $this->password_set_at) {
            return null;
        }

        return $this->password_set_at->addDays($rate);
    }

    /**
     * Returns if the set password has expires, and needs to be reset
     *
     * @return bool
     */
    public function passwordHasExpired(): bool
    {
        return ($this->password && $this->password_expires_at) ? $this->password_expires_at->isPast() : false;
    }

    /**
     * Returns if secondary password policy is enforced for the user
     *
     * @return mixed
     */
    public function requiresPassword()
    {
        return $this->roles()->forcesPassword()->exists();
    }

    /**
     * Determine whether the current account has a password set.
     *
     * @return bool
     */
    public function hasPassword(): bool
    {
        return $this->password !== null;
    }

    /**
     * Set the user's password.
     *
     * @param string $password The password string.
     * @return bool
     */
    public function setPassword(string $password): bool
    {
        $save = $this->fill([
            'password' => $password,
            'password_set_at' => Carbon::now(),
        ])->save();

        // if the password is being reset by its owner...
        if ($save && Auth::check() && Auth::user()->id === $this->id) {
            Session::put([
                'password_hash' => Auth::user()->getAuthPassword(),
            ]);
        }

        // Invalidate tokens
        $this->tokens->each(function (Token $token) {
            $token->revoke();
        });
        Auth::logoutOtherDevices($password);

        event(new PasswordChanged($this));
        return $save;
    }

    /**
     * Remove a member's current password.
     *
     * @return bool
     */
    public function removePassword(): bool
    {
        $fill = $this->fill([
            'password' => null,
            'password_set_at' => null,
        ])->save();

        event(new PasswordRemoved($this));

        return $fill;
    }
}
