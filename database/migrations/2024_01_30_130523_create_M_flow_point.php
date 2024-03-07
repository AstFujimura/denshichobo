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
        Schema::create('m_flow_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('フローマスタID');
            $table->foreign('フローマスタID')->references('id')->on('m_flows')->onDelete('cascade');
            $table->integer('承認移行ポイント')->default(1);
            $table->integer('承認ポイント')->default(1);
            $table->integer('個人グループ')->default(1);
            $table->boolean('承認可能状態')->default(true);
            $table->string('フロントエンド表示ポイント');
            $table->boolean('決裁地点')->default(false);
            $table->integer('母数')->default(0);

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
        Schema::dropIfExists('m_flow_points');
    }
};
