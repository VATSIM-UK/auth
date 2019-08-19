<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Http\Request;

Route::get('/', function () {
    return 'Hello World';
});

Route::get('/login', 'Auth\LoginController@loginWithVatsimSSO')->name('login');
Route::get('/login/sso/verify', 'Auth\LoginController@verifySSOLogin')->name('login.sso.verify');
Route::get('/login/secondary', 'Auth\LoginController@showSecondarySignin')->name('login.secondary');
Route::post('/login/secondary', 'Auth\LoginController@verifySecondarySignin');


Route::get('/logout', 'Auth\LoginController@logout')->name('logout');


Route::get('/home', 'HomeController@index')->name('home');


Route::get('/core/login', function (Request $request) {

    $request->session()->put('state', $state = Str::random(40));

    $query = http_build_query([
        'client_id' => 3,
        'redirect_uri' => 'http://auth.vatsim.test/core/callback',
        'response_type' => 'code',
        'scope' => '',
        'state' => $state,
    ]);

    return redirect('http://auth.vatsim.test/oauth/authorize?'.$query);
});

Route::get('/core/callback', function (Request $request) {
    $state = $request->session()->pull('state');

    throw_unless(
        strlen($state) > 0 && $state === $request->state,
        InvalidArgumentException::class
    );

    $http = new GuzzleHttp\Client;

    $response = $http->post('http://auth.vatsim.test/oauth/token', [
        'form_params' => [
            'grant_type' => 'authorization_code',
            'client_id' => 3,
            'client_secret' => 'spiFOslPJChjgaUEw3EkmL4dOYPqqE0DJsxO32bR',
            'redirect_uri' => 'http://auth.vatsim.test/core/callback',
            'code' => $request->code,
        ],
    ]);

    return json_decode((string) $response->getBody(), true);
});
