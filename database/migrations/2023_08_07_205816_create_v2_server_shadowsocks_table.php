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
        Schema::create('v2_server_shadowsocks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('group_id');
            $table->string('route_id')->nullable();
            $table->integer('parent_id')->nullable();
            $table->string('tags')->nullable();
            $table->string('name');
            $table->string('rate', 11);
            $table->string('host');
            $table->string('port', 11);
            $table->integer('server_port');
            $table->string('cipher');
            $table->char('obfs', 11)->nullable();
            $table->string('obfs_settings')->nullable();
            $table->tinyInteger('show')->default(0);
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
        Schema::dropIfExists('v2_server_shadowsocks');
    }
};
