<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = DB::select("SHOW COLUMNS FROM installment_plans");
echo "INSTALLMENT_PLANS TABLE:" . PHP_EOL;
foreach ($cols as $c) {
    echo "- {$c->Field} ({$c->Type}) " . ($c->Null === 'YES' ? 'nullable' : 'required') . PHP_EOL;
}
