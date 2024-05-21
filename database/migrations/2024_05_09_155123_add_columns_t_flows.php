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
        Schema::table('t_flows', function (Blueprint $table) {
            $table->boolean('承認印')->default(false);
            $table->string('変更前承認ファイルパス')->nullable();
            $table->string('変更後承認ファイルパス')->nullable();
            $table->unsignedBigInteger('カテゴリマスタID')->default(1);
            $table->foreign('カテゴリマスタID')->references('id')->on('m_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('t_flows', function (Blueprint $table) {

            $table->dropColumn('承認印');
            $table->dropColumn('変更前承認ファイルパス');
            $table->dropColumn('変更後承認ファイルパス');
            $table->dropForeign(['カテゴリマスタID']);
            $table->dropColumn('カテゴリマスタID');
        });
    }
};
