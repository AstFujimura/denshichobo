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
            $table->dropForeign(['上位部署ID']);
            $table->dropColumn('上位部署ID');
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
            $table->unsignedBigInteger('上位部署ID')->nullable();
            $table->foreign('上位部署ID')->references('id')->on('departments')->onDelete('cascade');
        });
    }
};
