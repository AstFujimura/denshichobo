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
        Schema::create('uploaded_cards', function (Blueprint $table) {
            $table->id();
            $table->text('front_url')->nullable();
            $table->text('back_url')->nullable();
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending'); // 状態管理
            $table->text('openai_response')->nullable(); // OpenAIからの結果（あれば）
            $table->uuid('upload_id');
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
        Schema::dropIfExists('uploaded_cards');
    }
};
