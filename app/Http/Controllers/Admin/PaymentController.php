<?php

namespace App\Http\Controllers\Admin;

use App\Services\PaymentService;
use App\Utils\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function getPaymentMethods()
    {
        $methods = [];
        foreach (glob(base_path('app//Payments') . '/*.php') as $file) {
            array_push($methods, pathinfo($file)['filename']);
        }
        return response([
            'data' => $methods
        ]);
    }

    public function fetch()
    {
        $payments = Payment::all();
        foreach ($payments as $k => $v) {
            $payments[$k]['notify_url'] = url("/api/v1/guest/payment/notify/{$v->payment}/{$v->uuid}");
        }
        return response([
            'data' => $payments
        ]);
    }

    public function getPaymentForm(Request $request)
    {
        $paymentService = new PaymentService($request->input('payment'), $request->input('id'));
        return response([
            'data' => $paymentService->form()
        ]);
    }

    public function save(Request $request)
    {
        if ($request->input('id')) {
            $payment = Payment::find($request->input('id'));
            if (!$payment) abort(500, '支付方式不存在');
            try {
                $payment->update($request->input());
            } catch (\Exception $e) {
                abort(500, '更新失败');
            }
            return response([
                'data' => true
            ]);
        }
        if (!Payment::create([
            'name' => $request->input('name'),
            'payment' => $request->input('payment'),
            'config' => $request->input('config'),
            'uuid' => Helper::guid()
        ])) {
            abort(500, '保存失败');
        }
        return response([
            'data' => true
        ]);
    }

    public function drop(Request $request)
    {
        $payment = Payment::find($request->input('id'));
        if (!$payment) abort(500, '支付方式不存在');
        return response([
            'data' => $payment->delete()
        ]);
    }
}
