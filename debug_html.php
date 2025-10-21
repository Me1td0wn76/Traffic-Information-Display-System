<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use GuzzleHttp\Client;
use App\Models\RailwayOperator;

$client = new Client([
    'timeout' => 30,
    'verify' => false,
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ],
]);

// 近畿日本鉄道のHTMLを取得
$operator = RailwayOperator::where('slug', 'kintetsu')->first();

if ($operator && $operator->yahoo_url) {
    echo "URL: {$operator->yahoo_url}\n\n";

    $response = $client->get($operator->yahoo_url);
    $html = $response->getBody()->getContents();

    // HTMLの一部を保存
    file_put_contents(__DIR__ . '/debug_yahoo.html', $html);
    echo "HTMLを debug_yahoo.html に保存しました\n\n";

    // dd.normalとdd.troubleを含む部分を抽出
    preg_match_all('/<dd[^>]*class="[^"]*(?:trouble|normal)[^"]*"[^>]*>.*?<\/dd>/s', $html, $matches);

    echo "dd.troubleまたはdd.normalを含むタグ:\n";
    foreach ($matches[0] as $match) {
        echo $match . "\n\n";
    }
} else {
    echo "事業者が見つかりません\n";
}
