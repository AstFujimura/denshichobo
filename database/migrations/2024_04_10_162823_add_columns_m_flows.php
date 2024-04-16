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
        Schema::table('m_flows', function (Blueprint $table) {
            $table->unsignedBigInteger('カテゴリマスタID')->default(1);
            $table->foreign('カテゴリマスタID')->references('id')->on('m_categories')->onDelete('cascade');
            $table->string('項目順')->nullable();
            $table->string('標題')->nullable();
            $table->string('取引先')->nullable();
            $table->string('取引日')->nullable();
            $table->string('金額')->nullable();
            $table->string('コメント')->nullable();
            $table->string('請求書')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_flows', function (Blueprint $table) {
            $table->dropForeign(['カテゴリマスタID']);
            $table->dropColumn('カテゴリマスタID');
            $table->dropColumn('項目順');
            $table->dropColumn('標題');
            $table->dropColumn('取引先');
            $table->dropColumn('取引日');
            $table->dropColumn('金額');
            $table->dropColumn('コメント');
            $table->dropColumn('請求書');
        });
    }
};
