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
        Schema::create('v2_ticket', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->string('subject');
            $table->boolean('level');
            $table->boolean('status')->default(false)->comment('0:已开启 1:已关闭');
            $table->boolean('reply_status')->default(true)->comment('0:待回复 1:已回复');
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
        Schema::dropIfExists('v2_ticket');
    }
};
