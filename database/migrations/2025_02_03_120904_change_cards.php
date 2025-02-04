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
        Schema::table('cards', function (Blueprint $table) {
            // 既存の外部キーを削除
            $table->dropForeign(['名刺ユーザーID']);
            $table->dropForeign(['会社ID']);

            // ON DELETE CASCADE を適用した外部キーを再作成
            $table->foreign('名刺ユーザーID')->references('id')->on('cardusers')->onDelete('cascade');
            $table->foreign('会社ID')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cards', function (Blueprint $table) {
            // 追加した外部キーを削除
            $table->dropForeign(['名刺ユーザーID']);
            $table->dropForeign(['会社ID']);
            $table->foreign('会社ID')->references('id')->on('companies');
            $table->foreign('名刺ユーザーID')->references('id')->on('cardusers');
        });
    }
};
