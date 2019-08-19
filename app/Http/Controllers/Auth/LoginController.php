<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Libraries\SSO\VATSIMSSO;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
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

    public function __construct()
    {
        $this->middleware('guest')->except('logout', 'loginSecondary');
    }

    /*
     * Step 1: Redirect to VATSIM.NET SSO
     */
    public function loginWithVatsimSSO()
    {
        $sso = new VATSIMSSO();
        return $sso->login(url('/login/sso/verify'), function ($key, $secret, $url) {
            Session::put('vatsimauth', compact('key', 'secret'));
            return redirect($url);
        }, function ($error) {
            throw new Exception('Could not authenticate: ' . $error['message']);
        });
    }

    /*
     * Step 2: Verify SSO Login after redirect
     */
    public function verifySSOLogin()
    {
        $sso = new VATSIMSSO();
        $session = Session::get('vatsimauth');

        return $sso->validate(
            $session['key'],
            $session['secret'],
            Input::get('oauth_verifier'),
            function ($vatsimUser) {

                $user = User::firstOrNew(['id' => $vatsimUser->id]);
                $user->name_first = utf8_decode($vatsimUser->name_first);
                $user->name_last = utf8_decode($vatsimUser->name_last);
                $user->email = $vatsimUser->email;
                $user->experience = $vatsimUser->experience;
                $user->joined_at = $vatsimUser->reg_date;
                $user->last_login = Carbon::now();
                $user->last_login_ip = Request::ip();
                $user->inactive = $vatsimUser->rating->id == -1 ? true : false;
                $user->save();

                if ($user->hasPassword()) {
                    Auth::guard('partial_web')->loginUsingId($vatsimUser->id);
                    return redirect()->route('login.secondary');
                }

                Auth::loginUsingId($vatsimUser->id);
                return redirect('/home');
            },
            function ($error) {
                throw new Exception($error['message']);
            }
        );
    }

    /*
     * Step (3): Show Secondary Sign In
     */

    public function showSecondarySignin()
    {
        $user = Auth::guard('partial_web')->user();

        if (!$user->hasPassword()) {
            return redirect('/');
        }

        return view('auth.secondary');
    }

    /*
     * Step (4): Fully Authenticate
     */

    public function verifySecondarySignin(\Illuminate\Http\Request $request)
    {
        $user = Auth::guard('partial_web')->user();

        if (!$user->hasPassword()) {
            return redirect('/');
        }

        $this->validate($request, [
            'password' => 'required|string',
        ]);

        if (!Auth::attempt(['id' => Auth::guard('partial_web')->user()->id, 'password' => $request->input('password')])) {
            return back()->with('error', 'Incorrect details provided');
        }
        Auth::guard('partial_web')->logout();
        return redirect('/home');
    }
}
