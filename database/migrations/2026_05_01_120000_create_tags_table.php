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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ユーザーID');
            $table->foreign('ユーザーID')->references('id')->on('users')->onDelete('cascade');
            $table->string('タグ名');
            $table->string('カラーコード', 32);
            $table->boolean('非公開')->default(false);
            $table->timestamps();

            $table->unique(['ユーザーID', 'タグ名']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags');
    }
};
