<?php

namespace App\Http\Controllers;

use App\MpOvertimeSetting;
use Illuminate\Http\Request;

class MpOvertimeSettingController extends Controller
{
    public function show()
    {
        $setting = MpOvertimeSetting::first(); // only one setting expected
        return response()->json([
            'message' => 'success',
            'data' => $setting
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'start_time_open_overtime' => 'nullable|string',
            'end_time_open_overtime' => 'nullable|string',
            'enable_whatsapp' => 'required|boolean',
            'whatsapp_numbers' => 'nullable|string', // or array if JSON
        ]);

        $setting = MpOvertimeSetting::first();

        if (!$setting) {
            $setting = MpOvertimeSetting::create($validated);
        } else {
            $setting->update($validated);
        }

        return response()->json([
            'message' => 'Setting saved successfully',
            'data' => $setting
        ]);
    }
}
