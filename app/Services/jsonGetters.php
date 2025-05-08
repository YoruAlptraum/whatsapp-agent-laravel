<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class jsonGetters
{
    public function getTimestamp($data)
    {
        if (isset($data['entry'][0]['changes'][0]['value']['messages'][0]['timestamp'])) {
            return $data['entry'][0]['changes'][0]['value']['messages'][0]['timestamp'];
        } elseif (isset($data['entry'][0]['changes'][0]['value']['statuses'][0]['timestamp'])) {
            return $data['entry'][0]['changes'][0]['value']['statuses'][0]['timestamp'];
        } else {
            return null;
        }
    }
}
