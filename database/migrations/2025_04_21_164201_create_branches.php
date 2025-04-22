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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('会社ID');
            $table->foreign('会社ID')->references('id')->on('companies');
            $table->string('拠点名');
            $table->string('拠点所在地')->nullable();
            $table->string('電話番号')->nullable();
            $table->string('FAX番号')->nullable();
            $table->boolean('拠点指定')->default(false);
            $table->timestamps();
        });

        // 会社テーブルから会社名、会社所在地、電話番号、FAX番号を取得して拠点テーブルに挿入
        $companies = DB::table('companies')->get();
        foreach ($companies as $company) {
            DB::table('branches')->insert([
                '会社ID' => $company->id,
                '拠点名' => $company->会社名,
                '拠点所在地' => $company->会社所在地,
                '電話番号' => $company->電話番号,
                'FAX番号' => $company->FAX番号,
                '拠点指定' => false,
            ]);
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('会社所在地');
            $table->dropColumn('電話番号');
            $table->dropColumn('FAX番号');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('会社所在地')->nullable();
            $table->string('電話番号')->nullable();
            $table->string('FAX番号')->nullable();
        });
        Schema::dropIfExists('branches');
    }
};
