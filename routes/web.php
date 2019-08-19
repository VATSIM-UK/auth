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
