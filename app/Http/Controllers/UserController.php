<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Imports\UsersImport;
use App\Mail\PasswordChangedNotification;
use App\User;
use App\Vendor;
use Carbon\Carbon;
use Image;
use Excel;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $skip = $request->perpage * ($request->page - 1);
        $withTrashed = $request->with_trashed == 'true' ? true : false;

        $query = User::query();
        
        if ($withTrashed) {
            $query->withTrashed();
        } 

        $users = $query->where(function ($where) use ($request) {
            if (!empty($request->keyword)) {
                foreach ($request->columns as $index => $column) {
                    if ($index == 0) {
                        $where->where($column, 'like', '%' . $request->keyword . '%');
                    } else {
                        $where->orWhere($column, 'like', '%' . $request->keyword . '%');
                    }
                }
            }
        })
            ->when(!empty($request->sort), function ($query) use ($request) {
                $query->orderBy($request->sort, $request->order == 'ascend' ? 'asc' : 'desc');
            })
            ->take((int)$request->perpage)
            ->skip((int)$skip)
            ->get();

        $totalQuery = User::query();
        
        if ($withTrashed) {
            $totalQuery->withTrashed();
        }

        $total = $totalQuery->where(function ($where) use ($request) {
            if (!empty($request->keyword)) {
                foreach ($request->columns as $index => $column) {
                    if ($index == 0) {
                        $where->where($column, 'like', '%' . $request->keyword . '%');
                    } else {
                        $where->orWhere($column, 'like', '%' . $request->keyword . '%');
                    }
                }
            }
        })->count();

        return response()->json([
            'type' => 'success',
            'data' => $users,
            'total' => $total
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->vendor_id = $user->vendor_code;

        return response()->json([
            'type' => 'success',
            'data' =>  $user
        ]);
    }

    public function myProfile(Request $request)
    {
        $user = User::findOrFail(auth()->user()->id);

        return response()->json([
            'type' => 'success',
            'data' =>  $user
        ]);
    }

    public function list(Request $request)
    {
        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $type = $request->type != null ? $request->type : '';
        $withTrashed = $request->with_trashed == 'true' ? true : false;
        $data = array();

        try {
            $User = new User;
            if (!empty($request->takeAll)) {
                $results = $User->getList($keyword, $type, true, $withTrashed);
            } else {
                $results = $User->getList($keyword, $type, false, $withTrashed);
            }

            return response()->json([
                'type' => 'success',
                'message' => 'Success.',
                'data' => $results,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([

                'type' => 'failed',
                'message' => 'Err: ' . $e . '.',
                'data' => NULL,

            ], 400);
        }
    }

    public function store(Request $request)
    {

        $request->validate([
            'username' => 'required|string',
            'full_name' => 'required|string',
            // 'phone_number' => 'nullable|string|min:9',
            // 'email' => 'nullable|email',
            'type' => 'required|numeric',
            'password' => $request->typePost == 1 ? 'string|confirmed|min:6' : 'required|string|confirmed|min:6',
            'role_id' => 'required|string',
            'vendor_id' => $request->type == 1 ? 'required|string' : '',
            'photo' => $request->photo != null && $request->hasFile('photo') ? 'sometimes|image|mimes:jpeg,jpg,png|max:2048' : '',
            'is_admin' => 'boolean',
        ]);

        try {

            $User = User::firstOrNew(['username' => $request->username]);

            $User->username = $request->username;
            $User->full_name = $request->full_name;
            $User->phone_number = $request->phone_number;

            if ($request->department != '') {

                $User->department = $request->department;
            } else {

                $User->department = null;
            }

            if ($request->npk != '') {

                $User->npk = $request->npk;
            } else {

                $User->npk = null;
            }

            $User->email = $request->email;
            $User->type = intval($request->type);

            if ($request->password != '') {

                $User->password = Hash::make($request->password);
            }

            $photo_url = asset('storage/images/users/' . $request->photo);

            if ($request->photo != null && $request->hasFile('photo')) {

                if (Storage::disk('public')->exists('/images/users/' . $User->photo)) {
                    Storage::disk('public')->delete('/images/users/' . $User->photo);
                }

                $image      = $request->file('photo');
                $fileName   = $User->username . '.' . $image->getClientOriginalExtension();

                $img = Image::make($image->getRealPath());
                $img->resize(120, 120, function ($constraint) {
                    $constraint->aspectRatio();
                });

                $img->stream(); // <-- Key point

                Storage::disk('public')->put('/images/users' . '/' . $fileName, $img, 'public');

                $User->photo = $fileName;

                $photo_url = asset('storage/images/users/' . $fileName);
            }

            if ($request->type == 1) {

                $User->vendor_code = $request->vendor_id;
                $User->vendor_name = $request->vendor_name;
            } else {

                $User->vendor_code = null;
                $User->vendor_name = null;
            }

            $User->role_id = $request->role_id;
            $User->role_name = $request->role_name;
            $User->is_admin = $request->is_admin ?? false;

            $User->created_by = auth()->user()->username;
            $User->updated_by = auth()->user()->username;

            $User->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Data saved successfully!',
                'photo' => $photo_url,
                'data' => NULL,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([

                'type' => 'failed',
                'message' => 'Err: ' . $e . '.',
                'data' => NULL,

            ], 400);
        }
    }

    public function import(Request $request)
    {
        $data = array();

        $request->validate([
            'role_id' => 'required|string|exists:roles,_id',
            'role_name' => 'required|string|exists:roles,name',
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {

            if ($files = $request->file('file')) {

                //store file into document folder
                $Excels = Excel::toArray(new UsersImport, $files);
                $Excels = $Excels[0];
                // $Excels = json_decode(json_encode($Excels[0]), true);

                foreach ($Excels as $Excel) {

                    if ($Excel['username'] != null) {

                        $vendor_code = null;
                        $vendor_name = null;

                        if ($Excel['type'] == 1) {

                            $vendor_code = $Excel['vendor_code'];
                            $Vendor = Vendor::where('code', $vendor_code)->first();

                            if ($Vendor) {

                                $vendor_name = $Vendor->name;
                            }
                        }

                        //store your file into database
                        $User = User::firstOrNew(['username' => $Excel['username']]);
                        $User->username = strval($Excel['username']);
                        $User->full_name = $Excel['full_name'];
                        $User->department = $Excel['department'];
                        $User->npk = $Excel['npk'];
                        $User->phone_number = $this->phoneNumber($Excel['phone_number']);

                        $User->email = $Excel['email'];
                        $User->password = Hash::make('dci12345');
                        $User->type = $Excel['type'];

                        $User->role_id = $request->role_id;
                        $User->role_name = $request->role_name;

                        if ($Excel['type'] == 1) {

                            $User->vendor_code = $vendor_code;
                            $User->vendor_name = $vendor_name;
                        } else {

                            $User->vendor_code = null;
                            $User->vendor_name = null;
                        }

                        $User->created_by = auth()->user()->username;
                        $User->created_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $User->updated_by = auth()->user()->username;
                        $User->updated_at = new \MongoDB\BSON\UTCDateTime(Carbon::now());
                        $User->save();
                    }
                }

                return response()->json([

                    "result" => true,
                    "msg_type" => 'Success',
                    "message" => 'Data stored successfully!',
                    // "message" => $Excels,

                ], 200);
            }
        } catch (\Exception $e) {

            return response()->json([

                "result" => false,
                "msg_type" => 'error',
                "message" => 'err: ' . $e,

            ], 400);
        }
    }

    public function destroy($id)
    {
        $User = User::find($id);

        if (Storage::disk('public')->exists('/images/users/' . $User->photo)) {
            Storage::disk('public')->delete('/images/users/' . $User->photo);
        }

        $User->delete();

        return response()->json([
            'type' => 'success',
            'message' => 'Data deleted successfully'
        ], 200);
    }

    private function IsNullOrEmptyString($str)
    {
        return (!isset($str) || trim($str) === '');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
        try {
            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Current password incorrect.'], 400);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            Mail::to($user->email)->send(new PasswordChangedNotification($user));

            return response()->json(['message' => 'Password changed successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error changing password: ' . $e->getMessage(),  // Or a more user-friendly message
            ], 500);  // 500 for server errors
        }
    }

    private function phoneNumber($number)
    {

        if (substr($number, 0, 1) == 0) {

            $number = '+62' . substr($number, 1);
        } else {

            $number = '+' . $number;
        }

        if (strpos($number, '-')) {
            $number = str_replace('-', '', $number);
        }

        if (strpos($number, ' ')) {
            $number = str_replace(' ', '', $number);
        }

        return $number;
    }

    public function myData(Request $request)
    {
        try {
            $user = auth()->user(); // Get the authenticated user

            if ($user) {
                return response()->json([
                    'type' => 'success',
                    'message' => 'User data retrieved successfully.',
                    'data' => $user,
                ], 200);
            } else {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'User not authenticated.',
                    'data' => null,
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Error retrieving user data: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getMyPermissions(Request $request)
    {
        try {
            $user = auth()->user(); // Get the authenticated user
            if ($user) {
                $permissions = $user->role->permissions->pluck('slug');

                return response()->json([
                    'type' => 'success',
                    'message' => 'User permissions retrieved successfully.',
                    'data' => $permissions,
                ], 200);
            } else {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'User not authenticated.',
                    'data' => null,
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Error retrieving user permissions: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
