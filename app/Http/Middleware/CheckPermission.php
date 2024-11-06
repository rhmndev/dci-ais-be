<?php

namespace App\Http\Middleware;

use Closure;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,  ...$permissions)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validPermissions = array();

        if ($user->role && $user->role->permissions) {
            foreach ($user->role->permissions as $itemPermission) {
                if (isset($itemPermission->slug) && $itemPermission->slug != "" && $itemPermission->allow == true) {
                    $validPermissions[] = $itemPermission->slug;
                }
            }
        }

        foreach ($permissions as $permission) {
            if ($user->role && $user->role->permissions) {
                if (in_array($permission, $validPermissions)) {
                    return $next($request);
                }
            }
        }

        return response()->json(['message' => 'Permission denied.'], 403);
    }
}
