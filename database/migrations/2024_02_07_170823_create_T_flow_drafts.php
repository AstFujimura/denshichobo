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
        Schema::create('t_flow_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('標題');
            $table->text('コメント');
            $table->unsignedBigInteger('フローマスタID')->nullable();
            $table->string('ファイルパス');
            $table->string('取引先');
            $table->integer('金額');
            $table->date('日付');
            $table->unsignedBigInteger('申請者ID');
            $table->foreign('申請者ID')->references('id')->on('users');
            $table->integer('過去データID');
            $table->string('ファイル形式');


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
        Schema::dropIfExists('t_flow_drafts');
    }
};
