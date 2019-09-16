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

$uuid = '^([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}){1}$';

Route::group(['namespace' => 'Auth', 'prefix' => '', 'as' => ''], function () {

    // Registration Routes...
    Route::get('register', 'RegisterController@showRegistrationForm')->name('register');
    Route::post('register', 'RegisterController@register');

    // Authentication Routes...
    Route::get('login', 'LoginController@showLoginForm')->name('login');
    Route::post('login', 'LoginController@login');
    Route::post('logout', 'LoginController@logout')->name('logout');

    // Password Reset Routes...
    Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');

    Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('password/reset', 'ResetPasswordController@reset')->name('password.update');

    // Email Verification Routes...
    Route::get('email/verify', 'VerificationController@show')->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', 'VerificationController@verify')->name('verification.verify');
    Route::post('email/resend', 'VerificationController@resend')->name('verification.resend');
});

Route::get('/', 'HomeController@index')->name('home');

Route::get('/routes', 'HomeController@showApplicationRoutes')->name('routes');

Route::pattern('client', $uuid);

Route::group(['prefix' => '/clients', 'as' => 'clients.'], function () {
    // List, show, view all
    Route::get('/', 'AuthCodeClientController@index')->name('index');
    Route::get('/{client}', 'AuthCodeClientController@show')->name('show');
    // Add new
    Route::get('/create', 'AuthCodeClientController@create')->name('create');
    Route::post('/', 'AuthCodeClientController@store')->name('store');
    // Update, edit existing
    Route::get('/{client}/edit', 'AuthCodeClientController@edit')->name('edit');
    Route::put('/{client}', 'AuthCodeClientController@update')->name('update');
    // Delete, remove
    Route::put('/{client}/revoke', 'AuthCodeClientController@revoke')->name('revoke');
    Route::put('/{client}/restore', 'AuthCodeClientController@restore')->name('restore');
    Route::delete('/{client}', 'AuthCodeClientController@destroy')->name('destroy');

    Route::group(['prefix' => '/personal', 'as' => 'personal.'], function () {
        // List, show, view all
        Route::get('/', 'PersonalAccessClientController@index')->name('index');
        Route::get('/{client}', 'PersonalAccessClientController@show')->name('show');
        // Add new
        Route::get('/create', 'PersonalAccessClientController@create')->name('create');
        Route::post('/', 'PersonalAccessClientController@store')->name('store');
        // Update, edit existing
        Route::get('/{client}/edit', 'PersonalAccessClientController@edit')->name('edit');
        Route::put('/{client}', 'PersonalAccessClientController@update')->name('update');
        // Delete, remove
        Route::put('/{client}/revoke', 'PersonalAccessClientController@revoke')->name('revoke');
        Route::put('/{client}/restore', 'PersonalAccessClientController@restore')->name('restore');
        Route::delete('/{client}', 'PersonalAccessClientController@destroy')->name('destroy');
        // Generate token
        Route::post('/{client}/token', 'PersonalAccessClientController@token')->name('token');
    });
});
