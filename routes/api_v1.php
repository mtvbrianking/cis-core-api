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

Route::pattern('module', $slug);

Route::group(['prefix' => 'modules', 'as' => 'modules.'], function () {
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
