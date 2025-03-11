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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->datetime('開始');
            $table->datetime('終了');
            $table->boolean('開始時間指定')->default(true);
            $table->boolean('終了時間指定')->default(true);
            $table->unsignedBigInteger('予定ID');
            $table->foreign('予定ID')->references('id')->on('plans')->onDelete('cascade');
            $table->string('予定詳細');
            $table->text('メモ')->nullable();
            $table->unsignedBigInteger('施設ID');
            $table->foreign('施設ID')->references('id')->on('facilities')->onDelete('cascade');
            $table->unsignedBigInteger('定期イベントID')->nullable();
            $table->foreign('定期イベントID')->references('id')->on('regular_events')->onDelete('cascade');
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
        Schema::dropIfExists('events');
    }
};
