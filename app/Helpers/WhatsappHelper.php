<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\MpOvertimeSetting;

class WhatsappHelper
{
    public static function sendIfEnabled($message, $countryCode = '62')
    {
        $setting = MpOvertimeSetting::first();

        if (!$setting || !$setting->enable_whatsapp || empty($setting->whatsapp_numbers)) {
            Log::info('WhatsApp notification skipped: Disabled or no numbers.');
            return [
                'type' => 'skipped',
                'message' => 'WhatsApp notification disabled or no numbers configured.',
                'data' => null
            ];
        }

        return self::sendMessage($setting->whatsapp_numbers, $message, $countryCode);
    }

    public static function sendMessage($phoneNumbers, $message, $countryCode = '62')
    {
        $token = config('services.fonnte.api_token');
        $phones = is_array($phoneNumbers) ? $phoneNumbers : [$phoneNumbers];

        $formattedPhones = implode(',', array_map(function ($phone) {
            $cleaned = preg_replace('/[^0-9]/', '', $phone);
            return ltrim(preg_replace('/^62/', '', $cleaned), '0');
        }, $phones));

        Log::info("Sending WhatsApp to: {$formattedPhones}");
        Log::info("token: {$token}");

        $postFields = http_build_query([
            'target' => $formattedPhones,
            'message' => $message,
            'countryCode' => $countryCode,
        ]);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.fonnte.com/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: $token",
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            Log::error('cURL error: ' . curl_error($ch));
            curl_close($ch);
            return [
                'type' => 'failed',
                'message' => 'cURL error: ' . curl_error($ch),
                'data' => null
            ];
        }

        curl_close($ch);
        $result = json_decode($response, true);

        if ($httpCode >= 400) {
            Log::error("WhatsApp send failed: " . $response);
            return [
                'type' => 'failed',
                'message' => 'WhatsApp API returned HTTP ' . $httpCode,
                'data' => $result
            ];
        }

        return [
            'type' => 'success',
            'message' => 'Message sent successfully.',
            'data' => $result
        ];
    }
}
