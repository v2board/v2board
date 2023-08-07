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
        Schema::create('v2_commission_log', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('invite_user_id');
            $table->integer('user_id');
            $table->char('trade_no', 36);
            $table->integer('order_amount');
            $table->integer('get_amount');
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
        Schema::dropIfExists('v2_commission_log');
    }
};
