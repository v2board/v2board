<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_order', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('invite_user_id')->nullable();
            $table->integer('user_id');
            $table->integer('plan_id');
            $table->integer('coupon_id')->nullable();
            $table->integer('payment_id')->nullable();
            $table->integer('type')->comment('1新购2续费3升级');
            $table->string('period');
            $table->string('trade_no', 36)->unique('trade_no');
            $table->string('callback_no')->nullable();
            $table->integer('total_amount');
            $table->integer('handling_amount')->nullable();
            $table->integer('discount_amount')->nullable();
            $table->integer('surplus_amount')->nullable()->comment('剩余价值');
            $table->integer('refund_amount')->nullable()->comment('退款金额');
            $table->integer('balance_amount')->nullable()->comment('使用余额');
            $table->text('surplus_order_ids')->nullable()->comment('折抵订单');
            $table->boolean('status')->default(false)->comment('0待支付1开通中2已取消3已完成4已折抵');
            $table->boolean('commission_status')->default(false)->comment('0待确认1发放中2有效3无效');
            $table->integer('commission_balance')->default(0);
            $table->integer('actual_commission_balance')->nullable()->comment('实际支付佣金');
            $table->integer('paid_at')->nullable();
            $table->integer('created_at');
            $table->integer('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_order');
    }
};
