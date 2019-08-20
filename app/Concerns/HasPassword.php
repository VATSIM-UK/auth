<?php


namespace App\Concerns;

use Auth;
use Carbon\Carbon;
use Hash;
use Session;

trait HasPassword
{
    /**
     * Verify if supplied password is correct for user
     *
     * @param $password
     * @return bool
     */
    public function verifyPassword($password)
    {
        if ($this->password == sha1(sha1($password))) {
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
    public function setPasswordAttribute($password)
    {
        // if password is null, remove the current password
        // elseif password is already hashed, store it as provided
        // else password needs hashing, hash and store it
        if ($password === null) {
            $this->attributes['password'] = null;
        } elseif (!Hash::needsRehash($password)) {
            $this->attributes['password'] = $password;
        } else {
            $this->attributes['password'] = Hash::make($password);
        }
    }

    /**
     * Determine whether the current account has a password set.
     *
     * @return bool
     */
    public function hasPassword()
    {
        return $this->password !== null;
    }

    /**
     * Set the user's password.
     *
     * @param string $password The password string.
     * @return bool
     */
    public function setPassword($password)
    {
        //TODO: Implement expiry

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

        return $save;
    }

    /**
     * Remove a member's current password.
     *
     * @return bool
     */
    public function removePassword()
    {
        return $this->fill([
            'password' => null,
            'password_set_at' => null,
            'password_expires_at' => null,
        ])->save();
    }
}
