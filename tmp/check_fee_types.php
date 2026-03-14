<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = DB::select("SHOW COLUMNS FROM fee_types");
echo "FEE_TYPES TABLE COLUMNS:" . PHP_EOL;
foreach ($columns as $col) {
    echo "- {$col->Field} ({$col->Type}) " . ($col->Null === 'YES' ? 'nullable' : 'required') . " " . ($col->Key === 'PRI' ? '[PK]' : '') . PHP_EOL;
}
