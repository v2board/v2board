<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PaymentSave;
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
            $notifyUrl = url("/api/v1/guest/payment/notify/{$v->payment}/{$v->uuid}");
            if ($v->notify_domain) {
                $parseUrl = parse_url($notifyUrl);
                $notifyUrl = $v->notify_domain . $parseUrl['path'];
            }
            $payments[$k]['notify_url'] = $notifyUrl;
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
        if (!config('v2board.app_url')) {
            abort(500, '请在站点配置中配置站点地址');
        }
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
        $params = $request->validate([
            'name' => 'required',
            'icon' => 'nullable',
            'payment' => 'required',
            'config' => 'required',
            'notify_domain' => 'nullable|url'
        ], [
            'name.required' => '显示名称不能为空',
            'payment.required' => '网关参数不能为空',
            'config.required' => '配置参数不能为空',
            'notify_domain.url' => '自定义通知域名格式有误'
        ]);
        $params['uuid'] = Helper::randomChar(8);
        if (!Payment::create($params)) {
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
