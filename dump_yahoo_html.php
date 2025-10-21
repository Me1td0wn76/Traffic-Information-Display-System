<?php

require __DIR__.'/vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client([
    'verify' => false,
    'timeout' => 30,
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ],
]);

$url = 'https://transit.yahoo.co.jp/diainfo/area/6';
echo "URLからHTMLを取得中: {$url}\n";

$response = $client->get($url);
$html = $response->getBody()->getContents();

// HTMLをファイルに保存
$filename = 'yahoo_transit.html';
file_put_contents($filename, $html);

echo "HTMLを {$filename} に保存しました\n";
echo "ファイルサイズ: " . number_format(strlen($html)) . " bytes\n\n";

// 各アンカーIDの位置を確認
$anchorIds = ['item8', 'item230', 'item306', 'item321', 'item339'];

foreach ($anchorIds as $anchorId) {
    $pos = strpos($html, "id=\"{$anchorId}\"");
    if ($pos !== false) {
        // 前後200文字を抽出
        $start = max(0, $pos - 100);
        $length = 400;
        $snippet = substr($html, $start, $length);

        echo "=== #{$anchorId} の周辺HTML ===\n";
        echo htmlspecialchars($snippet) . "\n\n";
    } else {
        echo "❌ #{$anchorId} が見つかりません\n\n";
    }
}
