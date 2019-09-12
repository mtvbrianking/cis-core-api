<?php

$slug = '/^[a-z\d\-]+$/';

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
    Route::get('/', 'ModuleController@index')->name('index');
    Route::get('/{module}', 'ModuleController@show')->name('show');
    Route::post('/', 'ModuleController@store')->name('store');
    Route::put('/{module}', 'ModuleController@update')->name('update');
    Route::put('/{module}/revoke', 'ModuleController@revoke')->name('revoke');
    Route::put('/{module}/restore', 'ModuleController@restore')->name('restore');
    Route::delete('/{module}', 'ModuleController@destroy')->name('destroy');
});
