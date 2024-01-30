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
        Schema::create('M_next_flow_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('現フロー地点ID');
            $table->foreign('現フロー地点ID')->references('id')->on('M_flow_points')->onDelete('cascade');
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
        Schema::dropIfExists('M_next_flow_points');
    }
};
