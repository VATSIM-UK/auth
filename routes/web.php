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


Route::namespace('Auth')->group(function () {
    Route::get('/login', 'LoginController@loginWithVatsimSSO')->name('login');
    Route::get('/login/sso/verify', 'LoginController@verifySSOLogin')->name('login.sso.verify');
    Route::get('/login/secondary', 'LoginController@showSecondarySignin')->name('login.secondary');
    Route::post('/login/secondary', 'LoginController@verifySecondarySignin');

    Route::get('/logout', 'LoginController@logout')->name('logout');


    Route::get('/login/password/forgot', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::get('/login/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('/login/password/reset', 'Auth\ResetPasswordController@reset');

});


Route::get('/{any?}', 'SpaController@index')->where('any', '.*');
