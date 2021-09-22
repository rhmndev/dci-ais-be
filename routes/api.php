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

        #region Dashboard
        Route::get('/dashboard', 'DashboardController@index');
        #endregion

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
        Route::get('/vendor/{id}', 'VendorController@show');
        Route::get('/vendorlist', 'VendorController@list');
        Route::get('/vendorsync', 'VendorController@SyncSAP');
        Route::post('/vendor', 'VendorController@store');
        Route::post('/vendor/{id}', 'VendorController@store');
        Route::delete('/vendor/{id}', 'VendorController@destroy');
        Route::post('/vendorimport', 'VendorController@import');
        #endregion

        #region Master Material
        Route::get('/material', 'MaterialController@index');
        Route::get('/material/{id}', 'MaterialController@show');
        Route::get('/materialsync', 'MaterialController@SyncSAP');
        Route::post('/material', 'MaterialController@store');
        Route::post('/material/{id}', 'MaterialController@store');
        Route::delete('/material/{id}', 'MaterialController@destroy');
        Route::post('/materialimport', 'MaterialController@import');
        #endregion

        #region Master News
        Route::get('/news', 'NewsController@index');
        Route::get('/news/{id}', 'NewsController@show');
        Route::post('/news', 'NewsController@store');
        Route::post('/news/{id}', 'NewsController@store');
        Route::delete('/news/{id}', 'NewsController@destroy');
        #endregion

        #region Transaction Receive
        Route::get('/receive', 'ReceivingController@index');
        Route::get('/receivesync', 'ReceivingController@SyncSAP');
        #endregion

        #region Transaction Receive Details
        Route::get('/receivedetail', 'ReceivingMaterialController@index');
        Route::get('/receivedetail/{id}', 'ReceivingMaterialController@show');
        Route::post('/receivedetail', 'ReceivingMaterialController@update');
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
Route::post('/resetpassword', 'AuthController@resetpassword');
Route::get('/resetpassword/{token}', 'AuthController@show');
Route::post('/resetpassword/{token}', 'AuthController@resetpassword');

Route::get('/dataVendorSAP', 'SAPController@getVendor');
Route::post('/dataVendorSAP', 'SAPController@storeVendor');

Route::get('/dataMaterialSAP', 'SAPController@getMaterial');
Route::post('/dataMaterialSAP', 'SAPController@storeMaterial');

Route::get('/dataPOSAP', 'SAPController@getPO');
Route::post('/dataPOSAP', 'SAPController@storePO');