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
        Schema::create('m_pointers', function (Blueprint $table) {
            $table->id();            
            $table->unsignedBigInteger('カテゴリマスタID');
            $table->foreign('カテゴリマスタID')->references('id')->on('m_categories')->onDelete('cascade');
            $table->unsignedBigInteger('任意項目マスタID');
            $table->foreign('任意項目マスタID')->references('id')->on('m_optionals')->onDelete('cascade');
            $table->decimal('top', 10, 5);
            $table->decimal('left', 10, 5);
            $table->integer('フォントサイズ')->default(16);
            $table->string('フォント')->nullable();
            $table->integer('ページ')->default(1);

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
        Schema::dropIfExists('m_pointers');
    }
};
