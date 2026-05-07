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
        Schema::create('card_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('名刺ID');
            $table->foreign('名刺ID')->references('id')->on('cards')->onDelete('cascade');
            $table->unsignedBigInteger('タグID');
            $table->foreign('タグID')->references('id')->on('tags')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['名刺ID', 'タグID']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('card_tag');
    }
};
