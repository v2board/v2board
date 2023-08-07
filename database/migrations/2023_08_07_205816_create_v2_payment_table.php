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
        Schema::create('v2_payment', function (Blueprint $table) {
            $table->integer('id', true);
            $table->char('uuid', 32);
            $table->string('payment', 16);
            $table->string('name');
            $table->string('icon')->nullable();
            $table->text('config');
            $table->string('notify_domain', 128)->nullable();
            $table->integer('handling_fee_fixed')->nullable();
            $table->decimal('handling_fee_percent', 5)->nullable();
            $table->boolean('enable')->default(false);
            $table->integer('sort')->nullable();
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
        Schema::dropIfExists('v2_payment');
    }
};
