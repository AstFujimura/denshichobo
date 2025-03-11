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
        Schema::create('regular_events', function (Blueprint $table) {
                $table->id();
                $table->integer('頻度');
                $table->date('期限')->default('2037-12-31');
                $table->integer('曜日')->nullable();
                $table->integer('日付')->nullable();
                $table->integer('週番号')->nullable();
                $table->datetime('開始');
                $table->datetime('終了');
                $table->boolean('開始時間指定')->default(true);
                $table->boolean('終了時間指定')->default(true);
                $table->unsignedBigInteger('予定ID');
                $table->foreign('予定ID')->references('id')->on('plans')->onDelete('cascade');
                $table->string('予定詳細');
                $table->unsignedBigInteger('施設ID');
                $table->text('メモ')->nullable();
                $table->foreign('施設ID')->references('id')->on('facilities')->onDelete('cascade');
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
        Schema::dropIfExists('regular_events');
    }
};
