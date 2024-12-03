<?php

namespace App\Http\Controllers;

use App\Jobs\SendExpiryReminders;
use App\Reminder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        $request->validate([
            // 'remindable_type' => 'required',
            // 'remindable_id' => 'required',
            'title' => 'required',
            'description' => 'nullable',
            'expires_datetime' => 'required|date',
            'reminder_method' => 'required|in:email,whatsapp,both',
            // 'reminder_frequency' => 'required',
            // 'frequency_settings' => 'nullable|json',
            'emails' => 'nullable',
            'files' => 'nullable'
        ]);

        try {
            Log::info($request->all());
            $repeatData = $request->repeat;
            if (is_string($repeatData)) {
                $repeatData = json_decode($repeatData, true);
            }
            $remindMeAtData = $request->remindMeAt;
            if (is_string($remindMeAtData)) {
                $remindMeAtData = json_decode($remindMeAtData, true);
            }

            $filePaths = [];

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $originalFileName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $fileNameWithoutExtension = pathinfo($originalFileName, PATHINFO_FILENAME);
                    // ... (handle empty filename if needed)

                    $fileName = Str::slug($fileNameWithoutExtension, '-') . '_' . time() . '.' . $extension;

                    $filePath = 'reminder-attachments/' . $fileName;  // Relative path within storage/app/public

                    Storage::disk('public')->put($filePath, file_get_contents($file)); // Store the file

                    $filePaths[] = Storage::url($filePath); // Get the URL for accessing the file
                }
            }
            // return response()->json([
            //     'type' => 'failed',
            //     'message' => 'Reminder created asd.',
            //     'data' => $repeatData,
            //     'data2' => $remindMeAtData
            // ], 400);

            $reminder = Reminder::create([
                'user_id' => auth()->user()->id,
                'username' => auth()->user()->username,
                'title' => $request->title,
                'description' => $request->description,
                'starred' => $request->starred ?? false,
                'category' => $request->category,
                'expires_at' => $request->expires_datetime ? Carbon::parse($request->expires_datetime)->toDateTimeString() : null,
                'is_repeat' => $repeatData['repeat'] ?? false,
                'repeat_freq' =>  $repeatData['frequency'] ?? null,
                'repeat_interval' =>  $repeatData['interval'] ?? null,
                'repeat_reminder_time' =>  $repeatData['reminderTime'] ?? Carbon::now(),
                'repeat_start_date' =>  $repeatData['startDate'] ?? Carbon::today(),
                'repeat_day_of_month' => $repeatData['dayOfMonth'] ?? null,
                'repeat_day_of_week' => $repeatData['daysOfWeek'] ?? null,
                'repeat_end_type' => $repeatData['endType'] ?? "never",
                'repeat_end_date' => $repeatData['endDate'] ?? null,
                'repeat_end_occurrences' => $repeatData['endOccurrences'] ?? 0,
                'reminder_frequency' => $remindMeAtData['frequencies'] ?? null,
                'reminder_interval_day' => $remindMeAtData['intervalDay'],
                'reminder_interval_week' => $remindMeAtData['intervalWeek'],
                'reminder_interval_month' => $remindMeAtData['intervalMonth'],
                'reminder_interval_year' => $remindMeAtData['intervalYear'],
                'reminder_method' => $request->reminder_method,
                'whatsapp_number' => $request->whatsapp_number ?? null,
                'emails' => $request->emails ?? null,
                'files' => $filePaths,
                'is_reminded' => ($remindMeAtData['frequencies'] && count($remindMeAtData['frequencies'])) ?  true : false,
                'ends_at' => $request->ends_at,
                'max_occurrences' => $request->max_occurrences,
                'notify_until_expired' => $request->notifyUntilExpired,
                'notify_interval' => $request->notifyInterval,
                'status' => 'running'
            ]);

            return response()->json([
                'type' => 'success',
                'message' => 'Reminder created successfully.',
                'data' => $reminder
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Error creating reminder: ' . $e,
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
