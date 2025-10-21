<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RailwayOperator;

class RailwayOperatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operators = [
            [
                'name' => 'JR西日本',
                'slug' => 'jr-west',
                'yahoo_url' => 'https://transit.yahoo.co.jp/diainfo/area/6#item8', // 近畿エリア - JR西日本セクション
                'is_active' => true,
            ],
            [
                'name' => '近畿日本鉄道',
                'slug' => 'kintetsu',
                'yahoo_url' => 'https://transit.yahoo.co.jp/diainfo/area/6#item230', // 近畿エリア - 近鉄セクション
                'is_active' => true,
            ],
            [
                'name' => '阪急電鉄',
                'slug' => 'hankyu',
                'yahoo_url' => 'https://transit.yahoo.co.jp/diainfo/area/6#item306', // 近畿エリア - 阪急セクション
                'is_active' => true,
            ],
            [
                'name' => '大阪メトロ',
                'slug' => 'osaka-metro',
                'yahoo_url' => 'https://transit.yahoo.co.jp/diainfo/area/6#item321', // 近畿エリア - 大阪メトロセクション
                'is_active' => true,
            ],
            [
                'name' => '南海電鉄',
                'slug' => 'nankai',
                'yahoo_url' => 'https://transit.yahoo.co.jp/diainfo/area/6#item339', // 近畿エリア - 南海セクション
                'is_active' => true,
            ],
        ];

        foreach ($operators as $operator) {
            RailwayOperator::create($operator);
        }
    }
}
