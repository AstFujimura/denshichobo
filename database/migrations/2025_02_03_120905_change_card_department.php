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
        Schema::table('card_department', function (Blueprint $table) {
            // 既存の外部キーを削除
            $table->dropForeign(['名刺ID']);
            $table->dropForeign(['部署ID']);

            // ON DELETE CASCADE を適用した外部キーを再作成
            $table->foreign('名刺ID')->references('id')->on('cards')->onDelete('cascade');
            $table->foreign('部署ID')->references('id')->on('departments')->onDelete('cascade');

            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('card_department', function (Blueprint $table) {
            // 追加した外部キーを削除
            $table->dropForeign(['名刺ID']);
            $table->dropForeign(['部署ID']);

            // 元の外部キーを再作成（CASCADEなし）
            $table->foreign('名刺ID')->references('id')->on('cards');
            $table->foreign('部署ID')->references('id')->on('departments');
        });
    }
};
