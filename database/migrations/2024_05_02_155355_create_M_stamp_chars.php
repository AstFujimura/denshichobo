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
        Schema::create('m_stamp_chars', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('はんこマスタID');
            $table->foreign('はんこマスタID')->references('id')->on('m_stamps')->onDelete('cascade');
            $table->string('文字');
            $table->decimal('top',10,5);
            $table->decimal('left',10,5);
            $table->integer('文字番号');

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
        Schema::dropIfExists('m_stamp_chars');
    }
};
