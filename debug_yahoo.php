<?php

require __DIR__.'/vendor/autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

$client = new Client([
    'verify' => false,
    'timeout' => 30,
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ],
]);

$url = 'https://transit.yahoo.co.jp/diainfo/area/6';
echo "URLからHTMLを取得中: {$url}\n\n";

$response = $client->get($url);
$html = $response->getBody()->getContents();
$crawler = new Crawler($html);

// アンカーIDのリスト
$anchorIds = [
    'item8' => 'JR西日本',
    'item230' => '近畿日本鉄道',
    'item306' => '阪急電鉄',
    'item321' => '大阪メトロ',
    'item339' => '南海電鉄',
];

foreach ($anchorIds as $anchorId => $operatorName) {
    echo "=== {$operatorName} (#{$anchorId}) ===\n";

    // セクションヘッダーを探す
    $sectionHeader = $crawler->filter("h3#{$anchorId}");

    if ($sectionHeader->count() === 0) {
        echo "❌ セクションヘッダーが見つかりません\n\n";
        continue;
    }

    echo "✅ セクションヘッダー: " . $sectionHeader->text() . "\n";

    // XPathで該当セクションを探す
    $section = $crawler->filterXPath("//h3[@id='{$anchorId}']/ancestor::div[@class='labelSmall']/following-sibling::div[@class='elmTblLstLine'][1]");
    echo "XPathマッチ数: " . $section->count() . "\n";

    if ($section->count() > 0) {
        $rows = $section->filter('table tbody tr');
        $dataRows = 0;
        $rows->each(function (Crawler $row) use (&$dataRows) {
            if ($row->filter('th')->count() === 0) {
                $dataRows++;
            }
        });
        echo "✅ 路線数: {$dataRows}\n";

        // 最初の3路線を表示
        $count = 0;
        $rows->each(function (Crawler $row) use (&$count) {
            if ($count >= 3) return;
            if ($row->filter('th')->count() > 0) return;

            $cells = $row->filter('td');
            if ($cells->count() >= 3) {
                $lineNameNode = $cells->eq(0)->filter('a');
                $lineName = $lineNameNode->count() > 0 ? trim($lineNameNode->text()) : trim($cells->eq(0)->text());
                $status = trim($cells->eq(1)->text());
                echo "  - {$lineName}: {$status}\n";
                $count++;
            }
        });
    }

    echo "\n";
}
