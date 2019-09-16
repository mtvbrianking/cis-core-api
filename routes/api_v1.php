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

// Route::pattern('token', $slug);

Route::group(['prefix' => 'tokens'], function () {
    Route::post('/', 'TokenController@issue');
    Route::post('/refresh', 'TokenController@refresh');
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
    Route::put('/{role}/permissions', 'RoleController@sync_permissions');
    Route::get('/{role}/permissions/granted', 'RoleController@permissions_granted');
    Route::post('/', 'RoleController@store');
    Route::put('/{role}', 'RoleController@update');
    Route::put('/{role}/revoke', 'RoleController@revoke');
    Route::put('/{role}/restore', 'RoleController@restore');
    Route::delete('/{role}', 'RoleController@destroy');
});
