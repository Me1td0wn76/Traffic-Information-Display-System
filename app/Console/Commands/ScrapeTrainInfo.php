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

            foreach ($results as $slug => $result) {
                if ($result['success']) {
                    $this->line(sprintf(
                        '  %s: %d路線 (遅延: %d)',
                        $result['operator'],
                        $result['lines_count'],
                        $result['delayed_count']
                    ));
                } else {
                    $this->error(sprintf(
                        '  %s: エラー - %s',
                        $slug,
                        $result['error']
                    ));
                }
            }

            $this->newLine();

        } catch (\Exception $e) {
            $this->error('エラーが発生しました: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}

