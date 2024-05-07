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
            $table->string('ファイルパス')->nullable();
            $table->boolean('発行')->default(false);
            $table->decimal('縦', 10, 5);
            $table->decimal('横', 10, 5);
            $table->string('縦横')->nullable();
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
            $table->dropColumn('ファイルパス');
            $table->dropColumn('発行');
            $table->dropColumn('縦');
            $table->dropColumn('横');
            $table->dropColumn('縦横');
        });
    }
};
