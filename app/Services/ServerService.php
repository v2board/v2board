<?php

namespace App\Services;

use App\Models\User;

class ServerService
{
    public function getAvailableUsers($groupId)
    {
        return User::whereIn('group_id', $groupId)
            ->whereRaw('u + d < transfer_enable')
            ->where(function ($query) {
                $query->where('expired_at', '>=', time())
                    ->orWhere('expired_at', NULL);
            })
            ->where('banned', 1)
            ->select([
                'id',
                'email',
                't',
                'u',
                'd',
                'transfer_enable',
                'enable',
                'v2ray_uuid',
                'v2ray_alter_id',
                'v2ray_level'
            ])
            ->get();
    }
}
