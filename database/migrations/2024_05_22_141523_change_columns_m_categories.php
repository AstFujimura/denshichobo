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
            $table->decimal('縦', 10, 5)->nullable()->change();
            $table->decimal('横', 10, 5)->nullable()->change();
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
            $table->dropColumn('縦')->nullable(false)->change();
            $table->dropColumn('横')->nullable(false)->change();
        });
    }
};
