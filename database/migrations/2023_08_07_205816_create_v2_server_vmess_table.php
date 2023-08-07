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
        Schema::create('v2_server_vmess', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('group_id');
            $table->string('route_id')->nullable();
            $table->string('name');
            $table->integer('parent_id')->nullable();
            $table->string('host');
            $table->string('port', 11);
            $table->integer('server_port');
            $table->tinyInteger('tls')->default(0);
            $table->string('tags')->nullable();
            $table->string('rate', 11);
            $table->string('network', 11);
            $table->text('rules')->nullable();
            $table->text('networkSettings')->nullable();
            $table->text('tlsSettings')->nullable();
            $table->text('ruleSettings')->nullable();
            $table->text('dnsSettings')->nullable();
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
        Schema::dropIfExists('v2_server_vmess');
    }
};
