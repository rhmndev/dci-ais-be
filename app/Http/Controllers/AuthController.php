<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Permission;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|exists:users',
            'password' => 'required|min:6'
        ]);
        
        $token = Str::random(25);
        $user = User::where('username', $request->username)->first();
        
        if (Hash::check($request->password, $user->password)) {
            
            $user->forceFill([
                'api_token' => hash('sha256', $token)
            ])->save();


            $permissions = Permission::whereNull('parent_id')->orderBy('order_number')->get();
            $permission_allowed = $permissions->map(function($permission) use ($user){

                $permission_allowed = collect($user->role->permissions)->where('allow', true);

                if ($permission_allowed->pluck('permission_id')->contains($permission->id)) {

                    return [
                        '_id' => $permission->id,
                        'name' => $permission->name,
                        'url' => $permission->url,
                        'icon' => $permission->icon,
                        'children' => $permission->children->map(function($child) use ($user){
                            $permission_allowed = collect($user->role->permissions)->where('allow', true);
                            if ($permission_allowed->pluck('permission_id')->contains($child->id)) {
                                return [
                                    '_id' => $child->id,
                                    'name' => $child->name,
                                    'url' => $child->url
                                ];
                            }
                        })
                    ];
                }
            });

            $user->photo_url = asset('storage/images/users/'.$user->photo);
            
            return response()->json([
                'type' => 'success',
                'message' => 'Login successfully!',
                'token' => $token,
                'data' => $user,
                'permissions' => $permission_allowed->toArray(),
                'redirect' => Permission::find($user->role->permissions->where('allow', true)->first()->permission_id)->url
            ], 200);

        }   else {
            return response()->json([
                'type' => 'error',
                'message' => 'Please check your email or password!',
                'errors' => [
                    'password' => [
                        'Your password is invalid!'
                    ]
                ]
            ], 422);
        }
    }
}
