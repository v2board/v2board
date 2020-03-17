<?php

namespace App\Services;

use App\Models\Order;

class OrderService
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function cancel()
    {

    }
}
