<?php

namespace App\Services;

use App\Models\RailwayOperator;
use App\Models\TrainLine;
use App\Models\OperationStatus;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TrainScraperService
{
    protected Client $client;
    const CACHE_DURATION = 30;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ],
        ]);
    }

    public function scrapeAll(): array
    {
        $results = [];
        $operators = RailwayOperator::where('is_active', true)->get();

        foreach ($operators as $operator) {
            $cacheKey = "train_info_{$operator->slug}";
            $cachedResult = Cache::get($cacheKey);

            if ($cachedResult !== null) {
                Log::info("キャッシュから取得: {$operator->name}");
                $results[$operator->slug] = $cachedResult;
                continue;
            }

            try {
                Log::info("スクレイピング開始: {$operator->name}");
                $result = $this->scrapeOperator($operator);
                $results[$operator->slug] = $result;
                Cache::put($cacheKey, $result, now()->addMinutes(self::CACHE_DURATION));
                sleep(2);
            } catch (\Exception $e) {
                Log::error("エラー ({$operator->name}): " . $e->getMessage());
                $results[$operator->slug] = ['success' => false, 'error' => $e->getMessage()];
            }
        }
        return $results;
    }

    public function scrapeOperator(RailwayOperator $operator): array
    {
        if (!$operator->yahoo_url) {
            throw new \Exception("Yahoo URLが設定されていません");
        }

        $response = $this->client->get($operator->yahoo_url);
        $html = $response->getBody()->getContents();
        $crawler = new Crawler($html);
        $trainData = $this->parseTrainInfo($crawler, $operator);
        $this->saveOperationStatuses($operator, $trainData);

        return [
            'success' => true,
            'operator' => $operator->name,
            'lines_count' => count($trainData),
            'delayed_count' => count(array_filter($trainData, fn($line) => $line['status'] !== 'normal')),
        ];
    }

    protected function parseTrainInfo(Crawler $crawler, RailwayOperator $operator): array
    {
        $trainData = [];

        try {
            // URLからアンカーIDを抽出 (例: #item8 → item8)
            $anchorId = '';
            if (preg_match('/#(.+)$/', $operator->yahoo_url, $matches)) {
                $anchorId = $matches[1];
            }

            if (empty($anchorId)) {
                Log::warning("アンカーIDが見つかりません: {$operator->yahoo_url}");
                return $trainData;
            }

            // XPathで <h3 id="item8"> の次の <div class="elmTblLstLine"> を探す
            // HTML構造: <div class="labelSmall"><h3 id="item8"></h3></div><div class="elmTblLstLine">...
            $section = $crawler->filterXPath("//h3[@id='{$anchorId}']/ancestor::div[@class='labelSmall']/following-sibling::div[@class='elmTblLstLine'][1]");

            if ($section->count() === 0) {
                Log::warning("セクションが見つかりません: h3#{$anchorId} for {$operator->name}");
                return $trainData;
            }

            // 該当セクション内のテーブル行を解析
            $section->filter('table tbody tr')->each(function (Crawler $row) use (&$trainData) {
                try {
                    if ($row->filter('th')->count() > 0) return;
                    $cells = $row->filter('td');
                    if ($cells->count() < 3) return;

                    $lineNameNode = $cells->eq(0)->filter('a')->first();
                    if ($lineNameNode->count() === 0) return;

                    $lineName = trim($lineNameNode->text());
                    $statusText = trim($cells->eq(1)->text());
                    $message = trim($cells->eq(2)->text());
                    $hasTrouble = $cells->eq(1)->filter('.colTrouble')->count() > 0;

                    if ($hasTrouble || $statusText !== '平常運転') {
                        $status = $this->determineStatus($statusText, $message);
                    } else {
                        $status = 'normal';
                    }

                    $trainData[] = [
                        'line_name' => $lineName,
                        'status' => $status,
                        'message' => $message !== '事故・遅延情報はありません' ? $message : '平常運転',
                    ];
                } catch (\Exception $e) {
                    Log::warning("解析エラー: " . $e->getMessage());
                }
            });

            Log::info("{$operator->name}: " . count($trainData) . "路線を検出");
        } catch (\Exception $e) {
            Log::error("HTML解析エラー ({$operator->name}): " . $e->getMessage());
        }

        return $trainData;
    }    protected function determineStatus(string $statusText, string $message): string
    {
        $text = $statusText . ' ' . $message;
        if (str_contains($text, '運休') || str_contains($text, '運転見合わせ')) {
            return str_contains($text, '一部') ? 'partial_suspended' : 'suspended';
        }
        if (str_contains($text, '遅延') || str_contains($text, '遅れ') || str_contains($text, '列車遅延')) {
            return 'delay';
        }
        if (str_contains($text, '運転計画')) {
            return 'delay';
        }
        return 'normal';
    }

    protected function saveOperationStatuses(RailwayOperator $operator, array $trainData): void
    {
        $checkedAt = Carbon::now();
        foreach ($trainData as $data) {
            $trainLine = TrainLine::firstOrCreate(
                [
                    'railway_operator_id' => $operator->id,
                    'slug' => $this->createSlug($data['line_name']),
                ],
                [
                    'name' => $data['line_name'],
                    'is_active' => true,
                ]
            );
            OperationStatus::create([
                'train_line_id' => $trainLine->id,
                'status' => $data['status'],
                'message' => $data['message'],
                'checked_at' => $checkedAt,
            ]);
        }
    }

    protected function createSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = preg_replace('/[^\w\-]/u', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
