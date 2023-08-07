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
        Schema::create('v2_plan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('group_id');
            $table->integer('transfer_enable');
            $table->string('name');
            $table->integer('speed_limit')->nullable();
            $table->boolean('show')->default(false);
            $table->integer('sort')->nullable();
            $table->boolean('renew')->default(true);
            $table->text('content')->nullable();
            $table->integer('month_price')->nullable();
            $table->integer('quarter_price')->nullable();
            $table->integer('half_year_price')->nullable();
            $table->integer('year_price')->nullable();
            $table->integer('two_year_price')->nullable();
            $table->integer('three_year_price')->nullable();
            $table->integer('onetime_price')->nullable();
            $table->integer('reset_price')->nullable();
            $table->boolean('reset_traffic_method')->nullable();
            $table->integer('capacity_limit')->nullable();
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
        Schema::dropIfExists('v2_plan');
    }
};
