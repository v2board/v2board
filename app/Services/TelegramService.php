<?php
namespace App\Services;

use \Curl\Curl;

class TelegramService {
    protected $api;

    public function __construct()
    {
        $this->api = 'https://api.telegram.org/bot' . config('v2board.telegram_bot_token') . '/';
    }

    public function sendMessage(int $chatId, string $text, string $parseMode = '')
    {
        $this->request('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode
        ]);
    }

    public function getMe()
    {
        $response = $this->request('getMe');
        if (!$response->ok) {
            return false;
        }
        return $response;
    }

    public function setWebhook(string $url)
    {
        $response = $this->request('setWebhook', [
            'url' => $url
        ]);
        if (!$response->ok) {
            return false;
        }
        return $response;
    }

    private function request(string $method, array $params = [])
    {
        $curl = new Curl();
        $curl->get($this->api . $method, http_build_query($params));
        $curl->close();
        return $curl->response;
    }
}
