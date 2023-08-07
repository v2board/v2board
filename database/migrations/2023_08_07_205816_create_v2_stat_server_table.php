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
        Schema::create('v2_stat_server', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('server_id')->index('server_id')->comment('节点id');
            $table->char('server_type', 11)->comment('节点类型');
            $table->bigInteger('u');
            $table->bigInteger('d');
            $table->char('record_type', 1)->comment('d day m month');
            $table->integer('record_at')->index('record_at')->comment('记录时间');
            $table->integer('created_at');
            $table->integer('updated_at');

            $table->unique(['server_id', 'server_type', 'record_at'], 'server_id_server_type_record_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_stat_server');
    }
};
