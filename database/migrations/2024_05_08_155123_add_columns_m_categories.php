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
        Schema::table('m_categories', function (Blueprint $table) {
            $table->boolean('承認印')->default(false);
            $table->boolean('申請印')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_categories', function (Blueprint $table) {
            $table->dropColumn('承認印');
            $table->dropColumn('申請印');
        });
    }
};
