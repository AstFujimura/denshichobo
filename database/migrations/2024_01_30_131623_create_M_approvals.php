<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('M_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('フロー地点ID');
            $table->unsignedBigInteger('ユーザーID')->nullable();
            $table->unsignedBigInteger('グループID')->nullable();
            $table->unsignedBigInteger('役職ID')->nullable();
            $table->foreign('フロー地点ID')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('グループID')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('役職ID')->references('id')->on('positions')->onDelete('cascade');

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
        Schema::dropIfExists('M_approvals');
    }
};
