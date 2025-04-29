<?php

use App\PurchaseOrder;
use Carbon\Carbon;
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

        Route::get('/check-token', 'AuthController@checkToken');

        Route::get('/getmydata', 'UserController@myData');

        #region Dashboard
        Route::get('/dashboard', 'DashboardController@index');
        Route::get('/dashboardV', 'DashboardController@indexV');
        #endregion
        Route::put('/admin/reset-password/{id}', 'AdminController@resetPassword');

        Route::group(['middleware' => ['checkPermission:user']], function () {
                #region Master User
                Route::get('/user', 'UserController@index');
                Route::get('/user/{id}', 'UserController@show');
                Route::post('/user', 'UserController@store');
                Route::post('/user/{id}', 'UserController@store');
                Route::delete('/user/{id}', 'UserController@destroy');
                Route::post('/userimport', 'UserController@import');
                #endregion
        });

        Route::get('/myprofile', 'UserController@myProfile');
        Route::get('/userlist', 'UserController@list');
        Route::get('/user/c/permissions', 'UserController@getMyPermissions');
        Route::post('/changepassword', 'UserController@changePassword');

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

        #region Master Storage Location
        Route::get('/storage-locations', 'StorageLocationController@index');
        Route::get('/storage-locations/{id}', 'StorageLocationController@show');
        Route::get('/storage-locations-list', 'StorageLocationController@list');
        Route::get('/storage-locations-sync', 'StorageLocationController@SyncSAP');
        Route::post('/storage-locations', 'StorageLocationController@store');
        Route::post('/storage-locations/{id}', 'StorageLocationController@store');
        Route::delete('/storage-locations/{id}', 'StorageLocationController@destroy');
        Route::post('/storage-locations-import', 'StorageLocationController@import');
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
        Route::put('/material/{id}', 'MaterialController@update');
        Route::delete('/material/{id}', 'MaterialController@destroy');
        Route::post('/materialimport', 'MaterialController@import');
        Route::get('/materialexport', 'MaterialController@export');
        #endregion

        #region Master Material Type
        Route::get('/materialtype', 'MaterialTypeController@index');
        Route::get('/materialtype/{id}', 'MaterialTypeController@show');
        // Route::get('/materialtypesync', 'MaterialTypeController@SyncSAP');
        Route::get('/materialtypelist', 'MaterialTypeController@list');
        Route::post('/materialtype', 'MaterialTypeController@store');
        Route::post('/materialtype/{id}', 'MaterialTypeController@store');
        Route::delete('/materialtype/{id}', 'MaterialTypeController@destroy');
        Route::post('/materialtypeimport', 'MaterialTypeController@import');
        Route::get('/materialtypeexport', 'MaterialTypeController@export');
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
        Route::get('/customer/g/customer-alias-list', 'CustomerController@getCustomerAliasList');

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
        Route::get('/line-number', 'LineNumberController@index');
        Route::get('/line-number/{id}', 'LineNumberController@show');
        Route::post('/line-number', 'LineNumberController@store');
        Route::get('/line-number-list', 'LineNumberController@list');
        Route::post('/line-number/{id}', 'LineNumberController@store');
        Route::delete('/line-number/{id}', 'LineNumberController@destroy');
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

        Route::get('/shipping-addresses', 'ShippingAddressController@index');

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
        Route::put('/permission/{id}', 'PermissionController@update');
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
        Route::get('/po-supplier', 'PurchaseOrderController@getBySupplierLoggedUser');
        Route::get('/purchase-orders/supplier/{supplier_code}', 'PurchaseOrderController@showByCodeSupplier');
        Route::get('/purchase-orders/{id}', 'PurchaseOrderController@show');
        Route::post('/purchase-orders', 'PurchaseOrderController@store');
        Route::put('/purchase-orders/{id}', '@update');
        Route::put('/purchase-orders/{id}/status', 'PurchaseOrderController@updateStatus');
        Route::delete('/purchase-orders/{id}', 'PurchaseOrderController@destroy');
        Route::get('/purchase-order-dashboard-data', 'PurchaseOrderController@getDashboardData');
        Route::get('/purchase-orders/d/{po_number}', 'PurchaseOrderController@showPO');
        Route::get('/purchase-orders/d/{po_number}/schedule-deliveries', 'PurchaseOrderController@showScheduleDeliveries');
        Route::post('/purchase-orders/c/{po_number}/upload-schedule-delivery', 'PurchaseOrderController@uploadScheduleDelivery');
        Route::post('/purchase-orders/g/{id}/schedule-deliveries-by-po', 'PurchaseOrderController@getScheduleDeliveriesByPoId');
        Route::post('/purchase-orders/c/{po_number}/generate-qr', 'PurchaseOrderController@generateAndStoreQRCode');
        Route::get('/purchase-orders/a/list', 'PurchaseOrderActivitiesController@getPOActivity');
        Route::get('/purchase-orders/a/{po_number}', 'PurchaseOrderActivitiesController@getActivityByPO');
        Route::post('/purchase-orders/mark-as-seen/{po_number}', 'PurchaseOrderController@markAsSeen');
        Route::post('/purchase-orders/mark-as-downloaded/{po_number}', 'PurchaseOrderController@markAsDownloaded');
        Route::get('/purchase-orders/g/list-need-schedule-delivery', 'PurchaseOrderController@listNeedScheduleDeliveries');
        Route::get('/purchase-orders/g/list-schedule-delivered', 'PurchaseOrderController@getListScheduleDelivered');
        Route::get('/purchase-orders/g/list-po-schedule-deliveries', 'PurchaseOrderController@getListPOScheduleDeliveries');
        Route::get('/purchase-orders/g/list-po-by-storage-location', 'PurchaseOrderController@getPOByStorageLocation');

        Route::post('/purchase-orders/a/sync-excel', 'PurchaseOrderController@SyncExcel');

        Route::get('/purchase-order/a/{approvalType}', 'PurchaseOrderController@listNeedSigned');
        Route::post('/purchase-order/s/knowed/{id}/confirm', 'PurchaseOrderController@signedAsKnowed');
        Route::post('/purchase-order/s/checked/{id}/confirm', 'PurchaseOrderController@signedAsChecked');
        Route::post('/purchase-order/s/approved/{id}/confirm', 'PurchaseOrderController@signedAsApproved');
        Route::post('/purchase-order/s/knowed/{id}/unconfirm', 'PurchaseOrderController@signedAsKnowedUnconfirmed');
        Route::post('/purchase-order/s/checked/{id}/unconfirm', 'PurchaseOrderController@signedAsCheckedUnconfirmed');
        Route::post('/purchase-order/s/approved/{id}/unconfirm', 'PurchaseOrderController@signedAsApprovedUnconfirmed');

        Route::post('/purchase-orders/{poId}/tracking-events', 'PurchaseOrderController@createTrackingEvent');
        Route::get('/purchase-orders/{poId}/tracking-events', 'PurchaseOrderController@getTrackingEvents');

        Route::get('/schedule-deliveries', 'ScheduleDeliveryController@index');
        Route::post('/schedule-deliveries/{id}', 'ScheduleDeliveryController@update');
        Route::get('/schedule-deliveries/download/{id}', 'ScheduleDeliveryController@downloadScheduleDelivery');
        Route::get('/schedule-deliveries/revision/download/{id}', 'ScheduleDeliveryController@downloadRevisionScheduleDelivery');
        Route::delete('/schedule-deliveries/{id}', 'ScheduleDeliveryController@destroy');

        Route::get('/purchase-order-analytics', 'PurchaseOrderAnalyticsController@index');
        Route::get('/poa/storage-location', 'PurchaseOrderAnalyticsController@getPurchaseOrderAnalyticsByStorageLocation');
        Route::get('/poa/storage-location/get-total-po', 'PurchaseOrderAnalyticsController@getTotalPurchaseOrderByStorageLocation');

        Route::get('/c/my-signer', 'PurchaseOrderSignerController@mySigner');
        Route::apiResource('/purchase-order-signers', 'PurchaseOrderSignerController');
        Route::get('/g/signer-by-type', 'PurchaseOrderSignerController@getByTypeName');
        // Email Area
        Route::get('/email-settings', 'EmailController@index');
        Route::post('/email-settings', 'EmailController@store');
        Route::get('/email-settings/g/templates', 'EmailController@showTemplate');
        Route::put('/email-settings/u/templates/{id}', 'EmailController@updateTemplate');
        Route::post('/{po_number}/send-email', 'EmailController@sendEmailPurchaseOrderConfirmation');
        Route::post('/send-test-email', 'EmailController@sendTestEmail');

        Route::group(['prefix' => 'travel-documents'], function () {
                Route::get('/list', 'TravelDocumentController@index');
                Route::get('/{id}', 'TravelDocumentController@show');
                Route::get('/g/scan', 'TravelDocumentController@scan');
                Route::get('/item/{id}', 'TravelDocumentController@showItem');
                // Route::post('/{id}', 'TravelDocumentController@update');
                Route::get('/delivery-orders/g/{no}', 'TravelDocumentController@getDeliveryOrders');
                Route::post('/by-po', 'TravelDocumentController@byPO');
                Route::post('/{poId}/get-print-items-label', 'TravelDocumentController@getPrintedItemsLabelsForSupplier');
                Route::post('/{poItemId}/get-print-label', 'TravelDocumentController@getPrintedLabels');
                Route::post('/{poItemId}/get-print-package-label', 'TravelDocumentController@getPrintedPackageLabels');
                Route::post('/{poId}/{poItemId}/print-label', 'TravelDocumentController@generateItemLabels');
                Route::post('/{poItemId}/print-label-temp', 'TravelDocumentController@tempPrintLabel');
                Route::post('/{poItemId}/print-package-label-temp', 'TravelDocumentController@tempPrintPackageLabel');
                Route::post('/{itemNumberId}/print-label-temp-by-id', 'TravelDocumentController@tempPrintLabelById');
                Route::post('/create/{poId}', 'TravelDocumentController@create');
                // Route::get('/{id}/download', 'TravelDocumentController@download');
                Route::post('/{id}/download', 'TravelDocumentController@downloadToPdf');
                Route::post('/{id}/print', 'TravelDocumentController@printTravelDocument');
                Route::get('/{id}/view-to-pdf', 'TravelDocumentController@viewToPdf');
                Route::get('/{id}/download-items-label', 'TravelDocumentController@downloadItemsLabel');
                Route::get('/{id}/print-items-label', 'TravelDocumentController@PrintItemsLabel');
                Route::get('/items/{itemId}/download-label', 'TravelDocumentController@downloadLabel');
                Route::get('/items/{itemId}/print-label', 'TravelDocumentController@printLabel');
                Route::post('/u/{TdId}/confirm', 'TravelDocumentController@confirmScan');

                Route::get('/g/supplier', 'TravelDocumentController@getBySupplierLoggedUser');
        });

        Route::prefix('delivery-orders')->group(function () {
                Route::get('/', 'DeliveryOrderController@index');
                Route::post('/', 'DeliveryOrderController@store');
                Route::put('/{id}', 'DeliveryOrderController@update');
                Route::delete('/{id}', 'DeliveryOrderController@destroy');
                Route::post('/a/import', 'DeliveryOrderController@import');
                Route::get('/a/export', 'DeliveryOrderController@export');
        });

        Route::group(['prefix' => 'schedule-delivery'], function () {
                Route::get('/', 'WhsScheduleDeliveryController@index');
                Route::post('/a/customer-delivery-cycle', 'WhsScheduleDeliveryController@createCustomerDeliveryCycle');
                Route::put('/u/customer-delivery-cycle/{id}', 'WhsScheduleDeliveryController@updateCustomerDeliveryCycle');
                Route::post('/a/customer-cycle', 'CustomerScheduleDeliveryListController@createCustomerCycle');
                Route::put('/u/customer-cycle/{id}', 'CustomerScheduleDeliveryListController@updateCustomeCycle');
                Route::post('/a/customer-pickup-time', 'CustomerScheduleDeliveryListController@createCustomerPickupTime');
                Route::put('/u/customer-pickup-time/{id}', 'CustomerScheduleDeliveryListController@updateCustomerPickupTime');
                Route::post('/a/import', 'CustomerScheduleDeliveryListController@importScheduleDeliveries');
                Route::post('/a/destroy/{id}', 'CustomerScheduleDeliveryListController@destroy');
                Route::post('/a/destroy-customer-schedule-delivery-cycle/{id}', 'WhsScheduleDeliveryController@destroyCustomerScheduleDeliveryCycle');
                Route::post('/a/destroy-customer-schedule-delivery/{id}', 'CustomerScheduleDeliveryListController@destroyCustomerScheduleDeliveryList');
                Route::post('/a/destroy-customer-pickup-time/{id}', 'CustomerScheduleDeliveryListController@destroyCustomerScheduleDeliveryPickupTime');
                Route::post('/a/destroy-customer-cycle/{id}', 'CustomerScheduleDeliveryListController@destroyCustomerScheduleDeliveryCycle');
                Route::post('/a/create-list', 'CustomerScheduleDeliveryListController@createList');

                Route::post('/a/destroy-selected', 'CustomerScheduleDeliveryListController@destroySelected');

                Route::post('/a/customer-import', 'CustomerScheduleDeliveryListController@importCustomer');
                Route::post('/a/customer-cycle-import', 'CustomerScheduleDeliveryListController@importCustomerCycle');
                Route::post('/a/customer-pickuptime-import', 'CustomerScheduleDeliveryListController@importCustomerPickupTime');
                Route::get('/g/customer-cycle-list', 'CustomerScheduleDeliveryListController@getCustomerCycleList');
                Route::get('/g/customer-pickuptime-list', 'CustomerScheduleDeliveryListController@getCustomerPickupTimeList');

                Route::post('/a/delete-selected', 'CustomerScheduleDeliveryListController@deleteSelected');
        });

        Route::group(['prefix' => 'whs-controls'], function () {
                Route::get('/', 'WhsMaterialControlController@index');
                Route::get('/g/seq/{seq}', 'WhsMaterialControlController@getSeqDetails');
                Route::post('/a/label-print-in', 'WhsMaterialControlController@inWhsMaterial');
                Route::delete('/{id}', 'WhsMaterialControlController@destroy');
                Route::post('/a/save-scan-whs', 'WhsMaterialControlController@saveScanWhs');
        });

        Route::group(['prefix' => 'stock-slocks'], function () {
                Route::post('/a/import', 'StockSlockController@import');
                Route::post('/a/take-out', 'StockSlockController@takeOut');
                Route::post('/a/put-in', 'StockSlockController@putIn');
                Route::post('/a/destroy/{id}', 'StockSlockController@destroy');

                Route::get('/g/history', 'StockSlockController@getHistory');
                Route::get('/a/print-activity', 'StockSlockController@printToPdf');
                Route::get('/a/print-activity-history', 'StockSlockController@printHistoryStockSloc');
        });

        Route::prefix('outgoing-goods')->group(function () {
                Route::get('/', 'OutgoingGoodController@index');
                Route::post('/', 'OutgoingGoodController@store');
                Route::get('/{id}', 'OutgoingGoodController@show');
                Route::post('/assign', 'OutgoingGoodController@assign');
                Route::put('/{id}/status', 'OutgoingGoodController@updateStatus');
                Route::get('/{id}/receipt', 'OutgoingGoodController@generateReceipt');
                Route::get('/g/templates', 'OutgoingGoodController@getTemplates');
                Route::post('/a/templates', 'OutgoingGoodController@storeOrUpdateTemplate');
                Route::delete('/a/templates/{id}', 'OutgoingGoodController@deleteTemplate');

                // Template Routes
        });

        Route::group(['prefix' => 'tracking-boxes'], function () {
                Route::post('/', 'TrackingBoxController@store');
                Route::get('/g/summary-period', 'TrackingBoxController@getSummaryByPeriod');
        });

        Route::group(['prefix' => 'rack'], function () {
                Route::delete('/{id}', 'RackController@destroy');
                Route::get('/g/qrcode/{slock}/print', 'RackController@printQrRackSloc');
        });

        Route::group(['prefix' => 'planning-productions'], function () {
                Route::get('/', 'PlanningProductionController@index');
                Route::post('/', 'PlanningProductionController@store');
                Route::put('/{id}', 'PlanningProductionController@update');
        });

        Route::group(['prefix' => 'order-customers'], function () {
                Route::get('/', 'OrderCustomerController@index');
                Route::get('/{id}', 'OrderCustomerController@show');
                Route::put('/{id}', 'OrderCustomerController@update');
                Route::post('/', 'OrderCustomerController@store');
                Route::post('/a/import', 'OrderCustomerController@import');
                Route::post('/a/export', 'OrderCustomerController@export');
                Route::delete('/{id}', 'OrderCustomerController@destroy');
        });

        Route::group(['prefix' => 'subconts'], function () {
                Route::get('/', 'SubContController@index');
                Route::get('/{id}', 'SubContController@show');
                Route::put('/{id}', 'SubContController@update');
                Route::post('/', 'SubContController@store');
                Route::post('/a/import', 'SubContController@import');
                Route::post('/a/export', 'SubContController@export');
                Route::delete('/{id}', 'SubContController@destroy');
        });

        Route::group(['prefix' => 'machines'], function () {
                Route::get('/', 'MachineController@index');
                Route::get('/{id}', 'MachineController@show');
                Route::put('/{id}', 'MachineController@update');
                Route::post('/', 'MachineController@store');
                Route::post('/a/import', 'MachineController@import');
                Route::post('/a/export', 'MachineController@export');
                Route::delete('/{id}', 'MachineController@destroy');
        });

        Route::group(['prefix' => 'departments'], function () {
                Route::get('/', 'DepartmentController@index');
                Route::get('/{id}', 'DepartmentController@show');
                Route::put('/{id}', 'DepartmentController@update');
                Route::post('/', 'DepartmentController@store');
                Route::post('/a/import', 'DepartmentController@import');
                Route::post('/a/export', 'DepartmentController@export');
                Route::delete('/{id}', 'DepartmentController@destroy');
        });

        Route::group(['prefix' => 'shifts'], function () {
                Route::get('/', 'ShiftController@index');
                Route::get('/{id}', 'ShiftController@show');
                Route::put('/{id}', 'ShiftController@update');
                Route::post('/', 'ShiftController@store');
                Route::post('/a/import', 'ShiftController@import');
                Route::post('/a/export', 'ShiftController@export');
                Route::delete('/{id}', 'ShiftController@destroy');
        });

        Route::group(['prefix' => 'parts'], function () {
                Route::get('/', 'PartController@index');
                Route::post('/', 'PartController@store');
                Route::get('/{code}', 'PartController@show');
                Route::put('/{id}', 'PartController@update');
                Route::put('/{id}/change-stock', 'PartController@updateStock');
                Route::delete('/{id}', 'PartController@destroy');
                Route::get('/g/list', 'PartController@getPartList');
                Route::post('/a/import', 'PartController@import');
                Route::post('/a/export', 'PartController@export');
                Route::post('/g/print-pdf', 'PartController@printPdf');

                // Route::post('/a/label-print-in', 'PartControlController@printPartIn');
                // Route::post('/a/label-print-out', 'PartController@printPartOut');
        });

        Route::group(['prefix' => 'mp-overtimes'], function () {
                // Route::get('/', 'OvertimeController@index');
                // Route::get('/{id}', 'OvertimeController@show');
                // Route::put('/{id}', 'OvertimeController@update');
                // Route::post('/', 'OvertimeController@store');
                // Route::post('/a/import', 'OvertimeController@import');
                // Route::post('/a/export', 'OvertimeController@export');
                Route::delete('/{id}', 'MpOvertimeController@destroy');
        });


        Route::group(['prefix' => 'part-controls'], function () {
                Route::get('/', 'PartControlController@index');
                Route::get('/g/seq/{seq}', 'PartControlController@getSeqDetails');
                Route::post('/a/label-print-in', 'PartControlController@inPart');
                Route::delete('/{id}', 'PartControlController@destroy');
                Route::post('/a/save-scan-part', 'PartControlController@saveScanPart');
                Route::get('/g/activity-parts', 'PartControlController@getActivityData');
                Route::get('/g/log', 'PartControlController@getPartStockLog');
                Route::post('/g/print-pdf', 'PartControlController@printPdf');
        });

        Route::group(['prefix' => 'part-mt-settings'], function () {
                Route::get('/', 'PartMonitoringSettingController@show');
                Route::post('/', 'PartMonitoringSettingController@update');
                Route::post('/test-notification', 'PartMonitoringSettingController@testNotification');
                Route::post('/send-notification', 'PartMonitoringSettingController@sendNotification');
        });
});

