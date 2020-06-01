<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\TelegramService;

class TelegramController extends Controller
{
    public function getBotInfo()
    {
        $telegramService = new TelegramService();
        $response = $telegramService->getMe();
        return response([
            'data' => [
                'username' => $response->result->username
            ]
        ]);
    }
}
