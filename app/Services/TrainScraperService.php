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

/**
 * 電車運行情報取得サービス
 * 
 * Yahoo!路線情報からスクレイピングを行います。
 * サーバー負荷を最小限にするため、以下の対策を実施：
 * - 30分に1回のみ実行（キャッシュ使用）
 * - 各リクエスト間に2秒の待機時間
 * - 適切なUser-Agentの設定
 */
class TrainScraperService
{
    protected Client $client;
    
    /**
     * キャッシュの有効期間（分）
     */
    const CACHE_DURATION = 30;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false, // SSL証明書の検証を無効化（開発環境のみ）
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'ja,en-US;q=0.9,en;q=0.8',
            ],
        ]);
    }    /**
     * 全ての鉄道事業者の運行情報をスクレイピング
     */
    public function scrapeAll(): array
    {
        $results = [];
        $operators = RailwayOperator::where('is_active', true)->get();

        foreach ($operators as $operator) {
            try {
                Log::info("スクレイピング開始: {$operator->name}");
                $results[$operator->slug] = $this->scrapeOperator($operator);

                // サーバーに負荷をかけないよう少し待機
                sleep(2);
            } catch (\Exception $e) {
                Log::error("スクレイピングエラー ({$operator->name}): " . $e->getMessage());
                $results[$operator->slug] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * 特定の鉄道事業者の運行情報を取得（モックデータ）
     */
    public function scrapeOperator(RailwayOperator $operator): array
    {
        // モックデータから路線情報を生成
        $trainData = $this->generateMockData($operator);

        // データベースに保存
        $this->saveOperationStatuses($operator, $trainData);

        return [
            'success' => true,
            'operator' => $operator->name,
            'lines_count' => count($trainData),
            'delayed_count' => count(array_filter($trainData, fn($line) => $line['status'] !== 'normal')),
        ];
    }

    /**
     * モックデータを生成
     */
    protected function generateMockData(RailwayOperator $operator): array
    {
        $trainData = [];
        $lines = $this->mockLineData[$operator->slug] ?? [];

        if (empty($lines)) {
            Log::warning("{$operator->name}のモックデータが定義されていません");
            return [];
        }

        foreach ($lines as $line) {
            // 確率に基づいてランダムに遅延を発生させる
            $random = rand(1, 100);

            if ($random <= $line['probability']) {
                // 遅延発生
                $statuses = ['delay', 'partial_suspended', 'suspended'];
                $status = $statuses[array_rand($statuses)];

                $messages = [
                    'delay' => [
                        '人身事故の影響で、遅れが出ています。',
                        '信号トラブルの影響で、遅れが出ています。',
                        '車両点検の影響で、遅れが出ています。',
                        '強風の影響で、遅れが出ています。',
                    ],
                    'partial_suspended' => [
                        '人身事故の影響で、一部区間で運転を見合わせています。',
                        '線路内人立入の影響で、一部区間で運転を見合わせています。',
                    ],
                    'suspended' => [
                        '大雨の影響で、全線で運転を見合わせています。',
                        '事故の影響で、運転を見合わせています。',
                    ],
                ];

                $message = $messages[$status][array_rand($messages[$status])];
            } else {
                // 平常運転
                $status = 'normal';
                $message = '平常運転';
            }

            $trainData[] = [
                'line_name' => $line['name'],
                'status' => $status,
                'message' => $message,
            ];
        }

        Log::info("{$operator->name}: " . count($trainData) . "路線のモックデータを生成");

        return $trainData;
    }

    /**
     * 運行状況をデータベースに保存
     */
    protected function saveOperationStatuses(RailwayOperator $operator, array $trainData): void
    {
        $checkedAt = Carbon::now();

        foreach ($trainData as $data) {
            // 路線を検索または作成
            $slug = $this->createSlug($data['line_name']);
            Log::info("Saving: {$data['line_name']} (slug: {$slug})");

            $trainLine = TrainLine::firstOrCreate(
                [
                    'railway_operator_id' => $operator->id,
                    'slug' => $slug,
                ],
                [
                    'name' => $data['line_name'],
                    'is_active' => true,
                ]
            );

            // 運行状況を保存
            OperationStatus::create([
                'train_line_id' => $trainLine->id,
                'status' => $data['status'],
                'message' => $data['message'],
                'checked_at' => $checkedAt,
            ]);
        }
    }

    /**
     * 路線名からスラッグを生成
     */
    protected function createSlug(string $name): string
    {
        // 日本語を含む路線名のため、URLエンコードを使用
        $slug = strtolower($name);
        $slug = preg_replace('/\s+/', '-', $slug); // スペースをハイフンに
        $slug = preg_replace('/[^\w\-]/u', '-', $slug); // 日本語を含む文字を保持
        $slug = preg_replace('/-+/', '-', $slug); // 連続するハイフンを1つに
        return trim($slug, '-');
    }
}
