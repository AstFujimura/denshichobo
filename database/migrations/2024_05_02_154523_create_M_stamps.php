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
        Schema::create('m_stamps', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('ユーザーID');
            $table->foreign('ユーザーID')->references('id')->on('users')->onDelete('cascade');
            $table->integer('フォントサイズ')->default(16);
            $table->string('フォント')->default("毛筆体");
            $table->decimal('縦横比',10,2)->default(0.8);
            $table->string('ファイルパス');

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
        Schema::dropIfExists('m_stamps');
    }
};
