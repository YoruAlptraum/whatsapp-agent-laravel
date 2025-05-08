<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class messageHandlers
{
    public function handleReceivedMessage($data, $userState)
    {
        $value = $data['entry'][0]['changes'][0]['value'];
        $contacts = $value['contacts'] ?? null;
        $messages = $value['messages'] ?? null;

        if (!$userState['respond']) {
            Log::info('>>>>>>>>> respond is set to false <<<<<<<<<');
        } else if ($contacts && $messages) {
            $userState['lastUserMessageTime'] = $messages[0]['timestamp'];
            $msg = $messages[0]['text']['body'] ?? "";

            // for testing: manually trigger a message to stop the bot fom responding
            if ($msg == 'stop') {
                Log::info('>>>>>>>>> stop message <<<<<<<<<');
                // sendMessage(contacts[0]["wa_id"], "stop responding");
            }

            try {
            } catch (\Exception $e) {
                Log::error('Error sending message: ' . $e->getMessage());
            }
        } else {
            Log::info('No contacts or messages found');
        }

        return $userState;
    }

    public function handleSentMessage($data, $userState)
    {
        $value = $data['entry'][0]['changes'][0]['value'];
        $contacts = $value['contacts'] ?? null;
        $statuses = $value['statuses'] ?? null;


        return $userState;
    }
}
