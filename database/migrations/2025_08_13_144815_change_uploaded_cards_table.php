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
        Schema::table('uploaded_cards', function (Blueprint $table) {
            $table->dropColumn('front_url');
            $table->dropColumn('back_url');
            $table->dropColumn('status');
            $table->dropColumn('openai_response');
            $table->integer('表')->default(0);
            $table->integer('裏')->default(0);
            $table->integer('ステータス')->default(0);
            $table->string('エラーメッセージ')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('uploaded_cards', function (Blueprint $table) {
            $table->text('front_url')->nullable();
            $table->text('back_url')->nullable();
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending'); // 状態管理
            $table->text('openai_response')->nullable(); // OpenAIからの結果（あれば）
            $table->dropColumn('表');
            $table->dropColumn('裏');
            $table->dropColumn('ステータス');
            $table->dropColumn('エラーメッセージ');
        });
    }
};
