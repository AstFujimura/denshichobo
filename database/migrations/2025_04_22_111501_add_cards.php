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
        Schema::table('cards', function (Blueprint $table) {
            $table->unsignedBigInteger('拠点ID')->nullable();
            $table->foreign('拠点ID')->references('id')->on('branches');
        });

        // 会社テーブルから会社名、会社所在地、電話番号、FAX番号を取得して拠点テーブルに挿入
        $cards = DB::table('cards')->get();
        foreach ($cards as $card) {
            $company = DB::table('companies')->where('id', $card->会社ID)->first();
            $branch = DB::table('branches')->where('会社ID', $company->id)->first();
            if ($branch) {
                DB::table('cards')->where('id', $card->id)->update(['拠点ID' => $branch->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropForeign(['拠点ID']);
            $table->dropColumn('拠点ID');
        });
    }
};
