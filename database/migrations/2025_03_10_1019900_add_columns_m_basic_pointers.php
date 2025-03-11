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
        Schema::create('m_basic_pointers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('カテゴリマスタID');
            $table->foreign('カテゴリマスタID')->references('id')->on('m_categories')->onDelete('cascade');
            $table->integer('基本情報');
            $table->decimal('top', 10, 5);
            $table->decimal('left', 10, 5);
            $table->integer('フォントサイズ')->default(16);
            $table->string('フォント')->nullable();
            $table->integer('ページ')->default(1);
            $table->timestamps();
        });

        // AUTO_INCREMENT の開始値を変更
        DB::statement('ALTER TABLE m_basic_pointers AUTO_INCREMENT = 10000;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_basic_pointers');
    }
};
