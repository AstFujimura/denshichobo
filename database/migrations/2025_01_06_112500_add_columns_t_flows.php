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
            $table->decimal('縦', 10, 5)->nullable();
            $table->decimal('横', 10, 5)->nullable();
            $table->string('縦横')->nullable();
            $table->boolean('発行')->default(true);
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
            $table->dropColumn('縦');
            $table->dropColumn('横');
            $table->dropColumn('縦横');
            $table->dropColumn('発行');
        });
    }
};
