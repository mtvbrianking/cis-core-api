<?php

$int = '^\d+$';
$slug = '^[a-z\d]+(?:-[a-z\d]+)*$';
$uuid = '^[a-fA-F\d]{8}-[a-fA-F\d]{4}-[a-fA-F\d]{4}-[a-fA-F\d]{4}-[a-fA-F\d]{12}$';

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => '\App\Http\Controllers\Oauth', 'prefix' => 'oauth', 'as' => 'oauth.'], function () {
    Route::post('/token', 'AccessTokenController@issueToken')->name('token');
});

/*
Route::group(['namespace' => '\Laravel\Passport\Http\Controllers', 'prefix' => 'oauth', 'as' => 'oauth.'], function () {
    Route::get('/authorize', 'AuthorizationController@authorize')->name('authorizations.authorize');
    Route::post('/authorize', 'ApproveAuthorizationController@approve')->name('authorizations.approve');
    Route::delete('/authorize', 'DenyAuthorizationController@deny')->name('authorizations.deny');

    Route::get('/clients', 'ClientController@forUser')->name('clients.index');
    Route::post('/clients', 'ClientController@store')->name('clients.store');
    Route::put('/clients/{client_id}', 'ClientController@update')->name('clients.update');
    Route::delete('/clients/{client_id}', 'ClientController@destroy')->name('clients.destroy');

    Route::get('/personal-access-tokens', 'PersonalAccessTokenController@forUser')->name('personal.tokens.index');
    Route::post('/personal-access-tokens', 'PersonalAccessTokenController@store')->name('personal.tokens.store');
    Route::delete('/personal-access-tokens/{token_id}', 'PersonalAccessTokenController@destroy')->name('personal.tokens.destroy');

    Route::get('/scopes', 'ScopeController@all')->name('scopes.index');

    Route::post('/token', 'AccessTokenController@issueToken')->name('token');

    Route::post('/token/refresh', 'TransientTokenController@refresh')->name('token.refresh');

    Route::get('/tokens', 'AuthorizedAccessTokenController@forUser')->name('tokens.index');
    Route::delete('/tokens/{token_id}', 'AuthorizedAccessTokenController@destroy')->name('tokens.destroy');
});
*/

Route::pattern('facility', $uuid);

Route::group(['prefix' => 'facilities'], function () {
    Route::get('/', 'FacilityController@index');
    Route::post('/', 'FacilityController@store');
    Route::get('/{facility}', 'FacilityController@show');
    Route::put('/{facility}', 'FacilityController@update');
    Route::delete('/{facility}', 'FacilityController@destroy');
    Route::put('/{facility}/modules', 'FacilityController@syncModules');
    Route::put('/{facility}/restore', 'FacilityController@restore');
    Route::put('/{facility}/revoke', 'FacilityController@revoke');
    Route::get('/{facility}/roles', 'FacilityController@roles');
    Route::get('/{facility}/users', 'FacilityController@users');
});

Route::pattern('module', $slug);

Route::group(['prefix' => 'modules'], function () {
    Route::get('/', 'ModuleController@index');
    Route::post('/', 'ModuleController@store');
    Route::get('/{module}', 'ModuleController@show');
    Route::put('/{module}', 'ModuleController@update');
    Route::delete('/{module}', 'ModuleController@destroy');
    Route::get('/{module}/permissions', 'ModuleController@permissions');
    Route::put('/{module}/restore', 'ModuleController@restore');
    Route::put('/{module}/revoke', 'ModuleController@revoke');
});

Route::pattern('permission', $int);

Route::group(['prefix' => 'permissions'], function () {
    Route::get('/', 'PermissionController@index');
    Route::post('/', 'PermissionController@store');
    Route::get('/{permission}', 'PermissionController@show');
    Route::put('/{permission}', 'PermissionController@update');
    Route::delete('/{permission}', 'PermissionController@destroy');
    // Route::get('/{permission}/module', 'PermissionController@module');
});

Route::pattern('role', $uuid);

Route::group(['prefix' => 'roles'], function () {
    Route::get('/', 'RoleController@index');
    Route::post('/', 'RoleController@store');
    Route::get('/{role}', 'RoleController@show');
    Route::put('/{role}', 'RoleController@update');
    Route::delete('/{role}', 'RoleController@destroy');
    Route::get('/{role}/permissions', 'RoleController@permissions');
    Route::put('/{role}/permissions', 'RoleController@syncPermissions');
    Route::get('/{role}/permissions/granted', 'RoleController@permissionsGranted');
    Route::put('/{role}/restore', 'RoleController@restore');
    Route::put('/{role}/revoke', 'RoleController@revoke');
    Route::get('/{role}/users', 'RoleController@users');
});

Route::pattern('user', $uuid);

Route::group(['prefix' => 'users'], function () {
    Route::get('/', 'UserController@index');
    Route::post('/', 'UserController@store');
    Route::post('/email', 'UserController@validateEmail')->middleware('client:validate-email');
    Route::put('/email', 'UserController@confirmEmailVerification')->middleware('client:confirm-email');
    Route::put('/password', 'UserController@updatePassword');
    Route::post('/password', 'UserController@confirmPassword');
    Route::put('/password/reset', 'UserController@resetPassword')->middleware('client:reset-password');
    Route::get('/{user}', 'UserController@show');
    Route::put('/{user}', 'UserController@update');
    Route::delete('/{user}', 'UserController@destroy');
    Route::put('/{user}/restore', 'UserController@restore');
    Route::put('/{user}/revoke', 'UserController@revoke');
});
