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

    Route::middleware(['auth:partial_web', 'guest:web', 'has_password'])->group(function () {
        Route::get('/login/password/forgot', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
        Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
        Route::get('/login/password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
        Route::post('/login/password/reset', 'ResetPasswordController@reset')->name('password.update');
    });

    Route::get('/action/confirm', 'ConfirmPasswordController@showConfirmForm')->name('password.confirm');
    Route::post('/action/confirm', 'ConfirmPasswordController@confirm')->name('password.confirm');
});

Route::get('/{any?}', 'SpaController@index')->where('any', '.*');
