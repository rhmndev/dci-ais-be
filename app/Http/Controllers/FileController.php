<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $skip = $request->perpage * ($request->page - 1);

        $files = File::where(function ($where) use ($request) {
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

        $total = File::where(function ($where) use ($request) {
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
            ->count();

        return response()->json([
            'type' => 'success',
            'data' => $files,
            'total' => $total
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            // 1. Validate (file type, size, expiry days if applicable)
            $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|file|max:' . File::MAX_UPLOAD_SIZE,
            ]);

            // 2. Store the file (using Storage facade)
            $filePath = $request->file('file')->store('uploads', 'public'); // Or your preferred storage

            // 3. Calculate expiry (if provided in the request)
            $expiresAt = $request->has('expiry_days')
                ? Carbon::now()->addDays($request->input('expiry_days'))
                : null;

            // 4. Create the database record
            $file = File::create([
                'user_id' => auth()->id(),
                'name' => $request->file('file')->getClientOriginalName(),
                'path' => $filePath,
                'size' => $request->file('file')->getSize(),
                'type' => $request->type,
                'ext' => $request->file('file')->getClientOriginalExtension(),
                'expires_at' => $expiresAt,
            ]);

            $file->save();

            $file->sendNotifications();

            return response()->json([
                'type' => 'success',
                'message' => 'File uploaded successfully!',
                'data' => $file
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:' . File::MAX_UPLOAD_SIZE, // Use the constant
            'original_name' => 'required|string',
            'expires_at' => 'nullable|date', // Make expires_at nullable
            'notify_expiry' => 'boolean',
            'notification_method' => 'string|in:email,whatsapp,both',
            'whatsapp_number' => 'nullable|string',
            'reminder_datetime' => 'nullable|date',
            'reminder_method' => 'string|in:email,whatsapp,both',
            'send_notification' => 'boolean',
            'send_notification_only_me' => 'boolean',
            'send_notification_to' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        return response()->json([
            'type' => 'failed',
            'message' => 'File ',
            'data' => $validator->errors(),
        ], 400);

        $file = $request->file('file');
        $uuid = Str::uuid()->toString();
        $originalName = $request->input('original_name');
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $fileCategory = $this->getFileCategory($mimeType);
        $disk = 'public';
        $path = $file->storeAs('uploads', "$uuid.$extension", $disk);
        $expiresAt = $request->input('expires_at');
        $notifyExpiry = $request->boolean('notify_expiry', false);
        $notificationMethod = $request->input('notification_method', 'email');
        $whatsappNumber = $request->input('whatsapp_number');
        $reminderDatetime = $request->input('reminder_datetime');
        $reminderMethod = $request->input('reminder_method', 'email');
        $sendNotification = $request->boolean('send_notification', true);
        $sendNotificationOnlyMe = $request->boolean('send_notification_only_me', true);
        $sendNotificationTo = $request->input('send_notification_to');
        $remindMeLater = $request->boolean('remind_me_later', false);
        $remindAt = $request->input('remind_at');




        $file = File::create([
            'uuid' => $uuid,
            'user_id' => auth()->id(),
            'user_npk' => auth()->user()->npk,
            'original_name' => $originalName,
            'name' => "$uuid.$extension",
            'disk' => $disk,
            'path' => $path,
            'mime_type' => $mimeType,
            'size' => $size,
            'file_category' => $fileCategory,
            'type' => $mimeType,
            'extension' => $extension,
            'created_by' => auth()->id(),
            'expires_at' => $expiresAt ? Carbon::parse($expiresAt) : null,
            'notify_expiry' => $notifyExpiry,
            'notification_method' => $notificationMethod,
            'whatsapp_number' => $whatsappNumber,
            'reminder_datetime' => $reminderDatetime ? Carbon::parse($reminderDatetime) : null,
            'reminder_method' => $reminderMethod,
            'send_notification' => $sendNotification,
            'send_notification_only_me' => $sendNotificationOnlyMe,
            'send_notification_to' => $sendNotificationTo,
            'remind_me_later' => $remindMeLater,
            'remind_at' => $remindAt ? Carbon::parse($remindAt) : null,


        ]);


        if ($file->expires_at) {
            dispatch(new ProcessFileExpiry($file))->delay($file->expires_at);
        }

        $file->sendNotifications();


        return response()->json(['message' => 'File uploaded successfully', 'file' => $file], 201);
    }


    public function download(Request $request, $uuid)
    {
        $file = File::where('uuid', $uuid)->firstOrFail();
        // Log::info($file);
        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }


    private function getFileCategory($mimeType)
    {

        if (Str::startsWith($mimeType, 'image/')) {
            return 'image';
        } elseif (Str::startsWith($mimeType, 'application/pdf')) {
            return 'document';
        } elseif (Str::startsWith($mimeType, 'video/')) {
            return 'video';
        }

        // Add more conditions for other categories...

        return 'other'; // Default category if no match is found
    }
}
