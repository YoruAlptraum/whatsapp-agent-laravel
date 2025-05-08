<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Handle the incoming webhook request
        $data = $request->all();

        // Process the data as needed
        // For example, you can log it or save it to the database        

        return response()->json(['status' => 'success']);
    }

    public function verifyWebhook(Request $request)
    {
        $mode = $request->query('hub.mode');
        $token = $request->query('hub.verify_token');
        $challenge = $request->query('hub.challenge');

        if ($mode && $token) {
            if ($mode === 'subscribe' && $token === env('VERIFICATION_TOKEN')) {
                return response($challenge, 200);
            } else {
                return response('', 403);
            }
        } else {
            return response()->json(['message' => 'Thank you for the message']);
        }
    }
}
