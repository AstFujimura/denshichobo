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
        Schema::create('t_flow_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('フローテーブルID');
            $table->foreign('フローテーブルID')->references('id')->on('t_flows')->onDelete('cascade');
            $table->unsignedBigInteger('フロー地点ID');
            $table->foreign('フロー地点ID')->references('id')->on('m_flow_points')->onDelete('cascade');
            $table->integer('承認移行ステータス')->default(-1);
            $table->integer('承認ステータス')->default(-1);

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
        Schema::dropIfExists('t_flow_points');
    }
};
