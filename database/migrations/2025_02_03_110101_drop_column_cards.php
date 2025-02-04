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
           $table->dropForeign(['会社履歴ID']);
           $table->dropColumn('会社履歴ID');
        });
    }

    /**
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->unsignedBigInteger('会社履歴ID')->nullable();
            $table->foreign('会社履歴ID')->references('id')->on('companyhistories')->delete('cascade');
        });
    }
};
