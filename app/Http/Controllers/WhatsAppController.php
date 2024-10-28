<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;

class WhatsAppController extends Controller
{
    public static function sendWhatsAppMessage($recipient_number = "", $msg = "")
    {
        $twilioSid = env('TWILIO_SID');
        $twilioToken = env('TWILIO_AUTH_TOKEN');
        $twilioWhatsAppNumber = env('TWILIO_WHATSAPP_NUMBER');
        $recipientNumber = 'whatsapp:+' . $recipient_number; // Replace with the recipient's phone number in WhatsApp format (e.g., "whatsapp:+1234567890")

        $twilio = new Client($twilioSid, $twilioToken);

        try {
            $message = $twilio->messages->create($recipientNumber, [
                "from" => $twilioWhatsAppNumber,
                'body' => $msg
            ]);

            return response()->json(['message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
