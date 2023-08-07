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
        Schema::create('v2_coupon', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('code');
            $table->string('name');
            $table->boolean('type');
            $table->integer('value');
            $table->boolean('show')->default(false);
            $table->integer('limit_use')->nullable();
            $table->integer('limit_use_with_user')->nullable();
            $table->string('limit_plan_ids')->nullable();
            $table->string('limit_period')->nullable();
            $table->integer('started_at');
            $table->integer('ended_at');
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
        Schema::dropIfExists('v2_coupon');
    }
};
