<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class AuthService
{
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function generateAuthData($utm)
    {
        return [
            'token' => $this->user->token,
            'is_admin' => $this->user->is_admin,
            'auth_data' => JWT::encode([
                'expired_at' => time() + 3600,
                'id' => $this->user->id,
                'utm' => $utm,
            ], config('app.key'), 'HS256')
        ];
    }


    public static function decryptAuthData($jwt)
    {
        try {
            if (!Cache::has($jwt)) {
                $data = (array)JWT::decode($jwt, new Key(config('app.key'), 'HS256'));
                if ($data['expired_at'] < time()) return false;
                $user = User::select([
                    'id',
                    'email',
                    'is_admin',
                    'is_staff'
                ])
                    ->find($data['id']);
                if (!$user) return false;
                Cache::put($jwt, $user->toArray(), 3600);
            }
            return Cache::get($jwt);
        } catch (\Exception $e) {
            return false;
        }
    }
}
