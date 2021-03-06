<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\PasswordStrengthRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequirePasswordChangeController extends Controller
{
    public function showSetSecondaryPassword()
    {
        if (! Auth::user()->requiresPassword() && ! Auth::user()->passwordHasExpired()) {
            return redriect('/');
        }

        return view('auth.passwords.set');
    }

    public function setSecondaryPassword(Request $request)
    {
        $user = Auth::user();
        $this->validate($request, [
            'old_password' => [function ($attribute, $value, $fail) use ($user) {
                if ($user->hasPassword() && ! $user->verifyPassword($value)) {
                    $fail('Your old password was incorrect');
                }
            }],
            'password' => ['required', 'confirmed', new PasswordStrengthRule, function ($attribute, $value, $fail) use ($user) {
                if ($user->verifyPassword($value)) {
                    $fail('Your new password cannot be the same as the old password.');
                }
            }],
        ]);

        $user->setPassword($request->input('password'));

        return redirect()->intended();
    }
}
