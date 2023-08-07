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
        Schema::create('v2_server_trojan', function (Blueprint $table) {
            $table->integer('id', true)->comment('节点ID');
            $table->string('group_id')->comment('节点组');
            $table->string('route_id')->nullable();
            $table->integer('parent_id')->nullable()->comment('父节点');
            $table->string('tags')->nullable()->comment('节点标签');
            $table->string('name')->comment('节点名称');
            $table->string('rate', 11)->comment('倍率');
            $table->string('host')->comment('主机名');
            $table->string('port', 11)->comment('连接端口');
            $table->integer('server_port')->comment('服务端口');
            $table->boolean('allow_insecure')->default(false)->comment('是否允许不安全');
            $table->string('server_name')->nullable();
            $table->boolean('show')->default(false)->comment('是否显示');
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
        Schema::dropIfExists('v2_server_trojan');
    }
};
