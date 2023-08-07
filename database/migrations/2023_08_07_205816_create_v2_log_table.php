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
        Schema::create('v2_log', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('title');
            $table->string('level', 11)->nullable();
            $table->string('host')->nullable();
            $table->string('uri');
            $table->string('method', 11);
            $table->text('data')->nullable();
            $table->string('ip', 128)->nullable();
            $table->text('context')->nullable();
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
        Schema::dropIfExists('v2_log');
    }
};
