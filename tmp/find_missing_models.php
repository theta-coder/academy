<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$modelsPath = base_path('app/Models');
$models = array_map(function($file) {
    return str_replace('.php', '', $file);
}, array_diff(scandir($modelsPath), ['.', '..']));

$tables = array_map(function($table) {
    return head((array)$table);
}, DB::select('SHOW TABLES'));

$laravelTables = [
    'cache', 'cache_locks', 'failed_jobs', 'job_batches', 'jobs', 
    'migrations', 'password_reset_tokens', 'sessions', 'telescope_entries', 
    'telescope_entries_tags', 'telescope_monitoring'
];

echo "TABLES WITHOUT MODELS:" . PHP_EOL;
foreach ($tables as $table) {
    if (in_array($table, $laravelTables)) continue;
    
    // Simple plural to singular conversion for basic check
    $modelName = Str::studly(Str::singular($table));
    if (!in_array($modelName, $models)) {
        echo "- $table (Suggested Model: $modelName)" . PHP_EOL;
    }
}
