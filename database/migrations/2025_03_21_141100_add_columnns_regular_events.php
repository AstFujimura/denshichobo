    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::table('regular_events', function (Blueprint $table) {
                $table->dropColumn('期限');
                $table->date('開始期間')->default(now());
                $table->date('終了期間')->default('2037-12-31');
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::table('regular_events', function (Blueprint $table) {
                $table->date('期限')->default('2037-12-31');
                $table->dropColumn('開始期間');
                $table->dropColumn('終了期間');
            });
        }
    };
