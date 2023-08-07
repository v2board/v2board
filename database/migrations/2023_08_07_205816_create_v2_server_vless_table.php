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
        Schema::create('v2_server_vless', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('group_id');
            $table->text('route_id')->nullable();
            $table->string('name');
            $table->integer('parent_id')->nullable();
            $table->string('host');
            $table->integer('port');
            $table->integer('server_port');
            $table->boolean('tls');
            $table->text('tls_settings')->nullable();
            $table->string('flow', 64)->nullable();
            $table->string('network', 11);
            $table->text('network_settings')->nullable();
            $table->text('tags')->nullable();
            $table->string('rate', 11);
            $table->boolean('show')->default(false);
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
        Schema::dropIfExists('v2_server_vless');
    }
};
