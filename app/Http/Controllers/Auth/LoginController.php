<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Libraries\SSO\VATSIMSSO;
use App\User;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /*
     * Login Lifecycle:
     *  1) Anonymous user lands at /login
     *  2) User is redirected to VATSIM SSO system
     *  3) User returns from VATSIM SSO system, with authentication token, which is then validated
     *  4) If valid, user is checked to see if they require Secondary Authentication. If not, they are logged in.
     *  5) Secondary Authentication users are then logged in once completed successfully.
     */

    private $webUser;
    private $partialWebUser;

    const SSO_GUARD = 'partial_web';
    const FULL_GUARD = 'web';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');

        $this->middleware(function ($request, $next) {
            $this->partialWebUser = userOnSSOGuard();
            $this->webUser = userOnFullGuard();

            return $next($request);
        });
    }

    public function logout()
    {
        Auth::logout();
        Auth::guard(self::SSO_GUARD)->logout();

        return redirect('/');
    }

    /**
     * This function acts as an internal router for all login-related actions.
     *
     * @param Request $request
     * @return mixed
     */
    public function handleLogin(Request $request)
    {
        // Step 1: Check if user has logged in via SSO yet
        if (! $this->partialWebUser) {
            return $this->loginWithVatsimSSO();
        }

        // Step 2: User SSO authenticated. Now check for secondary authentication issues
        if (! $this->partialWebUser->hasPassword()) {
            return $this->authDone($this->partialWebUser);
        }

        // Step 3: User has secondary password. If it is a GET request, show the secondary login page
        if (! $request->has('password') && $request->isMethod('GET')) {
            return $this->showSecondarySignin();
        }

        // Step 4: Validate POST from Secondary Sign In
        return $this->verifySecondarySignin($request);
    }

    public function loginWithVatsimSSO()
    {
        // Check we have necessary information
        if (! VATSIMSSO::isEnabled()) {
            return back()->with('error', 'VATSIM SSO Authentication is not currently available');
        }

        $sso = new VATSIMSSO();

        return $sso->login(route('login.sso.verify'), function ($key, $secret, $url) {
            Session::put('vatsimauth', compact('key', 'secret'));

            return redirect($url);
        }, function ($error) {
            throw new AuthenticationException('Could not authenticate with VATSIM SSO: '.$error['message']);
        });
    }

    public function verifySSOLogin(Request $request)
    {
        $sso = new VATSIMSSO();
        $session = Session::get('vatsimauth');

        $this->validate($request, [
            'oauth_verifier' => 'required',
        ]);

        return $sso->validate(
            $session['key'],
            $session['secret'],
            $request->input('oauth_verifier'),
            function ($vatsimUser) {
                $user = User::firstOrNew(['id' => $vatsimUser->id]);
                $user->name_first = utf8_decode($vatsimUser->name_first);
                $user->name_last = utf8_decode($vatsimUser->name_last);
                $user->email = $vatsimUser->email;
                $user->joined_at = $vatsimUser->reg_date;
                $user->last_login = Carbon::now();
                $user->last_login_ip = RequestFacade::ip();
                $user->save();

                $user->syncRatings($vatsimUser->rating->id, $vatsimUser->pilot_rating->rating);

                Auth::guard(self::SSO_GUARD)->loginUsingId($vatsimUser->id, true);
                $this->partialWebUser = $user;

                return redirect()->route('login');
            },
            function ($error) {
                throw new AuthenticationException($error['message']);
            }
        );
    }

    public function showSecondarySignin()
    {
        return view('auth.secondary')->with('user', $this->partialWebUser);
    }

    public function verifySecondarySignin(Request $request)
    {
        $passwordFieldName = 'password';

        $this->validate($request, [
            $passwordFieldName => 'required|string',
        ]);

        if (! Auth::attempt(['id' => $this->partialWebUser->id, 'password' => $request->input($passwordFieldName)])) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                $passwordFieldName => ['The supplied password did not match our records'],
            ]);
            throw $error;
        }

        return $this->authDone($this->partialWebUser);
    }

    public function authDone(User $user)
    {
        Auth::guard(self::FULL_GUARD)->loginUsingId($user->getKey(), true);

        if ($user->needsToUpdatePassword()) {
            return redirect()->route('login.set_password');
        }

        return redirect()->intended();
    }
}
