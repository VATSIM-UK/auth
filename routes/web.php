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
    Route::get('/login', 'LoginController@handleLogin')->name('login');
    Route::post('/login', 'LoginController@handleLogin');
    Route::get('/login/sso/verify', 'LoginController@verifySSOLogin')->name('login.sso.verify');

    Route::get('/logout', 'LoginController@logout')->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('/login/password/set', 'RequirePasswordChangeController@showSetSecondaryPassword')->name('login.password.set');
        Route::post('/login/password/set', 'RequirePasswordChangeController@setSecondaryPassword');
    });

    Route::middleware(['auth:partial_web', 'guest:web', 'auth.mandate.password'])->group(function () {
        Route::get('/login/password/forgot', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
        Route::post('/login/password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
        Route::get('/login/password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
        Route::post('/login/password/reset', 'ResetPasswordController@reset')->name('password.update');
    });

    Route::get('/action/confirm', 'ConfirmPasswordController@showConfirmForm')->name('password.confirm');
    Route::post('/action/confirm', 'ConfirmPasswordController@confirm')->name('password.confirm');
});

Route::get('/{any?}', 'SpaController@index')
    ->where('any', '.*')
    ->middleware('auth.check.password.expiry');
