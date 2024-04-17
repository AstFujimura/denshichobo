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
        Schema::create('t_optional_drafts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('フロー下書きテーブルID');
            $table->foreign('フロー下書きテーブルID')->references('id')->on('t_flow_drafts')->onDelete('cascade');
            $table->unsignedBigInteger('任意項目マスタID');
            $table->foreign('任意項目マスタID')->references('id')->on('m_optionals')->onDelete('cascade');
            $table->string('値');
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
        Schema::dropIfExists('t_optional_drafts');
    }
};
