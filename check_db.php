<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Railway Operators ===\n";
$operators = App\Models\RailwayOperator::all();
foreach ($operators as $op) {
    echo "{$op->id}: {$op->name} ({$op->slug})\n";
}

echo "\n=== Train Lines ===\n";
$lines = App\Models\TrainLine::with('railwayOperator')->get();
foreach ($lines as $line) {
    echo "{$line->id}: {$line->railwayOperator->name} - {$line->name}\n";
}

echo "\n=== Operation Statuses ===\n";
$statuses = App\Models\OperationStatus::with('trainLine')->latest()->take(10)->get();
foreach ($statuses as $status) {
    echo "{$status->id}: {$status->trainLine->name} - {$status->status} - {$status->checked_at}\n";
}

echo "\nTotal operators: " . $operators->count() . "\n";
echo "Total lines: " . $lines->count() . "\n";
echo "Total statuses: " . App\Models\OperationStatus::count() . "\n";
