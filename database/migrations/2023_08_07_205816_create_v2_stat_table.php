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
        Schema::create('v2_stat', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('record_at')->unique('record_at');
            $table->char('record_type', 1);
            $table->integer('order_count')->comment('订单数量');
            $table->integer('order_total')->comment('订单合计');
            $table->integer('commission_count');
            $table->integer('commission_total')->comment('佣金合计');
            $table->integer('paid_count');
            $table->integer('paid_total');
            $table->integer('register_count');
            $table->integer('invite_count');
            $table->string('transfer_used_total', 32);
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
        Schema::dropIfExists('v2_stat');
    }
};
