<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TrainScraperService;

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

        try {
            $results = $scraper->scrapeAll();

            $this->newLine();
            $this->info(' スクレイピング完了');
            $this->newLine();

            if (empty($results)) {
                $this->warn('  結果が空です');
                return 1;
            }

            foreach ($results as $slug => $result) {
                if (isset($result['success']) && $result['success']) {
                    $this->line(sprintf(
                        '  %s: %d路線 (遅延: %d)',
                        $result['operator'] ?? $slug,
                        $result['lines_count'] ?? 0,
                        $result['delayed_count'] ?? 0
                    ));
                } else {
                    $this->error(sprintf(
                        '  %s: エラー - %s',
                        $slug,
                        $result['error'] ?? 'Unknown error'
                    ));
                }
            }

            $this->newLine();

        } catch (\Exception $e) {
            $this->error('エラーが発生しました: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}

