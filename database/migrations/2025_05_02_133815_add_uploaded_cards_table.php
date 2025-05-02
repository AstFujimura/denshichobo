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
            $table->string('ファイル名')->nullable();
            $table->unsignedBigInteger('名刺ID')->nullable();
            $table->foreign('名刺ID')->references('id')->on('cards')->onDelete('cascade');
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
            $table->dropColumn('ファイル名');
            $table->dropColumn('名刺ID');
        });
    }
};
