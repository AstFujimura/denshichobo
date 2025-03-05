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
        Schema::create('m_optionals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('カテゴリマスタID');
            $table->foreign('カテゴリマスタID')->references('id')->on('m_categories')->onDelete('cascade');
            $table->string('項目名');
            $table->integer('型');
            $table->boolean('必須');
            $table->integer('最大')->nullable();
            $table->integer('条件1カラム')->nullable();
            $table->integer('条件1')->nullable();
            $table->integer('条件値1')->nullable();
            $table->integer('条件2カラム')->nullable();
            $table->integer('条件2')->nullable();
            $table->integer('条件値2')->nullable();
            $table->boolean('デフォルト')->default(false);
            $table->boolean('金額条件')->default(false);

            $table->timestamps();
        });

        //マイグレーションファイルでデータの挿入は推奨されないが支払い承認と経費精算のデフォルトを作成する
        // レコードの挿入
        DB::table('m_optionals')->insert([
            [
                'id' => 1,
                'カテゴリマスタID' => 1,
                '項目名' => '標題',
                '型' => 1,
                '必須' => true,
                '最大' => 30,
                'デフォルト' => true,
                '金額条件' => false,
            ],
            [
                'id' => 2,
                'カテゴリマスタID' => 1,
                '項目名' => '取引先',
                '型' => 1,
                '必須' => true,
                '最大' => 30,
                'デフォルト' => false,
                '金額条件' => false,
            ],
            [
                'id' => 3,
                'カテゴリマスタID' => 1,
                '項目名' => '取引日',
                '型' => 3,
                '必須' => true,
                '最大' => null,
                'デフォルト' => false,
                '金額条件' => false,
            ],
            [
                'id' => 4,
                'カテゴリマスタID' => 1,
                '項目名' => '金額',
                '型' => 2,
                '必須' => true,
                '最大' => 2000000000,
                'デフォルト' => false,
                '金額条件' => true,
            ],
            [
                'id' => 5,
                'カテゴリマスタID' => 1,
                '項目名' => 'コメント',
                '型' => 1,
                '必須' => true,
                '最大' => 250,
                'デフォルト' => false,
                '金額条件' => false,
            ],
            [
                'id' => 6,
                'カテゴリマスタID' => 1,
                '項目名' => '請求書',
                '型' => 4,
                '必須' => true,
                '最大' => null,
                'デフォルト' => false,
                '金額条件' => false,
            ],
            [
                'id' => 7,
                'カテゴリマスタID' => 2,
                '項目名' => '標題',
                '型' => 1,
                '必須' => true,
                '最大' => 30,
                'デフォルト' => true,
                '金額条件' => false,
            ],
            [
                'id' => 8,
                'カテゴリマスタID' => 2,
                '項目名' => '取引先',
                '型' => 1,
                '必須' => true,
                '最大' => 30,
                'デフォルト' => false,
                '金額条件' => false,
            ],
            [
                'id' => 9,
                'カテゴリマスタID' => 2,
                '項目名' => '取引日',
                '型' => 3,
                '必須' => true,
                '最大' => null,
                'デフォルト' => false,
                '金額条件' => false,
            ],
            [
                'id' => 10,
                'カテゴリマスタID' => 2,
                '項目名' => '金額',
                '型' => 2,
                '必須' => true,
                '最大' => 2000000000,
                'デフォルト' => false,
                '金額条件' => true,
            ],
            [
                'id' => 11,
                'カテゴリマスタID' => 2,
                '項目名' => 'コメント',
                '型' => 1,
                '必須' => true,
                '最大' => 250,
                'デフォルト' => false,
                '金額条件' => false,
            ],
            [
                'id' => 12,
                'カテゴリマスタID' => 2,
                '項目名' => '請求書',
                '型' => 4,
                '必須' => true,
                '最大' => null,
                'デフォルト' => false,
                '金額条件' => false,
            ],
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
        Schema::dropIfExists('m_optionals');
    }
};
