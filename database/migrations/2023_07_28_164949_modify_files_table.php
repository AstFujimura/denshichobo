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

            $table->string('訂正許可')->default("有効");
            $table->string('削除許可')->default("有効");
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
            $table->dropColumn('訂正許可');
            $table->dropColumn('削除許可');
        });
    }
};
