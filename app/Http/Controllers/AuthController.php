<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\User;
use App\Permission;
use App\Mail\UserMail;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function checkToken(Request $request)
    {
        // Check if the user is authenticated (token is valid)
        if (Auth::guard('api')->check()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Token is valid',
                'user' => Auth::user()
            ], 200);
        }

        // If token is invalid or expired
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid or expired token'
        ], 401);
    }

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
            $permission_allowed = $permissions->map(function ($permission) use ($user) {
                $permission_allowed = collect($user->role->permissions)->where('allow', true);

                if ($permission_allowed->pluck('permission_id')->contains($permission->id)) {

                    return [
                        '_id' => $permission->id,
                        'name' => $permission->name,
                        'url' => $permission->url,
                        'icon' => $permission->icon,
                        'children' => $permission->children->sortBy('order_number')->map(function ($child) use ($user) {
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


            $user->photo_url = asset('storage/images/users/' . $user->photo);

            return response()->json([
                'type' => 'success',
                'message' => 'Login successfully!',
                'token' => $token,
                'data' => $user,
                'permissions' => $permission_allowed->toArray(),
                'redirect' => Permission::find($user->role->permissions->where('allow', true)->first()->permission_id)->url
            ], 200);
        } else {
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

    public function show(Request $request)
    {
        $user = User::where('reset_token', $request->token)->firstOrFail();

        return response()->json([
            'type' => 'success',
            'data' =>  $user
        ]);
    }

    public function resetpassword(Request $request)
    {

        $notification_text = '';

        if ($request->token == null) {

            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $notification_text = 'Send email success.';

            $User = User::where('email', $request->email)->firstOrFail();
        } else {

            $request->validate([
                'email' => 'required|email|exists:users,email',
                'token' => 'required|exists:users,reset_token',
                'password' => 'required|confirmed',
            ]);

            $notification_text = 'Reset password success.';

            $User = User::where('reset_token', $request->token)->firstOrFail();
        }

        try {

            $token = Str::random(10);

            if ($request->token == null) {

                Mail::to($request->email)->send(new UserMail($token));
            } else {

                $User->password = Hash::make($request->password);
            }

            $User->reset_token = $token;

            $User->updated_by = 'System';

            $User->save();

            return response()->json([
                'type' => 'success',
                'message' => $notification_text,
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
}
