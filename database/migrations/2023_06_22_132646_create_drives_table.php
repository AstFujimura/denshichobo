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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->integer('日付');
            $table->string('取引先');
            $table->string('金額');
            //2023_08/03提出カラム追加defaultをとりあえず空にしている
            $table->string('提出')->default("");
            $table->unsignedBigInteger('保存者ID');
            $table->string('ファイルパス');
            $table->foreign('保存者ID')->references('id')->on('users');
            $table->timestamp('updated_at')->useCurrent()->nullable();
            $table->timestamp('created_at')->useCurrent()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
};
