<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetNotification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function resetPassword(Request $request, $id)
    {

        $request->validate([
            // You might want other validation rules for an admin route
        ]);


        try {
            $user = User::findOrFail($id);
            $newPassword = $user->email ?? '';

            if (empty($newPassword)) {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'User email is empty. Cannot reset password.',
                ], 400);
            }

            $user->password = Hash::make($newPassword);
            $user->save();

            Mail::to($user->email)->send(new PasswordResetNotification($user, $newPassword));

            return response()->json([
                'type' => 'success',
                'message' => 'Password reset successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Error resetting password: ' . $e->getMessage(), // Log the error
            ], 500); // 500 for server errors
        }
    }
}
