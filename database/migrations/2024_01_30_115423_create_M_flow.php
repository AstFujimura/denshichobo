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
        Schema::create('m_flows', function (Blueprint $table) {
            $table->id();
            $table->string('フロー名');
            $table->boolean('削除フラグ')->default(false);
            $table->boolean('グループ条件')->default(true);
            $table->integer('金額下限条件')->default(0);
            $table->integer('金額上限条件')->default(2000000000);
            $table->boolean('承認可能状態')->default(false);
            $table->boolean('決裁地点数');
            $table->text('フロントエンドオブジェクト');
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
        Schema::dropIfExists('m_flows');
    }
};
