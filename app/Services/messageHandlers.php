<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\messageSenders;

class messageHandlers
{
    protected $messageSenders;
    protected $responseDict;

    public function __construct()
    {
        $this->messageSenders = new messageSenders();

        $json = Storage::disk('local')->get('/responses.json');
        $this->responseDict = json_decode($json, true)['responses'];
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
                if (isset($this->responseDict[$userState['nextMessage']]['message'])) {
                    $msg = $this->responseDict[$userState['nextMessage']]['message'];
                    $nextMessage = $this->responseDict[$userState['nextMessage']]['next'];
                } else {
                    $userInput = "";
                    // validate user input
                    if ($messages[0]['type'] == 'text') {
                        $userInput = strtolower($messages[0]['text']['body']);
                    } else if ($messages[0]['type'] == 'interactive') {
                        $userInput = $messages[0]['interactive']['button_reply']['id'];
                    }

                    if (isset($this->responseDict[$userState['nextMessage']][$userInput])) {
                        $msg = $this->responseDict[$userState['nextMessage']][$userInput]['message'];
                        $nextMessage = $this->responseDict[$userState['nextMessage']][$userInput]['next'];
                    } else {
                        $msg = "Opção inválida. Digite novamente";
                    }
                }

                if (isset($this->responseDict[$userState['nextMessage']]['options'])) {
                    foreach ($this->responseDict[$userState['nextMessage']]['options'] as $key => $title) {
                        Log::info("option: $title (id: $key)");
                        $options[] = [
                            'type' => 'reply',
                            'reply' => [
                                'id' => $key,
                                'title' => $title,
                            ],
                        ];
                    }
                    $apiRes = $this->messageSenders->sendInteractiveMessage($contacts[0]["wa_id"], $msg, $options);
                } else {
                    $apiRes = $this->messageSenders->sendMessage($contacts[0]["wa_id"], $msg);
                }

                $userState['nextMessage'] = $nextMessage ?? $userState['nextMessage'];
                if ($userState['nextMessage'] == 'end') {
                    $userState['respond'] = false;
                }
                $userState['lastAPIMessage'] = $apiRes['messages'][0]['id'];

                Log::info("Message ID: " . $userState['lastAPIMessage']);
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

        // Pending 

        return $userState;
    }
}