Route::group(['prefix' => 'rack'], function () {
        Route::get('/', 'RackController@index');
        Route::post('/', 'RackController@store');
        Route::post('/a/segment/create', 'RackController@createSegment');
        Route::post('/a/segment/delete/{id}', 'RackController@deleteSegment');
        Route::post('/a/segment/update/{id}', 'RackController@updateSegment');
        Route::get('/g/segment-list', 'RackController@getSegmentList');
        Route::get('/{id}/g/qrcode', 'RackController@generateQrCode');
        Route::get('/g/qr-code/{id}', 'RackController@showDataByQrCode');
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

Route::get('/g/mp-overtime/settings', 'MpOvertimeSettingController@show');
Route::put('/u/mp-overtime/settings', 'MpOvertimeSettingController@update');

Route::get('/d/{po_number}/view', 'PurchaseOrderController@showToSupplier');
Route::get('/d/{po_id}/download', 'PurchaseOrderController@downloadPDFForSupplier');
Route::get('/d/{po_id}/print-qr-label', 'PurchaseOrderController@printLabelQRForSupplier');
Route::get('/d/{po_id}/print-po-for-supplier', 'PurchaseOrderController@printPOForSupplier');
Route::post('/{po_number}/download', 'PurchaseOrderController@download');
Route::post('/{po_number}/download-pdf', 'PurchaseOrderController@downloadPDF');
Route::post('/{po_number}/print-po', 'PurchaseOrderController@printPO');
Route::post('/po/download-zip', 'PurchaseOrderController@downloadMultiplePDF');

Route::get('/storage-locations-list-g', 'StorageLocationController@list');
Route::get('/send-whatsapp', 'WhatsAppController@sendWhatsAppMessage');

Route::group(['prefix' => 'boxes'], function () {
        Route::get('/', 'BoxController@index');
        Route::get('/g/boxes-details', 'BoxController@getBoxDetails');
        Route::post('/', 'BoxController@store');
        Route::get('/show/{id}', 'BoxController@show');
        Route::get('/g/details', 'BoxController@showDetails');
        Route::put('/{id}', 'BoxController@update');
        Route::delete('/{id}', 'BoxController@destroy');
        Route::post('/a/import', 'BoxController@import');
        Route::get('/g/color-data', 'BoxController@getColorData');
        Route::get('/g/color-codes', 'BoxController@getColorCodes');
        Route::get('/g/type-codes', 'BoxController@getTypeBoxes');
        Route::get('/g/count-boxes-by-type', 'BoxController@countBoxesByType');
        Route::get('/g/timeline', 'BoxController@getTimelineBox');
        Route::get('/g/getAnalyticDataBox', 'BoxController@getAnalyticDataBox');
        Route::get('/g/getStatusChartData', 'BoxController@getStatusChartData');
});

Route::group(['prefix' => 'stock-slocks'], function () {
        Route::get('/', 'StockSlockController@index');
        Route::post('/', 'StockSlockController@store');
        Route::get('/show/{id}', 'StockSlockController@show');
        Route::put('/{id}', 'StockSlockController@update');
        // Route::post('/a/import', 'StockSlockController@import');
});

Route::group(['prefix' => 'tracking-boxes'], function () {
        Route::get('/', 'TrackingBoxController@index');
        Route::get('/show/{id}', 'TrackingBoxController@show');
        Route::delete('/{id}', 'TrackingBoxController@destroy');
        Route::post('/update/{id}', 'TrackingBoxController@update');
        Route::get('/box/status', 'TrackingBoxController@getBoxStatus');
        Route::delete('/{id}', 'TrackingBoxController@destroy');
        Route::get('/g/data-customer', 'TrackingBoxController@getDataOrderCustomer');
        Route::get('/g/data-customer-ahm', 'TrackingBoxController@getDataOrderCustomerAHM');
        Route::get('/g/data-dn-customer', 'TrackingBoxController@getDNCustomer');

        Route::get('/g/dn-customer', 'TrackingBoxController@showDN');
        Route::get('/g/history', 'TrackingBoxController@historyBox');
        Route::get('/g/current-delivery-boxes', 'TrackingBoxController@getCurrentDeliveryBoxes');
});

Route::get('/g/department-list', 'DepartmentController@list');
Route::get('/g/shift-list', 'ShiftController@list');

Route::get('/g/mp-overtimes', 'MpOvertimeController@index');
Route::post('/create-mp-overtime', 'MpOvertimeController@store');
Route::put('/update-mp-overtime/{id}', 'MpOvertimeController@update');
Route::get('/g/mp-overtimes/print', 'MpOvertimeController@printPdf');

Route::get('/customer-schedule-delivery-lists', 'CustomerScheduleDeliveryListController@index');
Route::get('/delivery-schedule', 'CustomerScheduleDeliveryListController@getDeliverySchedules');


Route::get('/dn/g/compare', 'CompareDeliveryNoteController@getCompareDN');
Route::get('/g/currently-box-status', 'CompareDeliveryNoteController@getCurrentlyTrackingBoxStatus');
Route::get('/kanbans/g/kanban-details', 'CompareDeliveryNoteController@getKanban');
// Route::get('/test-arduino', function () {
//         // return number
//         return response()->json(['data' => [
//                 'material_id' => "02C106SWH1RAW",
//                 'stock' => "123",
//                 'rack' => "A1-1-1",
//         ]]);
// });
