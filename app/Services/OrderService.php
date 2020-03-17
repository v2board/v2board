<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function cancel():void
    {
        $order = $this->order;
        DB::beginTransaction();
        $order->status = 2;
        if (!$order->save()) {
            DB::rollBack();
        }
        if ($order->balance_amount) {
            $userService = new UserService();
            if (!$userService->addBalance($order->user_id, $order->balance_amount)) {
                DB::rollBack();
            }
        }
        DB::commit();
    }
}
