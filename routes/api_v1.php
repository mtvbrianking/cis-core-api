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
    Route::get('/{facility}', 'FacilityController@show');
    Route::put('/{facility}/modules', 'FacilityController@syncModules');
    Route::get('/{facility}/roles', 'FacilityController@roles');
    Route::get('/{facility}/users', 'FacilityController@users');
    Route::post('/', 'FacilityController@store');
    Route::put('/{facility}', 'FacilityController@update');
    Route::put('/{facility}/revoke', 'FacilityController@revoke');
    Route::put('/{facility}/restore', 'FacilityController@restore');
    Route::delete('/{facility}', 'FacilityController@destroy');
});

Route::pattern('module', $slug);

Route::group(['prefix' => 'modules'], function () {
    Route::get('/', 'ModuleController@index');
    Route::get('/{module}', 'ModuleController@show');
    Route::get('/{module}/permissions', 'ModuleController@permissions');
    Route::post('/', 'ModuleController@store');
    Route::put('/{module}', 'ModuleController@update');
    Route::put('/{module}/revoke', 'ModuleController@revoke');
    Route::put('/{module}/restore', 'ModuleController@restore');
    Route::delete('/{module}', 'ModuleController@destroy');
});

Route::pattern('permission', $int);

Route::group(['prefix' => 'permissions'], function () {
    Route::get('/', 'PermissionController@index');
    Route::get('/{permission}', 'PermissionController@show');
    Route::get('/{permission}/module', 'PermissionController@module');
    Route::post('/', 'PermissionController@store');
    Route::put('/{permission}', 'PermissionController@update');
    Route::delete('/{permission}', 'PermissionController@destroy');
});

Route::pattern('role', $uuid);

Route::group(['prefix' => 'roles'], function () {
    Route::get('/', 'RoleController@index');
    Route::get('/{role}', 'RoleController@show');
    Route::get('/{role}/users', 'RoleController@users');
    Route::get('/{role}/permissions', 'RoleController@permissions');
    Route::put('/{role}/permissions', 'RoleController@syncPermissions');
    Route::get('/{role}/permissions/granted', 'RoleController@permissionsGranted');
    Route::post('/', 'RoleController@store');
    Route::put('/{role}', 'RoleController@update');
    Route::put('/{role}/revoke', 'RoleController@revoke');
    Route::put('/{role}/restore', 'RoleController@restore');
    Route::delete('/{role}', 'RoleController@destroy');
});

Route::pattern('user', $slug);

Route::group(['prefix' => 'users'], function () {
    Route::get('/', 'UserController@index');
    Route::get('/{user}', 'UserController@show');
    Route::post('/', 'UserController@store');
    Route::post('/email', 'UserController@validateEmail');
    Route::put('/{user}', 'UserController@update');
    Route::put('/{user}/password', 'UserController@updatePassword');
    Route::put('/password', 'UserController@resetPassword');
    Route::post('/{user}/password', 'UserController@confirmPassword');
    Route::put('/{user}/revoke', 'UserController@revoke');
    Route::put('/{user}/restore', 'UserController@restore');
    Route::delete('/{user}', 'UserController@destroy');
});
