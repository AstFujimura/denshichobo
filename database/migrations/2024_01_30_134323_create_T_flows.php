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
        Schema::create('t_flows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('フローマスタID');
            $table->foreign('フローマスタID')->references('id')->on('m_flows');
            $table->integer('ステータス')->default(1);
            $table->string('ファイルパス');
            $table->string('取引先');
            $table->integer('金額');
            $table->date('日付');
            $table->integer('再承認番号');


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
        Schema::dropIfExists('t_flows');
    }
};
