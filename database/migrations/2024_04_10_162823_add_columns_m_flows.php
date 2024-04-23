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
        });
    }
};
