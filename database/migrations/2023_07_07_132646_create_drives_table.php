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
        Schema::table('files', function (Blueprint $table) {
            // カラムの追加
            $table->string('備考')->nullable();
            $table->integer('バージョン')->default(1);
            $table->string('ファイル形式')->default('.pdf');
            $table->string('ファイル変更')->default('あり');
            

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('files', function (Blueprint $table) {
            // カラムの追加のロールバック
            $table->dropColumn('備考');
            $table->dropColumn('バージョン');
            $table->dropColumn('ファイル形式');
            $table->dropColumn('ファイル変更');
        
        });
    }
};
