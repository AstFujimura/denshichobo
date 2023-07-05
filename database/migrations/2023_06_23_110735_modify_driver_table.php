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
        Schema::table('drives', function (Blueprint $table) {
            // カラムの追加
            $table->string('訪問先')->default("");
            $table->integer('給油')->nullable();
            $table->string('SS')->nullable();
            $table->string('高速')->nullable();
            

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drives', function (Blueprint $table) {
            // カラムの追加のロールバック
            $table->dropColumn('訪問先');
            $table->dropColumn('給油');
            $table->dropColumn('SS');
            $table->dropColumn('高速');
        
        });
    }
};
