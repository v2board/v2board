<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderHandleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $order;

    public $tries = 3;
    public $timeout = 5;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tradeNo)
    {
        $this->onQueue('order_handle');
        $this->order = Order::where('trade_no', $tradeNo)
            ->lockForUpdate()
            ->first();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->order) return;
        $orderService = new OrderService($this->order);
        switch ($this->order->status) {
            // cancel
            case 0:
                if ($this->order->created_at <= (time() - 1800)) {
                    $orderService->cancel();
                }
                break;
            case 1:
                $orderService->open();
                break;
        }
    }
}
