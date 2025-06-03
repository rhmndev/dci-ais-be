<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\News;
use App\User;
use App\Vendor;
use App\Material;
use App\Role;
use App\Receiving;

class DashboardController extends Controller
{
    //
    public function index()
    {
        #region News
        $resNews = News::orderBy('updated_at', 'desc')->get();

        $data['total_ann'] = count($resNews);
        $data['dataAnn'] = $resNews;
        #endregion
        
        #region Users
        $resUser = User::orderBy('updated_at', 'desc')->get();

        $data['total_users'] = count($resUser);
        $data['last_update_name_users'] = '-';
        $data['last_update_photo_users'] = '';
        $data['last_update_date_users'] = '-';
        if (count($resUser) > 0){
            $filterUser = User::where('username', $resUser[0]->updated_by)->first();

            if ($filterUser){
                $data['last_update_name_users'] = $filterUser->full_name;
                $data['last_update_photo_users'] = $filterUser->photo != null ? $filterUser->photo : '';
            } else {
                $data['last_update_name_users'] = 'System';
            }
            $data['last_update_date_users'] = $resUser[0]->updated_at;
        }
        #endregion

        #region Vendors
        $resVendor = Vendor::orderBy('updated_at', 'desc')->get();

        $data['total_vendors'] = count($resVendor);
        $data['last_update_name_vendors'] = '-';
        $data['last_update_photo_vendors'] = '';
        $data['last_update_date_vendors'] = '-';
        if (count($resVendor) > 0){
            $filterVendor = User::where('username', $resVendor[0]->updated_by)->first();

            if ($filterVendor){
                $data['last_update_name_vendors'] = $filterVendor->full_name;
                $data['last_update_photo_vendors'] = $filterVendor->photo != null ? $filterVendor->photo : '';
            } else {
                $data['last_update_name_vendors'] = 'System';
            }
            $data['last_update_date_vendors'] = $resVendor[0]->updated_at;
        }
        #endregion

        #region Meterials
        $resMaterial = Material::orderBy('updated_at', 'desc')->get();

        $data['total_materials'] = count($resMaterial);
        $data['last_update_name_materials'] = '-';
        $data['last_update_photo_materials'] = '';
        $data['last_update_date_materials'] = '-';
        if (count($resMaterial) > 0){
            $filterMaterial = User::where('username', $resMaterial[0]->updated_by)->first();

            if ($filterMaterial){
                $data['last_update_name_materials'] = $filterMaterial->full_name;
                $data['last_update_photo_materials'] = $filterMaterial->photo != null ? $filterMaterial->photo : '';
            } else {
                $data['last_update_name_materials'] = 'System';
            }
            $data['last_update_date_materials'] = $resMaterial[0]->updated_at;
        }
        #endregion

        #region Receivings
        $resReceiving = Receiving::orderBy('updated_at', 'desc')->get();

        $data['total_receiving'] = count($resReceiving);
        $data['last_update_name_receiving'] = '-';
        $data['last_update_photo_receiving'] = '';
        $data['last_update_date_receiving'] = '-';
        if (count($resReceiving) > 0){
            $filterReceiving = User::where('username', $resReceiving[0]->updated_by)->first();

            if ($filterReceiving){
                $data['last_update_name_receiving'] = $filterReceiving->full_name;
                $data['last_update_photo_receiving'] = $filterReceiving->photo != null ? $filterReceiving->photo : '';
            } else {
                $data['last_update_name_receiving'] = 'System';
            }
            $data['last_update_date_receiving'] = $resReceiving[0]->updated_at;
        }
        #endregion

        // get user logged
        $userLogged = auth()->user();
        $data['user_role'] = $userLogged->role_name;

        
        $data['user_role_id'] = $userLogged->role_id;
        
        $RoleData = Role::where('id', '!=', $userLogged->role_id)->first(); 
        // make shortcut for user have role 

        $data['shortcut'] = [];

        $roleWHS = ['Warehouse', 'WHS Admin', 'WHS Supply','Admin','WHS Supply Backup','PPIC MRP STAFF'];

        if(isset($RoleData)){
            if (in_array($RoleData->name, $roleWHS)){
                $data['shortcut'] = [
                    'menu' => [
                        'outgoing_goods' => [
                            'name' => 'Outgoing Good',
                            'url' => '/outgoing-goods',
                            'icon' => 'fa fa-truck',
                            'color' => 'primary',
                        ]
                    ]
                ];
            }
        }

        return response()->json([
            'type' => 'success',
            'data' =>  $data
        ]);
    }
    
    public function indexV()
    {
        $vendor = auth()->user()->vendor_code;

        #region Receivings
        $resReceiving = Receiving::where('vendor', $vendor)->orderBy('updated_at', 'desc')->get();

        $data['total_receiving'] = count($resReceiving);
        $data['last_update_name_receiving'] = '-';
        $data['last_update_photo_receiving'] = '';
        $data['last_update_date_receiving'] = '-';
        if (count($resReceiving) > 0){
            $filterReceiving = User::where('username', $resReceiving[0]->updated_by)->first();

            if ($filterReceiving){
                $data['last_update_name_receiving'] = $filterReceiving->full_name;
                $data['last_update_photo_receiving'] = $filterReceiving->photo != null ? $filterReceiving->photo : '';
            } else {
                $data['last_update_name_receiving'] = 'System';
            }
            $data['last_update_date_receiving'] = $resReceiving[0]->updated_at;
        }
        #endregion

        return response()->json([
            'type' => 'success',
            'data' =>  $data
        ]);
    }
}
