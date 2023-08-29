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
        User::create(
            [
                'name' => 'astec',
                'email' => 'astec@ast-sys.co.jp',
                'password' => Hash::make('Astec0213'),
            ],
            [
                'name' => 'ユーザー1',
                'email' => 'user@dafault.com',
                'password' => Hash::make('SaLe4ir3'),
            ]
        );

        DB::table('documents')->insert([
            [
                '書類' => '請求書',
                'check' => 'check',
                'order' => 1,
            ],
            [
                '書類' => '納品書',
                'check' => 'check',
                'order' => 2,
            ],
            [
                '書類' => '見積書',
                'check' => 'check',
                'order' => 3,
            ],
            [
                '書類' => '契約書',
                'check' => 'check',
                'order' => 4,
            ],
            [
                '書類' => '領収書',
                'check' => 'check',
                'order' => 5,
            ],
            [
                '書類' => '検収書',
                'check' => 'check',
                'order' => 6,
            ],
            [
                '書類' => '注文書',
                'check' => 'check',
                'order' => 7,
            ],
        ]);
    }
}
