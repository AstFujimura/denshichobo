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
        Schema::create('t_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('フローテーブルID');
            $table->unsignedBigInteger('フロー地点テーブルID');
            $table->foreign('フローテーブルID')->references('id')->on('t_flows')->onDelete('cascade');
            $table->foreign('フロー地点テーブルID')->references('id')->on('t_flow_points')->onDelete('cascade');
            $table->integer('ステータス')->default(1);
            $table->text('コメント')->nullable();
            $table->unsignedBigInteger('ユーザーID');
            $table->foreign('ユーザーID')->references('id')->on('users');

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
        Schema::dropIfExists('t_approvals');
    }
};
