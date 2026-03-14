<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$classmap = require 'vendor/composer/autoload_classmap.php';
$missing = [];
foreach ($classmap as $class => $file) {
    if (str_contains($class, 'App\\Models\\') && !file_exists($file)) {
        $missing[] = ['class' => $class, 'file' => $file];
    }
}

if (empty($missing)) {
    echo "ALL MODEL FILES EXIST" . PHP_EOL;
} else {
    echo "MISSING MODEL FILES:" . PHP_EOL;
    foreach ($missing as $m) {
        echo "- {$m['class']} => {$m['file']}" . PHP_EOL;
    }
}
