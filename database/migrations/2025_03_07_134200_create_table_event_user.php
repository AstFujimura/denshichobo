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
        Schema::create('event_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('イベントID');
                $table->foreign('イベントID')->references('id')->on('events')->onDelete('cascade');
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
        Schema::dropIfExists('event_user');
    }
};
