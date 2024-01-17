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
        Schema::create('versions', function (Blueprint $table) {
            $table->id();
            $table->boolean('フロー')->default(false);
            $table->timestamps();
        });

        //マイグレーションファイルでデータの挿入は推奨されないがfilesテーブルでグループIDを作成する際にエラーが出るため
        // レコードの挿入
        DB::table('versions')->insert([
            'フロー' => false,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('versions');
    }
};
