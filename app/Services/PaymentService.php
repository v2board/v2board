<?php

namespace App\Services;


use App\Models\Payment;

class PaymentService
{
    public function __construct($method, $id = NULL)
    {
        $this->method = $method;
        $this->class = '\\App\\Payments\\' . $this->method;
        if (!class_exists($this->class)) abort(500, 'gate is not found');
        if ($id) $payment = Payment::find($id)->toArray();
        $this->config = [];
        if (isset($payment) && $payment['config']) $this->config = json_decode($payment['config'], true);
        $this->config['id'] = $id;
        $this->config['enable'] = $payment['enable'];
        $this->payment = new $this->class($this->config);
    }

    public function notify($params)
    {
        if (!$this->config['enable']) abort(500, 'gate is not enable');
        return $this->payment->notify($params);
    }

    public function pay($order)
    {
        return $this->payment->pay([
            'notify_url' => url("/api/v1/guest/payment/notify/{$this->method}/{$this->config['id']}"),
            'return_url' => config('v2board.app_url', env('APP_URL')) . '/#/order/' . $order['trade_no'],
            'trade_no' => $order['trade_no'],
            'total_amount' => $order['total_amount'],
            'user_id' => $order['user_id'],
            'stripe_token' => $order['stripe_token']
        ]);
    }

    public function form()
    {
        $form = $this->payment->form();
        $keys = array_keys($form);
        foreach ($keys as $key) {
            if (isset($this->config[$key])) $form[$key]['value'] = $this->config[$key];
        }
        return $form;
    }
}
