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
        Schema::create('v2_knowledge', function (Blueprint $table) {
            $table->integer('id', true);
            $table->char('language', 5)->comment('語言');
            $table->string('category')->comment('分類名');
            $table->string('title')->comment('標題');
            $table->text('body')->comment('內容');
            $table->integer('sort')->nullable()->comment('排序');
            $table->boolean('show')->default(false)->comment('顯示');
            $table->integer('created_at')->comment('創建時間');
            $table->integer('updated_at')->comment('更新時間');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_knowledge');
    }
};
