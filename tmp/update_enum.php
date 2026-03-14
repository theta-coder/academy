<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    DB::statement("ALTER TABLE fee_fine_rules MODIFY COLUMN fine_type ENUM('fixed', 'percentage', 'daily_fixed', 'daily_percentage') NOT NULL");
    echo "SUCCESS: fee_fine_rules.fine_type enum updated successfully." . PHP_EOL;
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
