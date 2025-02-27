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
        Schema::create('m_tameru_regist', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('カテゴリマスタID');
                $table->foreign('カテゴリマスタID')->references('id')->on('m_categories')->onDelete('cascade');
                $table->unsignedBigInteger('ファイル');
                $table->foreign('ファイル')->references('id')->on('m_optionals')->onDelete('cascade');
                $table->unsignedBigInteger('取引日');
                $table->foreign('取引日')->references('id')->on('m_optionals')->onDelete('cascade');
                $table->unsignedBigInteger('金額');
                $table->foreign('金額')->references('id')->on('m_optionals')->onDelete('cascade');
                $table->unsignedBigInteger('取引先');
                $table->foreign('取引先')->references('id')->on('m_optionals')->onDelete('cascade');
                $table->unsignedBigInteger('書類区分');
                $table->foreign('書類区分')->references('id')->on('documents')->onDelete('cascade');
                $table->integer('提出');
                $table->integer('保存方法');
                $table->text('検索ワード')->nullable();
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_tameru_regist');
    }
};
