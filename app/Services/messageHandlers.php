<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\messageSenders;

class messageHandlers
{
    protected $messageSenders;
    protected $responseDict;
    protected $invalidDict;

    public function __construct()
    {
        $this->messageSenders = new messageSenders();

        $json = Storage::disk('local')->get('/responses.json');
        $this->responseDict = json_decode($json, true)['responses'];
        $this->invalidDict = json_decode($json, true)['invalid'];
    }

    public function handleReceivedMessage($data, $userState)
    {
        $value = $data['entry'][0]['changes'][0]['value'];
        $contacts = $value['contacts'];
        $messages = $value['messages'];

        if (!$userState['respond']) {
            Log::info('>>>>>>>>> respond is set to false <<<<<<<<<');
        } else if ($contacts && $messages) {
            $userState['lastUserMessageTime'] = $messages[0]['timestamp'];
            $msg = $messages[0]['text']['body'] ?? "";

            // for testing: manually trigger a message to stop the bot fom responding
            if (env('APP_ENV', 'local') == 'local' && $messages[0]['type'] == 'text' && $msg == 'stop') {
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
                        $msgOptions = $this->responseDict[$userState['nextMessage']][$userInput]['options'] ?? [];
                    } else {
                        $msg = $this->invalidDict["invalid-option"]['message'];
                    }
                }

                if (isset($this->responseDict[$userState['nextMessage']]['options']) || !empty($msgOptions)) {
                    $msgOptions = $msgOptions ?? $this->responseDict[$userState['nextMessage']]['options'];
                    foreach ($msgOptions as $key => $title) {
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
        $statuses = $value['statuses'] ?? null;

        // Check if the message is from human-agent and stop responding
        if ($statuses && $statuses[0]["id"] !== $userState['lastAPIMessage']) {
            $userState['respond'] = false;
            Log::info("Agent message received. Stopping bot response.");
        }

        return $userState;
    }
}
