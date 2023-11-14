
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
        // テーブルの作成
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('グループ名');
            $table->timestamps();
        });

        //マイグレーションファイルでデータの挿入は推奨されないがfilesテーブルでグループIDを作成する際にエラーが出るため
        // レコードの挿入
        DB::table('groups')->insert([
            'id' => 100000,
            'グループ名' => '指定なし'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groups');
    }
};