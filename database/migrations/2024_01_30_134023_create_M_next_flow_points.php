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
        Schema::create('m_next_flow_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('フローマスタID');
            $table->unsignedBigInteger('現フロー地点ID');
            $table->foreign('フローマスタID')->references('id')->on('m_flows')->onDelete('cascade');
            $table->foreign('現フロー地点ID')->references('id')->on('m_flow_points')->onDelete('cascade');
            $table->string('次フロントエンド表示ポイント');

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
        Schema::dropIfExists('m_next_flow_points');
    }
};
