<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Services\messageSenders;

class messageHandlers
{
    protected $messageSenders;

    public function __construct()
    {
        $this->messageSenders = new messageSenders();
    }

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
            if ($messages[0]['type'] == 'text' && $msg == 'stop') {
                Log::info('>>>>>>>>> stop message <<<<<<<<<');
                // send message that will stop the bot from responding
                $this->messageSenders->sendMessage($contacts[0]["wa_id"], "stop responding");
            }



            try {
                $apiRes = null;
                // check if next message has a message to send



            } catch (\Exception $e) {
                Log::error('Error handling message: ' . $e->getMessage());
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
