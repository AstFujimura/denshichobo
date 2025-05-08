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
        Schema::create('carduser_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('名刺ユーザーID');
            $table->foreign('名刺ユーザーID')->references('id')->on('cardusers')->onDelete('cascade');
            $table->unsignedBigInteger('ユーザーID');
            $table->foreign('ユーザーID')->references('id')->on('users')->onDelete('cascade');
            $table->boolean('マイ名刺ユーザー')->default(false);
            $table->boolean('お気に入りユーザー')->default(false);
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
        Schema::dropIfExists('carduser_user');
    }
};
