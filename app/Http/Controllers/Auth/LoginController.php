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

    public function __construct()
    {
        $this->middleware('guest')->except('logout');

        $this->middleware(function ($request, $next) {
            $this->partialWebUser = Auth::guard('partial_web')->user();
            $this->webUser = Auth::guard('web')->user();

            return $next($request);
        });

    }

    public function logout()
    {
        Auth::logout();
        Auth::guard('partial_web')->logout();

        return redirect('/');
    }

    /*
     * Step 1: Redirect to VATSIM.NET SSO
     */
    public function loginWithVatsimSSO()
    {
        // Check we have necessary information
        if (! VATSIMSSO::isEnabled()) {
            return back()->with('error', 'VATSIM SSO Authentication is not currently available');
        }

        if ($this->partialWebUser) {

            // Check for secondary password
            if ($this->partialWebUser->hasPassword()) {
                return redirect()->route('login.secondary');
            } else {
                return $this->authDone($this->partialWebUser);
            }
        }

        $sso = new VATSIMSSO();

        return $sso->login(url('/login/sso/verify'), function ($key, $secret, $url) {
            Session::put('vatsimauth', compact('key', 'secret'));

            return redirect($url);
        }, function ($error) {
            throw new AuthenticationException('Could not authenticate with VATSIM SSO: ' . $error['message']);
        });
    }

    /*
     * Step 2: Verify SSO Login after redirect
     */
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

                Auth::guard('partial_web')->loginUsingId($vatsimUser->id, true);
                $this->partialWebUser = $user;


                if ($this->partialWebUser->hasPassword()) {
                    return redirect()->route('login.secondary');
                }

                return $this->authDone($this->partialWebUser);
            },
            function ($error) {
                throw new AuthenticationException($error['message']);
            }
        );
    }

    /*
     * Step (3): Show Secondary Sign In
     */

    public function showSecondarySignin()
    {
        if (! $this->partialWebUser->hasPassword()) {
            return $this->authDone($this->partialWebUser);
        }

        return view('auth.secondary')->with('user', $this->partialWebUser);
    }

    /*
     * Step (4): Fully Authenticate
     */

    public function verifySecondarySignin(Request $request)
    {
        if (! $this->partialWebUser->hasPassword()) {
            return $this->authDone($this->partialWebUser);
        }

        $this->validate($request, [
            'password' => 'required|string',
        ]);

        if (! Auth::attempt(['id' => $this->partialWebUser->id, 'password' => $request->input('password')])) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'password' => ['The supplied password did not match our records'],
            ]);
            throw $error;
        }

        return $this->authDone($this->partialWebUser);
    }

    public function authDone(User $user)
    {
        Auth::loginUsingId($user->id, true);
        return redirect()->intended();
    }
}
