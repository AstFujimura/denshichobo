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
        Schema::create('companyhistories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('会社ID');
            $table->foreign('会社ID')->references('id')->on('companies');
            $table->string('現会社名');
            $table->integer('履歴番号')->default(1);
            $table->boolean('最新フラグ')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companyhistories');
    }
};
