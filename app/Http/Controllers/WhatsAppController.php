<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;

class WhatsAppController extends Controller
{
    public static function sendWhatsAppMessage(Request $request, $recipient_number = "", $msg = "")
    {
        // $request->validate([
        //     'recipient_number' => 'required',
        //     'msg' => 'required',
        // ]);

        $twilioSid = env('TWILIO_SID');
        $twilioToken = env('TWILIO_AUTH_TOKEN');
        $twilioWhatsAppNumber = env('TWILIO_WHATSAPP_NUMBER');
        // $recipientNumber = 'whatsapp:+6285156376462'; // Replace with the recipient's phone number in WhatsApp format (e.g., "whatsapp:+1234567890")
        $recipientNumber = 'whatsapp:+6281380425601'; // Replace with the recipient's phone number in WhatsApp format (e.g., "whatsapp:+1234567890")
        $message = $msg;

        $twilio = new Client($twilioSid, $twilioToken);

        try {
            $twilio->messages->create(
                $recipientNumber,
                [
                    "from" => $twilioWhatsAppNumber,
                    "body" => $message,
                ]
            );

            return response()->json(['message' => 'WhatsApp message sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
