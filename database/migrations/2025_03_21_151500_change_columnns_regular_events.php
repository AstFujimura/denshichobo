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
        Schema::table('regular_events', function (Blueprint $table) {
            $table->time('開始')->default('00:00:00')->change();
            $table->time('終了')->default('23:59:59')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('regular_events', function (Blueprint $table) {
            $table->dropColumn(['開始', '終了']);
        });

        Schema::table('regular_events', function (Blueprint $table) {
            $table->datetime('開始');
            $table->datetime('終了');
        });
    }
};
