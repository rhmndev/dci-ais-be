<?php

namespace App\Http\Controllers;

use App\PartMonitoringSetting;
use Illuminate\Http\Request;
use App\Helpers\WhatsappHelper;
use Illuminate\Support\Facades\Log;

class PartMonitoringSettingController extends Controller
{
    public function show()
    {
        $setting = PartMonitoringSetting::first(); // only one setting expected
        return response()->json([
            'message' => 'success',
            'data' => $setting
        ]);
    }
    public function update(Request $request)
    {
        $validated = $request->validate([
            'enable_whatsapp' => 'required|boolean',
            'whatsapp_numbers' => 'nullable|string', // or array if JSON
        ]);

        $setting = PartMonitoringSetting::first();

        if (!$setting) {
            $setting = PartMonitoringSetting::create($validated);
        } else {
            $setting->update($validated);
        }

        return response()->json([
            'message' => 'Setting saved successfully',
            'data' => $setting
        ]);
    }
    public function getWhatsappNumbers()
    {
        $setting = PartMonitoringSetting::first();
        if ($setting && $setting->enable_whatsapp) {
            return response()->json([
                'message' => 'success',
                'data' => explode(',', $setting->whatsapp_numbers)
            ]);
        }

        return response()->json([
            'message' => 'WhatsApp notifications are disabled or no numbers found.',
            'data' => []
        ]);
    }
    public function setWhatsappNumbers(Request $request)
    {
        $validated = $request->validate([
            'whatsapp_numbers' => 'required|string', // or array if JSON
        ]);

        $setting = PartMonitoringSetting::first();

        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found',
            ], 404);
        }

        $setting->update($validated);

        return response()->json([
            'message' => 'WhatsApp numbers updated successfully',
            'data' => explode(',', $setting->whatsapp_numbers)
        ]);
    }
    public function enableWhatsappNotifications(Request $request)
    {
        $validated = $request->validate([
            'enable_whatsapp' => 'required|boolean',
        ]);

        $setting = PartMonitoringSetting::first();

        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found',
            ], 404);
        }

        $setting->update($validated);

        return response()->json([
            'message' => 'WhatsApp notifications updated successfully',
            'data' => $setting
        ]);
    }

    public function disableWhatsappNotifications(Request $request)
    {
        $setting = PartMonitoringSetting::first();

        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found',
            ], 404);
        }

        $setting->update(['enable_whatsapp' => false]);

        return response()->json([
            'message' => 'WhatsApp notifications disabled successfully',
            'data' => $setting
        ]);
    }

    public function testNotification(Request $request)
    {
        $setting = PartMonitoringSetting::first();

        if (!$setting || !$setting->enable_whatsapp) {
            return response()->json([
                'message' => 'WhatsApp notifications are disabled or no numbers found.',
                'data' => []
            ]);
        }

        $numbers = explode(',', $setting->whatsapp_numbers);
        // Here you would send the message to the WhatsApp numbers
        // For example, using a WhatsApp API

        $response = WhatsappHelper::sendMessage($numbers, "Test Notification: This is a test message from the Part Monitoring System.");

        Log::info('Whatsapp response:', [
            'response' => $response,
            'request' => $request->all(),
        ]);
        return response()->json([
            'message' => 'Test notification sent successfully',
            'data' => $numbers
        ]);
    }

    public function sendNotification(Request $request)
    {
        $setting = PartMonitoringSetting::first();

        if (!$setting || !$setting->enable_whatsapp) {
            return response()->json([
                'message' => 'WhatsApp notifications are disabled or no numbers found.',
                'data' => []
            ]);
        }

        $numbers = explode(',', $setting->whatsapp_numbers);
        // Here you would send the message to the WhatsApp numbers
        // For example, using a WhatsApp API

        $response = WhatsappHelper::sendMessage($numbers, "🚨 Notification: {$request->message}");

        Log::info('Whatsapp response:', [
            'response' => $response,
            'request' => $request->all(),
        ]);
        return response()->json([
            'message' => 'Notification sent successfully',
            'data' => $numbers
        ]);
    }
}
