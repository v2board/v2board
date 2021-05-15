<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\OrderSave;
use App\Models\Payment;
use App\Services\CouponService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Utils\Helper;
use Omnipay\Omnipay;
use Stripe\Stripe;
use Stripe\Source;
use Library\BitpayX;
use Library\MGate;
use Library\Epay;

class OrderController extends Controller
{
    public function fetch(Request $request)
    {
        $model = Order::where('user_id', $request->session()->get('id'))
            ->orderBy('created_at', 'DESC');
        if ($request->input('status') !== null) {
            $model->where('status', $request->input('status'));
        }
        $order = $model->get();
        $plan = Plan::get();
        for ($i = 0; $i < count($order); $i++) {
            for ($x = 0; $x < count($plan); $x++) {
                if ($order[$i]['plan_id'] === $plan[$x]['id']) {
                    $order[$i]['plan'] = $plan[$x];
                }
            }
        }
        return response([
            'data' => $order->makeHidden(['id', 'user_id'])
        ]);
    }

    public function details(Request $request)
    {
        $order = Order::where('user_id', $request->session()->get('id'))
            ->where('trade_no', $request->input('trade_no'))
            ->first();
        if (!$order) {
            abort(500, __('user.order.details.order_not_exist'));
        }
        $order['plan'] = Plan::find($order->plan_id);
        $order['try_out_plan_id'] = (int)config('v2board.try_out_plan_id');
        if (!$order['plan']) {
            abort(500, __('user.order.details.plan_not_exist'));
        }
        return response([
            'data' => $order
        ]);
    }

    public function save(OrderSave $request)
    {
        $userService = new UserService();
        if ($userService->isNotCompleteOrderByUserId($request->session()->get('id'))) {
            abort(500, __('user.order.save.exist_open_order'));
        }

        $plan = Plan::find($request->input('plan_id'));
        $user = User::find($request->session()->get('id'));

        if (!$plan) {
            abort(500, __('user.order.save.plan_not_exist'));
        }

        if ((!$plan->show && !$plan->renew) || (!$plan->show && $user->plan_id !== $plan->id)) {
            if ($request->input('cycle') !== 'reset_price') {
                abort(500, __('user.order.save.plan_stop_sell'));
            }
        }

        if (!$plan->renew && $user->plan_id == $plan->id && $request->input('cycle') !== 'reset_price') {
            abort(500, __('user.order.save.plan_stop_renew'));
        }

        if ($plan[$request->input('cycle')] === NULL) {
            abort(500, __('user.order.save.plan_stop'));
        }

        if ($request->input('cycle') === 'reset_price') {
            if ($user->expired_at <= time() || !$user->plan_id) {
                abort(500, __('user.order.save.plan_exist_not_buy_package'));
            }
        }

        if (!$plan->show && $plan->renew && !$userService->isAvailable($user)) {
            abort(500, __('user.order.save.plan_expired'));
        }

        DB::beginTransaction();
        $order = new Order();
        $orderService = new OrderService($order);
        $order->user_id = $request->session()->get('id');
        $order->plan_id = $plan->id;
        $order->cycle = $request->input('cycle');
        $order->trade_no = Helper::guid();
        $order->total_amount = $plan[$request->input('cycle')];

        if ($request->input('coupon_code')) {
            $couponService = new CouponService($request->input('coupon_code'));
            if (!$couponService->use($order)) {
                DB::rollBack();
                abort(500, __('user.order.save.coupon_use_failed'));
            }
            $order->coupon_id = $couponService->getId();
        }

        $orderService->setVipDiscount($user);
        $orderService->setOrderType($user);
        $orderService->setInvite($user);

        if ($user->balance && $order->total_amount > 0) {
            $remainingBalance = $user->balance - $order->total_amount;
            $userService = new UserService();
            if ($remainingBalance > 0) {
                if (!$userService->addBalance($order->user_id, - $order->total_amount)) {
                    DB::rollBack();
                    abort(500, __('user.order.save.insufficient_balance'));
                }
                $order->balance_amount = $order->total_amount;
                $order->total_amount = 0;
            } else {
                if (!$userService->addBalance($order->user_id, - $user->balance)) {
                    DB::rollBack();
                    abort(500, __('user.order.save.insufficient_balance'));
                }
                $order->balance_amount = $user->balance;
                $order->total_amount = $order->total_amount - $user->balance;
            }
        }

        if (!$order->save()) {
            DB::rollback();
            abort(500, __('user.order.save.order_create_failed'));
        }

        DB::commit();

        return response([
            'data' => $order->trade_no
        ]);
    }

    public function checkout(Request $request)
    {
        $tradeNo = $request->input('trade_no');
        $method = $request->input('method');
        $order = Order::where('trade_no', $tradeNo)
            ->where('user_id', $request->session()->get('id'))
            ->where('status', 0)
            ->first();
        if (!$order) {
            abort(500, __('user.order.checkout.order_not_exist_or_paid'));
        }
        // free process
        if ($order->total_amount <= 0) {
            $order->total_amount = 0;
            $order->status = 1;
            $order->save();
            return response([
                'type' => -1,
                'data' => true
            ]);
        }
        $payment = Payment::find($method);
        if (!$payment || $payment->enable !== 1) abort(500, __('user.order.checkout.pay_method_not_use'));
        $paymentService = new PaymentService($payment->payment, $payment->id);
        $result = $paymentService->pay([
            'trade_no' => $tradeNo,
            'total_amount' => $order->total_amount,
            'user_id' => $order->user_id,
            'stripe_token' => $request->input('token')
        ]);
        $order->update(['payment_id' => $method]);
        return response([
            'type' => $result['type'],
            'data' => $result['data']
        ]);
    }

    public function check(Request $request)
    {
        $tradeNo = $request->input('trade_no');
        $order = Order::where('trade_no', $tradeNo)
            ->where('user_id', $request->session()->get('id'))
            ->first();
        if (!$order) {
            abort(500, __('user.order.check.order_not_exist'));
        }
        return response([
            'data' => $order->status
        ]);
    }

    public function getPaymentMethod()
    {
        $methods = Payment::select([
            'id',
            'name',
            'payment'
        ])
            ->where('enable', 1)->get();

        return response([
            'data' => $methods
        ]);
    }

    public function cancel(Request $request)
    {
        if (empty($request->input('trade_no'))) {
            abort(500, __('user.order.cancel.params_wrong'));
        }
        $order = Order::where('trade_no', $request->input('trade_no'))
            ->where('user_id', $request->session()->get('id'))
            ->first();
        if (!$order) {
            abort(500, __('user.order.cancel.order_not_exist'));
        }
        if ($order->status !== 0) {
            abort(500, __('user.order.cancel.only_cancel_pending_order'));
        }
        $orderService = new OrderService($order);
        if (!$orderService->cancel()) {
            abort(500, __('user.order.cancel.cancel_failed'));
        }
        return response([
            'data' => true
        ]);
    }
}
