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
        Schema::create('m_categories', function (Blueprint $table) {
            $table->id();
            $table->string('カテゴリ名');
            $table->string('注釈')->nullable();
            $table->string('項目順')->nullable();

            $table->timestamps();
        });

        //マイグレーションファイルでデータの挿入は推奨されないが支払い承認と経費精算はデフォルトでidが1,2となるようにするため
        // レコードの挿入
        DB::table('m_categories')->insert([
            [
                'id' => 1,
                'カテゴリ名' => '支払い承認',
                '項目順' => '1_2_3_4_5_6',
            ],
            [
                'id' => 2,
                'カテゴリ名' => '経費精算',
                '項目順' => '7_8_9_10_11_12',
            ]
        ]
        );
        

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_categories');
    }
};
