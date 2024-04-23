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
            $table->string('標題');
            $table->unsignedBigInteger('フローマスタID')->nullable();
            $table->foreign('フローマスタID')->references('id')->on('m_flows')->onDelete('cascade');
            $table->integer('ステータス')->default(0);
            $table->integer('再承認番号')->default(1);
            $table->unsignedBigInteger('申請者ID');
            $table->foreign('申請者ID')->references('id')->on('users');
            $table->integer('過去データID');
            $table->integer('決裁数')->default(0);
            $table->integer('決裁地点数')->default(1);


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
