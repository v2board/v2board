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
        Schema::create('v2_user', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('invite_user_id')->nullable();
            $table->bigInteger('telegram_id')->nullable();
            $table->string('email', 64)->unique('email');
            $table->string('password', 64);
            $table->char('password_algo', 10)->nullable();
            $table->char('password_salt', 10)->nullable();
            $table->integer('balance')->default(0);
            $table->integer('discount')->nullable();
            $table->tinyInteger('commission_type')->default(0)->comment('0: system 1: period 2: onetime');
            $table->integer('commission_rate')->nullable();
            $table->integer('commission_balance')->default(0);
            $table->integer('t')->default(0);
            $table->bigInteger('u')->default(0);
            $table->bigInteger('d')->default(0);
            $table->bigInteger('transfer_enable')->default(0);
            $table->boolean('banned')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->integer('last_login_at')->nullable();
            $table->boolean('is_staff')->default(false);
            $table->integer('last_login_ip')->nullable();
            $table->string('uuid', 36);
            $table->integer('group_id')->nullable();
            $table->integer('plan_id')->nullable();
            $table->integer('speed_limit')->nullable();
            $table->tinyInteger('remind_expire')->nullable()->default(1);
            $table->tinyInteger('remind_traffic')->nullable()->default(1);
            $table->char('token', 32);
            $table->bigInteger('expired_at')->nullable()->default(0);
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('v2_user');
    }
};
