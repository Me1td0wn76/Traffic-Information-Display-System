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
                'yahoo_url' => 'https://transit.yahoo.co.jp/diainfo/area/6', // 近畿エリア
                'is_active' => true,
            ],
            [
                'name' => '近畿日本鉄道',
                'slug' => 'kintetsu',
                'yahoo_url' => 'https://transit.yahoo.co.jp/diainfo/163/0',
                'is_active' => true,
            ],
            [
                'name' => '阪急電鉄',
                'slug' => 'hankyu',
                'yahoo_url' => 'https://transit.yahoo.co.jp/diainfo/164/0',
                'is_active' => true,
            ],
            [
                'name' => '大阪メトロ',
                'slug' => 'osaka-metro',
                'yahoo_url' => 'https://transit.yahoo.co.jp/diainfo/94/0',
                'is_active' => true,
            ],
            [
                'name' => '南海電鉄',
                'slug' => 'nankai',
                'yahoo_url' => 'https://transit.yahoo.co.jp/diainfo/165/0',
                'is_active' => true,
            ],
        ];

        foreach ($operators as $operator) {
            RailwayOperator::create($operator);
        }
    }
}
