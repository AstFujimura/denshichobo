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
            $table->unsignedBigInteger('ユーザーID')->nullable();
            $table->foreign('ユーザーID')->references('id')->on('users')->onDelete('cascade');
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
            $table->dropForeign(['ユーザーID']);
            $table->dropColumn('ユーザーID');
        });
    }
};
