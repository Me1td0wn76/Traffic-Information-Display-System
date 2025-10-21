<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$lines = App\Models\TrainLine::with(['railwayOperator', 'latestOperationStatus'])->get();

echo "登録されている路線:\n\n";

foreach ($lines as $line) {
    $status = $line->latestOperationStatus ? $line->latestOperationStatus->status : 'データなし';
    $message = $line->latestOperationStatus ? $line->latestOperationStatus->message : '';
    echo sprintf(
        "%s: %s [%s] %s\n",
        $line->railwayOperator->name,
        $line->name,
        $status,
        $message
    );
}

echo "\n合計: " . $lines->count() . " 路線\n";
