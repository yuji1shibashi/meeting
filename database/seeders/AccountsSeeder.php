<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('accounts')->insert([
            [
                'name' => '知苑謙一',
                'email' => 'kenichi.chien@fox-hound.jp',
                'password' => Hash::make('password'),
                'role' => 1,
                'slackID' => NULL,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => '石橋祐治',
                'email' => 'yuji.ishibashi@fox-hound.jp',
                'password' => Hash::make('aaaa1111'),
                'role' => 1,
                'slackID' => 'UABB26W81',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => '伏田慶二郎',
                'email' => 'keijiro.hushida@fox-hound.jp',
                'password' => Hash::make('password'),
                'role' => 1,
                'slackID' => NULL,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => '佐藤駿',
                'email' => 'shun.sato@fox-hound.tech',
                'password' => Hash::make('password'),
                'role' => 1,
                'slackID' => NULL,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => '猛虎野郎',
                'email' => 'mouko@fox-hound.tech',
                'password' => NULL,
                'role' => 0,
                'slackID' => NULL,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => '難波花月',
                'email' => 'nanba@fox-hound.tech',
                'password' => NULL,
                'role' => 0,
                'slackID' => NULL,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => '道頓堀飛雄',
                'email' => 'doutonbori@fox-hound.tech',
                'password' => NULL,
                'role' => 0,
                'slackID' => NULL,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => '四条祇園',
                'email' => 'shijo@fox-hound.tech',
                'password' => NULL,
                'role' => 0,
                'slackID' => NULL,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
        ]);
    }
}
