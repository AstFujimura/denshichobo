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
        Schema::create('card_department', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('名刺ID');
            $table->foreign('名刺ID')->references('id')->on('cards');
            $table->unsignedBigInteger('部署ID');
            $table->foreign('部署ID')->references('id')->on('departments');
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
        Schema::dropIfExists('card_department');
    }
};
