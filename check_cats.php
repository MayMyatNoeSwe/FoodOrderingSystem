<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$categories = App\Models\Category::selectRaw('name, COUNT(*) as count')
    ->groupBy('name')
    ->havingRaw('COUNT(*) > 1')
    ->get();

foreach ($categories as $c) {
    echo $c->name . " - " . $c->count . "\n";
}
