<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = array_map(function($table) {
    return head((array)$table);
}, DB::select('SHOW TABLES'));

$modelsPath = base_path('app/Models');
$modelFiles = array_diff(scandir($modelsPath), ['.', '..']);
$models = [];
foreach ($modelFiles as $file) {
    if (str_ends_with($file, '.php')) {
        $models[] = str_replace('.php', '', $file);
    }
}

$laravelTables = ['cache', 'cache_locks', 'failed_jobs', 'job_batches', 'jobs', 'migrations', 'password_reset_tokens', 'sessions'];

$results = [];
foreach ($tables as $table) {
    if (in_array($table, $laravelTables)) continue;
    
    $found = false;
    $targets = [Str::studly(Str::singular($table)), Str::studly($table), 'ParentModel', 'Classes'];
    foreach ($targets as $t) {
        if (in_array($t, $models)) {
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $results[] = $table;
    }
}

if (empty($results)) {
    echo "NO_MISSING_MODELS";
} else {
    echo "MISSING:" . implode(',', $results);
}
