<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_group_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('個人グループID');
            $table->foreign('個人グループID')->references('id')->on('schedule_groups')->onDelete('cascade');
            $table->unsignedBigInteger('ユーザーID');
            $table->foreign('ユーザーID')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('schedule_group_user');
    }
};
