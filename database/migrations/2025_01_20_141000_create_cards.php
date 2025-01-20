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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('名刺ユーザーID');
            $table->foreign('名刺ユーザーID')->references('id')->on('cardusers');
            $table->date('開始期間')->nullable();
            $table->date('終了期間')->nullable();
            $table->boolean('最新フラグ')->default(true);
            $table->string('名刺ファイル表');
            $table->string('名刺ファイル裏')->nullable();
            $table->unsignedBigInteger('会社ID');
            $table->foreign('会社ID')->references('id')->on('companies');
            $table->unsignedBigInteger('会社履歴ID');
            $table->foreign('会社履歴ID')->references('id')->on('companyhistories');
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
        Schema::dropIfExists('cards');
    }
};
