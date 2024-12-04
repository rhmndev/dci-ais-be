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
            $perPage = $request->query('per_page', 10); // Get per_page from query params, default 10

            $reminders = Reminder::where('user_id', $userId)
                ->where(function ($query) {
                    $query->whereNull('status')
                        ->orWhere('status', '!=', 'completed');
                })
                ->paginate($perPage);

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

    public function upcoming(Request $request)
    {
        try {
            $userId = auth()->user()->_id;
            $perPage = $request->query('per_page', 10);

            $upcomingReminders = Reminder::where('user_id', $userId)
                ->where('expires_at', '>', Carbon::now())
                ->where(function ($query) {
                    $query->whereNull('status')
                        ->orWhere('status', '!=', 'completed');
                })
                ->paginate($perPage);

            return response()->json([
                'type' => 'success',
                'message' => 'Upcoming reminders retrieved successfully.',
                'data' => $upcomingReminders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error retrieving upcoming reminders: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function showAll(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $reminders = Reminder::with('user')->paginate($perPage);

            return response()->json([
                'type' => 'success',
                'message' => 'Reminders for user retrieved successfully.',
                'data' => $reminders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
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
                ->where('expires_at', '>', Carbon::now())->where(function ($query) {
                    $query->whereNull('status')
                        ->orWhere('status', '!=', 'completed');
                })
                ->count();
            $overdueReminders = Reminder::where('user_id', $userId)
                ->where('expires_at', '<', Carbon::now())->where(function ($query) {
                    $query->whereNull('status')
                        ->orWhere('status', '!=', 'completed');
                })
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
                'message' => 'Error retrieving overview: ' . $e->getMessage(),
                'data' => null
            ], 500);
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

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $originalFileName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $fileNameWithoutExtension = pathinfo($originalFileName, PATHINFO_FILENAME);
                    // ... (handle empty filename if needed)

                    $fileName = Str::slug($fileNameWithoutExtension, '-') . '_' . time() . '.' . $extension;

                    $filePath = 'reminder-attachments/' . $fileName;  // Relative path within storage/app/public

                    Storage::disk('public')->put($filePath, file_get_contents($file)); // Store the file

                    $filePaths[] = $filePath;
                }
            }

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
                'repeat_start_date' =>  isset($repeatData['startDate']) ? $repeatData['startDate'] : Carbon::now(),
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
            'title' => 'required',
            'description' => 'nullable',
            'expires_datetime' => 'nullable|date',
            'reminder_method' => 'required|in:email,whatsapp,both',
            'whatsapp_number' => 'nullable',
            'emails' => 'nullable',
            'files' => 'nullable',
            'starred' => 'boolean',
            'category' => 'nullable',
            'is_repeat' => 'boolean',
            'repeat_freq' => 'nullable|in:daily,weekly,monthly,yearly',
            'repeat_interval' => 'nullable|numeric',
            'repeat_reminder_time' => 'nullable|date_format:H:i',
            'repeat_start_date' => 'nullable|date',
            'repeat_day_of_month' => 'nullable|numeric',
            'repeat_day_of_week' => 'nullable|array',
            'repeat_end_type' => 'nullable|in:never,endDate,endOccurrences',
            'repeat_end_date' => 'nullable|date',
            'repeat_end_occurrences' => 'nullable|numeric',
            'reminder_frequency' => 'nullable|array',
            'reminder_interval_day' => 'nullable|numeric',
            'reminder_interval_week' => 'nullable|numeric',
            'reminder_interval_month' => 'nullable|numeric',
            'reminder_interval_year' => 'nullable|numeric',
            'ends_at' => 'nullable|date',
            'max_occurrences' => 'nullable|numeric',
            'notify_until_expired' => 'boolean',
            'notify_interval' => 'nullable|numeric'
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

    public function downloadReminderFile($id, $filename)
    {
        try {
            $reminder = Reminder::findOrFail($id);

            // Validate that the filename exists in the reminder's files array. This is important for security to prevent arbitrary file access.
            if (!in_array($filename, $reminder->files)) {
                return response()->json(['type' => 'error', 'message' => 'File not found in this reminder.'], 404);
            }


            $filePath = storage_path('app/public/' . str_replace("storage/reminder-attachments/", '', $filename));


            if (Storage::disk('public')->exists(str_replace("storage/", '', $filename))) {
                return response()->download($filePath, basename($filename));
            } else {
                return response()->json(['type' => 'error', 'message' => 'File not found.'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['type' => 'error', 'message' => 'Error downloading file: ' . $e->getMessage()], 500);
        }
    }

    public function completeReminder(Request $request, $reminderId)
    {
        try {
            $reminder = Reminder::findOrFail($reminderId);

            // Check if the reminder belongs to the authenticated user (important!)
            if ($reminder->user_id != auth()->user()->id) {
                return response()->json(['type' => 'error', 'message' => 'Unauthorized.'], 403);
            }

            // Perform the completion logic.  This might involve:
            // 1. Changing a status field (e.g., 'completed' or 'closed')
            $reminder->status = 'completed'; // Or any status you want
            $reminder->completed_at = Carbon::now();

            // 2. Stopping any recurring schedules if needed. (Depends on your implementation)
            // if ($reminder->is_repeat){
            //    // handle cancelling repeat logic
            //}

            $reminder->save();


            return response()->json([
                'type' => 'success',
                'message' => 'Reminder marked as complete.',
                'data' => $reminder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error completing reminder: ' . $e->getMessage()
            ], 500);
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
    public function getByStatus(Request $request, string $status)
    {
        try {
            $userId = auth()->user()->_id;
            $perPage = $request->query('per_page', 10); // Get per_page from query params, default 10
            $reminders = Reminder::where('user_id', $userId)
                ->where('status', $status)
                ->paginate($perPage);

            return response()->json([
                'type' => 'success',
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
}
