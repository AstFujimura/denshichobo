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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('会社ID');
            $table->foreign('会社ID')->references('id')->on('companies');
            $table->string('部署名');
            $table->unsignedBigInteger('上位部署ID')->nullable();
            $table->foreign('上位部署ID')->references('id')->on('departments');
            $table->string('部署電話番号')->nullable();
            $table->string('部署FAX番号')->nullable();
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
        Schema::dropIfExists('departments');
    }
};
