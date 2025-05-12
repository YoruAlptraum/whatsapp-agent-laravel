<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\messageHandlers;
use App\Services\jsonGetters;

class WebhookController extends Controller
{
    protected $messageHandlers;
    protected $jsonGetters;

    public function __construct()
    {
        $this->messageHandlers = new messageHandlers();
        $this->jsonGetters = new jsonGetters();
    }

    public function handleWebhook(Request $request)
    {
        $data = $request->all();

        // Check if we have the expected data structure
        if (!isset($data['entry'][0]['changes'][0]['value'])) {
            Log::info('Invalid webhook data received');
            return response()->json(['message' => 'Invalid webhook data']);
        }

        $value = $data['entry'][0]['changes'][0]['value'];
        $contacts = $value['contacts'] ?? null;
        $statuses = $value['statuses'] ?? null;
        $timestamp = $this->jsonGetters->getTimestamp($data);

        Log::info("--------------------------------------------------------{$timestamp}--------------------------------------------------------");

        // Determine user's WhatsApp ID
        if ($contacts) {
            $waId = $contacts[0]['wa_id'];
        } elseif ($statuses) {
            $waId = $statuses[0]['recipient_id'];
        } else {
            return response()->json(['message' => 'No contacts or statuses found']);
        }

        $cacheKey = "wa-id:{$waId}";

        // Get user state from Cache
        $userState = Cache::get($cacheKey, [
            'respond' => true,
            'lastUserMessageTime' => '0',
            'nextMessage' => 'welcome'
        ]);

        // Discard old messages
        if ($timestamp < $userState['lastUserMessageTime']) {
            Log::info('Old message');
            return response()->json(['message' => 'Mensagem antiga']);
        }

        // Process message based on type
        if ($contacts) {
            Log::info('Received message');
            $userState = $this->messageHandlers->handleReceivedMessage($data, $userState);
        } else {
            Log::info('Sent message');
            $userState = $this->messageHandlers->handleSentMessage($data, $userState);
        }

        // Store updated user state in Cache
        try {
            Log::info('userState data: ' . json_encode($userState));

            Cache::put($cacheKey, $userState, now()->addDay());
        } catch (\Exception $error) {
            Log::error("Error storing data: {$error->getMessage()}");
        }

        return response()->json(['message' => 'Message processed']);
    }

    public function verifyWebhook(Request $request)
    {
        $info = $request->all();
        $mode = $info['hub_mode'] ?? null;
        $token = $info['hub_verify_token'] ?? null;
        $challenge = $info['hub_challenge'] ?? null;

        Log::info("Mode: {$mode} Token: {$token} Challenge: {$challenge}");

        if ($mode && $token) {
            if ($mode === 'subscribe' && $token === env('VERIFICATION_TOKEN')) {
                Log::info('Webhook verified');
                return response($challenge, 200);
            } else {
                Log::info("Invalid verification token");
                return response('', 403);
            }
        } else {
            Log::info("Invalid webhook verification request");
            return response()->json(['message' => 'Thank you for the message']);
        }
    }
}
