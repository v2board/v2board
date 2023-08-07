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
        Schema::create('v2_invite_code', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->char('code', 32);
            $table->boolean('status')->default(false);
            $table->integer('pv')->default(0);
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
        Schema::dropIfExists('v2_invite_code');
    }
};
