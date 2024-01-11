<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Document;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'astec',
                'email' => 'astec@ast-sys.co.jp',
                'password' => Hash::make('Astecakira0813'),
            ],
            [
                'name' => 'ユーザー1',
                'email' => 'user@dafault.com',
                'password' => Hash::make('SaLe4ir3'),
            ]
        ]
        );
        DB::table('groups')->insert([
            [
                'id' => '1',
                'グループ名' => 'astec(固有グループ名ghdF4ol)'
            ],
            [
                'id' => '2',
                'グループ名' => '管理者(固有グループ名ghdF4ol)'
            ]
        ]
        );

        DB::table('group_user')->insert([
            [
                'グループID' => 1,
                'ユーザーID' => 1,
            ],
            [
                'グループID' => 2,
                'ユーザーID' => 2,
            ]
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
                'check' => '',
                'order' => 4,
            ],
            [
                '書類' => '領収書',
                'check' => '',
                'order' => 5,
            ],
            [
                '書類' => '検収書',
                'check' => '',
                'order' => 6,
            ],
            [
                '書類' => '注文書',
                'check' => '',
                'order' => 7,
            ],
        ]);
    }
}
