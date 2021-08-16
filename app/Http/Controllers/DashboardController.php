<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Vendor;
use App\Material;
use App\Receiving;

class DashboardController extends Controller
{
    //
    public function index()
    {
        $resUser = User::orderBy('updated_at', 'desc')->get();
        $filterUser = User::where('username', $resUser[0]->updated_by)->first();

        $resVendor = Vendor::orderBy('updated_at', 'desc')->get();
        $filterVendor = User::where('username', $resVendor[0]->updated_by)->first();

        $resMaterial = Material::orderBy('updated_at', 'desc')->get();
        $filterMaterial = User::where('username', $resMaterial[0]->updated_by)->first();
        
        #region Users
        $data['total_users'] = count($resUser);
        $data['last_update_name_users'] = 'System';
        $data['last_update_photo_users'] = '';
        if ($filterUser != null){
            $data['last_update_name_users'] = $filterUser->full_name;
            $data['last_update_photo_users'] = $filterUser->photo != null ? $filterUser->photo : '';
        }
        $data['last_update_date_users'] = $resUser[0]->updated_at;
        #endregion
        
        #region Vendors
        $data['total_vendors'] = count($resVendor);
        $data['last_update_name_vendors'] = 'System';
        $data['last_update_photo_vendors'] = '';
        if ($filterVendor != null){
            $data['last_update_name_vendors'] = $filterVendor->full_name;
            $data['last_update_photo_vendors'] = $filterVendor->photo != null ? $filterVendor->photo : '';
        }
        $data['last_update_date_vendors'] = $resVendor[0]->updated_at;
        #endregion
        
        #region Meterials
        $data['total_materials'] = count($resMaterial);
        $data['last_update_name_materials'] = 'System';
        $data['last_update_photo_materials'] = '';
        if ($filterMaterial != null){
            $data['last_update_name_materials'] = $filterMaterial->full_name;
            $data['last_update_photo_materials'] = $filterMaterial->photo != null ? $filterMaterial->photo : '';
        }
        $data['last_update_date_materials'] = $resMaterial[0]->updated_at;
        #endregion
        
        #region Receivings
        $data['total_receiving'] = count($resVendor);
        $data['last_update_name_receiving'] = 'System';
        $data['last_update_photo_receiving'] = '';
        if ($filterVendor != null){
            $data['last_update_name_receiving'] = $filterVendor->full_name;
            $data['last_update_photo_receiving'] = $filterVendor->photo != null ? $filterVendor->photo : '';
        }
        $data['last_update_date_receiving'] = $resVendor[0]->updated_at;
        #endregion

        return response()->json([
            'type' => 'success',
            'data' =>  $data
        ]);
    }
}
