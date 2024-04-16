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
        Schema::create('m_optionals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('カテゴリマスタID');
            $table->foreign('カテゴリマスタID')->references('id')->on('m_categories')->onDelete('cascade');
            $table->string('項目名');
            $table->integer('型');
            $table->boolean('必須');
            $table->integer('文字制限');
            $table->integer('条件1カラム')->nullable();
            $table->integer('条件1')->nullable();
            $table->integer('条件値1')->nullable();
            $table->integer('条件2カラム')->nullable();
            $table->integer('条件2')->nullable();
            $table->integer('条件値2')->nullable();

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
        Schema::dropIfExists('m_optionals');
    }
};
