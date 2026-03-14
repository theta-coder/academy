<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = DB::select("SHOW COLUMNS FROM fee_fine_rules");
echo "FEE_FINE_RULES TABLE:" . PHP_EOL;
foreach ($cols as $c) {
    echo "- {$c->Field} ({$c->Type}) " . ($c->Null === 'YES' ? 'nullable' : 'required') . PHP_EOL;
}
