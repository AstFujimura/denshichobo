<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\User;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'astec',
            'email' => 'astec@ast-sys.co.jp',
            'password' => Hash::make('Astec0213'),
        ]);

        DB::table('documents')->insert([
            [
                '書類' => '請求書',
            ],
            [
                '書類' => '納品書',
            ],
            [
                '書類' => '見積書',
            ],
            // 他のレコードを追加...
        ]);
    }
}
