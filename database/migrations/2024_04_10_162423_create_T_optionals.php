<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_optionals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('フローテーブルID');
            $table->foreign('フローテーブルID')->references('id')->on('t_flows')->onDelete('cascade');
            $table->unsignedBigInteger('任意項目マスタID');
            $table->foreign('任意項目マスタID')->references('id')->on('m_optionals')->onDelete('cascade');
            $table->text('文字列')->nullable();
            $table->integer('数値')->nullable();
            $table->date('日付')->nullable();
            $table->string('ファイルパス')->nullable();
            $table->string('ファイル形式')->nullable();
            $table->boolean('bool')->nullable();
            $table->boolean('金額条件')->default(false);
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
        Schema::dropIfExists('t_optionals');
    }
};
