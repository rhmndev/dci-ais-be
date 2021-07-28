<?php

use Illuminate\Http\Request;

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

Route::group(['middleware' => ['auth:api']], function () {

        #region Master User
        Route::get('/user', 'UserController@index');
        Route::get('/user/{id}', 'UserController@show');
	Route::post('/user', 'UserController@store');
	Route::post('/user/{id}', 'UserController@store');
        Route::delete('/user/{id}', 'UserController@destroy');
	Route::post('/userimport', 'UserController@import');
	#endregion

        #region Master Vendor
        Route::get('/vendor', 'VendorController@index');
        // Route::get('/user/{id}', 'UserController@show');
	// Route::post('/user', 'UserController@store');
	// Route::post('/user/{id}', 'UserController@store');
        // Route::delete('/user/{id}', 'UserController@destroy');
	// Route::post('/userimport', 'UserController@import');
	#endregion

	#region Template
        Route::get('/role', 'RoleController@index');
        Route::post('/role', 'RoleController@store');
        Route::get('/role/list', 'RoleController@list');
        Route::get('/role/{id}', 'RoleController@show');
        Route::post('/role/{id}', 'RoleController@update');
        Route::delete('/role/{id}', 'RoleController@destroy');

        Route::get('/permission', 'PermissionController@index');
        Route::post('/permission', 'PermissionController@store');
        Route::get('/permission/list-permission', 'PermissionController@get');
        Route::get('/permission/list', 'PermissionController@list');
        Route::get('/permission/list-parent', 'PermissionController@listParentId');
        Route::get('/permission/{id}', 'PermissionController@show');
        Route::post('/permission/{id}', 'PermissionController@update');
        Route::delete('/permission/{id}', 'PermissionController@destroy');

        Route::get('/settings', 'SettingsController@index');
        Route::post('/settings', 'SettingsController@store');
        Route::get('/settings/find', 'SettingsController@find');
        Route::get('/settings/{id}', 'SettingsController@show');
        Route::post('/settings/{id}', 'SettingsController@update');
        Route::delete('/settings/{id}', 'SettingsController@destroy');
	#endregion

});

Route::post('/login', 'AuthController@login');