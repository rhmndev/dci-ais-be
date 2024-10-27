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

        Route::get('/getmydata', 'UserController@myData');

        #region Dashboard
        Route::get('/dashboard', 'DashboardController@index');
        Route::get('/dashboardV', 'DashboardController@indexV');
        #endregion

        #region Master User
        Route::get('/user', 'UserController@index');
        Route::get('/user/{id}', 'UserController@show');
        Route::post('/user', 'UserController@store');
        Route::get('/userlist', 'UserController@list');
        Route::post('/user/{id}', 'UserController@store');
        Route::delete('/user/{id}', 'UserController@destroy');
        Route::post('/userimport', 'UserController@import');
        Route::get('/user/c/permissions', 'UserController@getMyPermissions');
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

        #region Master Supplier
        Route::get('/supplier', 'SupplierController@index');
        Route::get('/supplier/{id}', 'SupplierController@show');
        Route::get('/supplier/g/{code}', 'SupplierController@showByCode');
        Route::get('/supplierlist', 'SupplierController@list');
        Route::get('/suppliersync', 'SupplierController@SyncSAP');
        Route::post('/supplier', 'SupplierController@store');
        Route::post('/supplier/{id}', 'SupplierController@store');
        Route::delete('/supplier/{id}', 'SupplierController@destroy');
        Route::post('/supplierimport', 'SupplierController@import');
        Route::get('/supplierexport', 'SupplierController@export');
        #endregion

        #region Master Material
        Route::get('/material', 'MaterialController@index');
        Route::get('/material/{id}', 'MaterialController@show');
        Route::get('/materialsync', 'MaterialController@SyncSAP');
        Route::get('/materiallist', 'MaterialController@list');
        Route::post('/material', 'MaterialController@store');
        Route::post('/material/{id}', 'MaterialController@store');
        Route::delete('/material/{id}', 'MaterialController@destroy');
        Route::post('/materialimport', 'MaterialController@import');
        Route::get('/materialexport', 'MaterialController@export');
        #endregion

        #region Master Customer
        Route::get('/customer', 'CustomerController@index');
        Route::get('/customer/{id}', 'CustomerController@show');
        Route::get('/customerlist', 'CustomerController@list');
        Route::get('/customersync', 'CustomerController@SyncSAP');
        Route::post('/customer', 'CustomerController@store');
        Route::post('/customer/{id}', 'CustomerController@store');
        Route::delete('/customer/{id}', 'CustomerController@destroy');
        Route::post('/customerimport', 'CustomerController@import');
        Route::get('/customer/{id}/parts/list', 'CustomerController@listParts');

        #region Master Parts Components Customer
        Route::get('/part-component', 'PartComponentController@index');
        Route::get('/part-component/{id}', 'PartComponentController@show');
        Route::get('/part-component/{id}/list', 'PartComponentController@list');
        Route::get('/part-component/list', 'PartComponentController@list');
        Route::get('/part-component-sync', 'PartComponentController@SyncSAP');
        Route::post('/part-component', 'PartComponentController@store');
        Route::post('/part-component/{id}', 'PartComponentController@store');
        Route::delete('/part-component/{id}', 'PartComponentController@destroy');
        Route::post('/part-component-import', 'PartComponentController@import');
        Route::get('/part-component-supplier/{id}', 'SupplierPartController@show');
        Route::get('/part-component-supplier/supplier/{supplier_id}', 'SupplierPartController@getBySupplier');
        Route::post('/part-component-supplier', 'SupplierPartController@store');
        #endregion

        #region Master Inspection
        // Route::get('/line-number', 'LineNumberController@index');
        // Route::get('/line-number/{id}', 'LineNumberController@show');
        // Route::post('/line-number', 'LineNumberController@store');
        Route::get('/line-number-list', 'LineNumberController@list');
        // Route::post('/line-number/{id}', 'LineNumberController@store');
        // Route::delete('/line-number/{id}', 'LineNumberController@destroy');
        #endregion Master Inspection

        #region Master Inspection
        Route::get('/inspection', 'InspectionController@index');
        Route::get('/inspection/{id}', 'InspectionController@show');
        Route::post('/inspection', 'InspectionController@store');
        Route::post('/inspection/{id}', 'InspectionController@store');
        Route::delete('/inspection/{id}', 'InspectionController@destroy');
        Route::get('/inspection-summary', 'InspectionController@getInspectionSummary');
        Route::get('/get-last-lot-number', 'InspectionController@getLastLOTNumber');
        #endregion Master Inspection

        #region Master News
        Route::get('/news', 'NewsController@index');
        Route::get('/news/{id}', 'NewsController@show');
        Route::post('/news', 'NewsController@store');
        Route::post('/news/{id}', 'NewsController@store');
        Route::delete('/news/{id}', 'NewsController@destroy');
        #endregion

        #region Transaction Receive Vendor
        Route::get('/receive', 'ReceivingController@index');
        Route::get('/receivesync', 'ReceivingController@SyncSAP');
        Route::post('/postGR', 'ReceivingController@postGR');
        #endregion

        #region Transaction Receive Vendor Details
        Route::get('/receiveDetail', 'ReceivingDetailsController@index');
        Route::get('/receiveDetail/{id}', 'ReceivingDetailsController@show');
        Route::get('/scanData', 'ReceivingDetailsController@scanData');

        Route::post('/receiveDetail', 'ReceivingDetailsController@update');
        #endregion

        #region Transaction Receive GR
        Route::get('/receiveGR', 'GoodReceivingController@index');
        Route::get('/receiveGRDetail/{id}', 'GoodReceivingController@show');

        Route::post('/receiveGR', 'GoodReceivingController@update');
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

        Route::get('/purchase-orders', 'PurchaseOrderController@index');
        Route::get('/purchase-orders/{id}', 'PurchaseOrderController@show');
        Route::post('/purchase-orders', 'PurchaseOrderController@store');
        Route::put('/purchase-orders/{id}', 'PurchaseOrderController@update');
        Route::delete('/purchase-orders/{id}', 'PurchaseOrderController@destroy');
        Route::get('/purchase-order-dashboard-data', 'PurchaseOrderController@getDashboardData');
        Route::get('/purchase-orders/d/{po_number}', 'PurchaseOrderController@showPO');
        Route::get('/purchase-orders/a/list', 'PurchaseOrderActivitiesController@getPOActivity');
        Route::get('/purchase-orders/a/{po_number}', 'PurchaseOrderActivitiesController@getActivityByPO');
        Route::post('/purchase-orders/mark-as-seen/{po_number}', 'PurchaseOrderController@markAsSeen');
        Route::post('/purchase-orders/mark-as-downloaded/{po_number}', 'PurchaseOrderController@markAsDownloaded');

        Route::get('/purchase-order/a/{approvalType}', 'PurchaseOrderController@listNeedSigned');
        Route::post('/purchase-order/s/knowed/{id}/confirm', 'PurchaseOrderController@signedAsKnowed');
        Route::post('/purchase-order/s/checked/{id}/confirm', 'PurchaseOrderController@signedAsChecked');
        Route::post('/purchase-order/s/approved/{id}/confirm', 'PurchaseOrderController@signedAsApproved');
        Route::post('/purchase-order/s/knowed/{id}/unconfirm', 'PurchaseOrderController@signedAsKnowedUnconfirmed');
        Route::post('/purchase-order/s/checked/{id}/unconfirm', 'PurchaseOrderController@signedAsCheckedUnconfirmed');
        Route::post('/purchase-order/s/approved/{id}/unconfirm', 'PurchaseOrderController@signedAsApprovedUnconfirmed');

        Route::get('/purchase-order-analytics', 'PurchaseOrderAnalyticsController@index');

        Route::get('/c/my-signer', 'PurchaseOrderSignerController@mySigner');
        Route::apiResource('/purchase-order-signers', 'PurchaseOrderSignerController');
        // Email Area
        Route::get('/email-settings', 'EmailController@index');
        Route::post('/email-settings', 'EmailController@store');
        Route::post('/email-settings/g/templates', 'EmailController@showTemplate');
        Route::post('/{po_number}/send-email', 'EmailController@sendEmailPurchaseOrderConfirmation');
        Route::post('/send-test-email', 'EmailController@sendTestEmail');
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

Route::get('/dataGRSAP', 'SAPController@getGR');
Route::post('/dataGRSAP', 'SAPController@storeGR');

Route::get('/part-component/list', 'PartComponentController@list');
Route::get('/status-part-component/list', 'StatusPartComponentController@list');

Route::get('/qrcode', 'InspectionController@qrcode');
Route::get('/qr-get-data', 'QrController@getData');
Route::get('/readqrcode', 'InspectionController@qrDecode');

Route::get('/d/{po_number}/view', 'PurchaseOrderController@showToSupplier');
Route::get('/d/{po_id}/download', 'PurchaseOrderController@downloadPDFForSupplier');
Route::get('/d/{po_id}/print-qr-label', 'PurchaseOrderController@printLabelQRForSupplier');
Route::post('/{po_number}/download', 'PurchaseOrderController@download');
Route::post('/{po_number}/download-pdf', 'PurchaseOrderController@downloadPDF');
Route::post('/po/download-zip', 'PurchaseOrderController@downloadMultiplePDF');
