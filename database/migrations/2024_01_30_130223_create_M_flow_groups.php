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
        Schema::create('M_flow_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('フローマスタID');
            $table->unsignedBigInteger('グループID');
            $table->foreign('フローマスタID')->references('id')->on('M_flows');
            $table->foreign('グループID')->references('id')->on('groups');

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
        Schema::dropIfExists('M_flow_groups');
    }
};
