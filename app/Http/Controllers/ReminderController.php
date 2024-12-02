<?php

namespace App\Http\Controllers;

use App\Jobs\SendExpiryReminders;
use App\Reminder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $userId = auth()->user()->_id;
            $reminders = Reminder::where('user_id', $userId)->paginate(10);
            return response()->json([
                'type' => 'success',
                'message' => 'Reminders retrieved successfully.',
                'data' => $reminders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Error retrieving reminders: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function overview(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $totalReminders = Reminder::where('user_id', $userId)->count();
            $upcomingReminders = Reminder::where('user_id', $userId)
                ->where('reminder_datetime', '>', Carbon::now())
                ->count();
            $overdueReminders = Reminder::where('user_id', $userId)
                ->where('reminder_datetime', '<', Carbon::now())
                ->count();

            return response()->json([
                'data' => [
                    'totalReminders' => $totalReminders,
                    'upcomingReminders' => $upcomingReminders,
                    'overdueReminders' => $overdueReminders,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error retrieving overview: ' . $e->getMessage(), // Include error details for debugging
                'data' => null
            ], 500); // 500 for server errors
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,_id', // Validate user_id exists in users table
            'username' => 'required|string',
            // 'remindable_type' => 'required|string',
            // 'remindable_id' => 'required',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'reminder_datetime' => 'required|date',
            'expires_at' => 'nullable|date',
            'reminder_frequency' => 'required|in:daily,weekly,monthly,yearly,custom',
            'frequency_settings' => 'nullable|json',
            'reminder_method' => 'required|in:email,whatsapp,both',
            'whatsapp_number' => 'nullable|string', // Add validation for whatsapp_number
            'emails' => 'nullable|array', // Validate emails as an array
            'files' => 'nullable|array' //optional
        ]);

        if ($validator->fails()) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Validation failed.',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            $validatedData = $validator->validated();

            // Decode JSON fields if necessary
            // if (is_string($validatedData['frequency_settings']) && json_decode($validatedData['frequency_settings']) !== null) {
            //     $validatedData['frequency_settings'] = json_decode($validatedData['frequency_settings'], true);
            // }

            // Same for emails and files
            // if (is_string($validatedData['emails']) && json_decode($validatedData['emails']) !== null) {
            //     $validatedData['emails'] = json_decode($validatedData['emails'], true);
            // }
            // if (is_string($validatedData['files']) && json_decode($validatedData['files']) !== null) {
            //     $validatedData['files'] = json_decode($validatedData['files'], true);
            // }

            $reminder = Reminder::create($validatedData);

            return response()->json([
                'type' => 'success',
                'message' => 'Reminder created successfully.',
                'data' => $reminder
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Error creating reminder: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $reminder = Reminder::find($id);
            if (!$reminder) {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'Reminder not found.',
                    'data' => null
                ], 404);
            }
            return response()->json([
                'type' => 'success',
                'message' => 'Reminder retrieved successfully.',
                'data' => $reminder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Error retrieving reminder: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    // ... update and destroy methods with similar try-catch and response structure

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            // ... validation rules
        ]);

        if ($validator->fails()) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Validation failed.',  // More specific message
                'data' => $validator->errors()
            ], 422);
        }

        try {
            $reminder = Reminder::find($id);
            if (!$reminder) {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'Reminder not found.',
                    'data' => null
                ], 404);
            }

            $reminder->update($validator->validated());

            return response()->json([
                'type' => 'success',
                'message' => 'Reminder updated successfully.',
                'data' => $reminder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Error updating reminder: ' . $e->getMessage(),  // Include error details
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $reminder = Reminder::find($id);
            if (!$reminder) {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'Reminder not found.',
                    'data' => null
                ], 404);
            }
            $reminder->delete();
            return response()->json([
                'type' => 'success',
                'message' => 'Reminder deleted successfully.',
                'data' => null // or you could return the deleted reminder data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Error deleting reminder: ' . $e->getMessage(),  // Specific error message
                'data' => null
            ], 500); // 500 for server errors
        }
    }

    public function testNotification($id)
    {
        try {
            $reminder = Reminder::find($id);
            if (!$reminder) {
                return response()->json([
                    'type' => 'failed',
                    'message' => 'Reminder not found.',
                    'data' => null
                ], 404);
            }

            // Dispatch the notification job
            SendExpiryReminders::dispatchNow($reminder);


            return response()->json([
                'type' => 'success',
                'message' => 'Test notification dispatched successfully.',
                'data' => $reminder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Error dispatching test notification: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
