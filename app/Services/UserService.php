<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function isAvailable()
    {
        if ($this->user->transfer_enable && ($this->user->expired_at > time() || $this->user->expired_at == 0)) {
            return true;
        }
        return false;
    }
}
