<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class messageSenders
{
    protected $headers;
    protected $url;

    public function __construct()
    {
        $this->headers = [
            'Authorization' => 'Bearer ' . env('WHATSAPP_TOKEN'),
            'Content-Type' => 'application/json',
        ];

        $this->url = env('WHATSAPP_API_URL') . '/' . env('WHATSAPP_PHONE_NUMBER_ID') . '/messages';
    }

    // simple message
    public function sendMessage($to, $body = "")
    {
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'body' => $body,
            ]
        ];

        try {
            $response = Http::withHeaders($this->headers)->post($this->url, $data);

            if ($response->successful()) {
                Log::info('Message sent successfully: ' . $response->body());
                return $response->json();
            } else {
                Log::error('Failed to send message: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return null;
        }
    }

    public function sendInteractiveMessage($to, $body = "", $options = [])
    {
        // button message template
        // {
        //     'type' => 'reply',
        //     'reply' => [
        //         'id' => $option['id'],
        //         'title' => $option['title'],
        //     ],
        // }

        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => [
                    'text' => $body,
                ],
                'action' => [
                    'buttons' => $options,
                ],
            ]
        ];

        try {
            $response = Http::withHeaders($this->headers)->post($this->url, $data);

            if ($response->successful()) {
                Log::info('Message sent successfully: ' . $response->body());
                return $response->json();
            } else {
                Log::error('Failed to send message: ' . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return null;
        }
    }
}
