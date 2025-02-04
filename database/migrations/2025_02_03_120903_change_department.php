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
        Schema::table('departments', function (Blueprint $table) {
            // 既存の外部キーを削除
            $table->dropForeign(['会社ID']);
            $table->dropForeign(['上位部署ID']);

            // ON DELETE CASCADE を適用した外部キーを再作成
            $table->foreign('会社ID')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('上位部署ID')->references('id')->on('departments')->onDelete('cascade');

            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            // 追加した外部キーを削除
            $table->dropForeign(['会社ID']);
            $table->dropForeign(['上位部署ID']);

            // 元の外部キーを再作成（CASCADEなし）
            $table->foreign('会社ID')->references('id')->on('companies');
            $table->foreign('上位部署ID')->references('id')->on('departments');
        });
    }
};
