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
        Schema::create('regular_event_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('定期イベントID');
                $table->foreign('定期イベントID')->references('id')->on('regular_events')->onDelete('cascade');
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
        Schema::dropIfExists('regular_event_user');
    }
};
