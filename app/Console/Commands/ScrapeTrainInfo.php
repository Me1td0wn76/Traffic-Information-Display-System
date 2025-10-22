<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TrainScraperService;
use Illuminate\Support\Facades\Log;

class ScrapeTrainInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'train:scrape {operator?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '電車の運行情報をスクレイピングします';

    /**
     * Execute the console command.
     */
    public function handle(TrainScraperService $scraper)
    {
        $this->info(' 電車運行情報のスクレイピングを開始します...');
        Log::info('コマンド実行: train:scrape');

        try {
            $results = $scraper->scrapeAll();

            $this->newLine();
            $this->info(' スクレイピング完了');
            $this->newLine();

            if (empty($results)) {
                $this->warn('  結果が空です');
                Log::warning('スクレイピング結果が空です');
                return 1;
            }

            $totalLines = 0;
            $totalDelayed = 0;
            $totalUpdated = 0;

            foreach ($results as $slug => $result) {
                if (isset($result['success']) && $result['success']) {
                    $this->line(sprintf(
                        '  %s: %d路線 (遅延: %d, 更新: %d)',
                        $result['operator'] ?? $slug,
                        $result['lines_count'] ?? 0,
                        $result['delayed_count'] ?? 0,
                        $result['updated_count'] ?? 0
                    ));
                    
                    $totalLines += $result['lines_count'] ?? 0;
                    $totalDelayed += $result['delayed_count'] ?? 0;
                    $totalUpdated += $result['updated_count'] ?? 0;
                } else {
                    $this->error(sprintf(
                        '  %s: エラー - %s',
                        $slug,
                        $result['error'] ?? 'Unknown error'
                    ));
                }
            }

            $this->newLine();
            $this->info(sprintf(
                ' 合計: %d路線 | 遅延: %d | 更新: %d',
                $totalLines,
                $totalDelayed,
                $totalUpdated
            ));

            Log::info('コマンド完了: train:scrape', [
                'total_lines' => $totalLines,
                'total_delayed' => $totalDelayed,
                'total_updated' => $totalUpdated
            ]);

        } catch (\Exception $e) {
            $this->error('エラーが発生しました: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            Log::error('コマンドエラー: train:scrape', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }
}

