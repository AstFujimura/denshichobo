<?php

use Illuminate\Database\Migrations\Migration;
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
        // 'back_pending' を追加
        DB::statement("ALTER TABLE uploaded_cards MODIFY status ENUM('pending', 'processing', 'done', 'failed', 'back_pending') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 'back_pending' を削除（元に戻す）
        DB::statement("ALTER TABLE uploaded_cards MODIFY status ENUM('pending', 'processing', 'done', 'failed') DEFAULT 'pending'");
    }
};
