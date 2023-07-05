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
            // 運転者カラムを削除
            $table->dropColumn('運転者');

            // 運転者コードカラムを追加し、userテーブルのidに外部キー制約を追加
            $table->unsignedBigInteger('運転者コード')->nullable();
            $table->foreign('運転者コード')->references('id')->on('users');
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
            Schema::table('your_table_name', function (Blueprint $table) {
                // 外部キー制約を削除
                $table->dropForeign(['運転者コード']);
    
                // 運転者コードカラムを削除し、運転者カラムを再追加
                $table->dropColumn('運転者コード');
                $table->string('運転者');
        });
    });
    }
};

