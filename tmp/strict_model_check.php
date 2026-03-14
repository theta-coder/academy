<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = array_map(function($table) {
    return head((array)$table);
}, DB::select('SHOW TABLES'));

$modelsPath = base_path('app/Models');
$modelFiles = is_dir($modelsPath) ? array_diff(scandir($modelsPath), ['.', '..']) : [];

$availableModels = [];
foreach ($modelFiles as $file) {
    if (str_ends_with($file, '.php')) {
        $content = file_get_contents($modelsPath . '/' . $file);
        // Try to get class name from filename
        $modelName = str_replace('.php', '', $file);
        
        // Also try to find specified table in the model file
        $tableName = null;
        if (preg_match('/protected\s+\$table\s*=\s*[\'"](.+?)[\'"]/', $content, $matches)) {
            $tableName = $matches[1];
        }
        
        $availableModels[] = [
            'name' => $modelName,
            'table' => $tableName
        ];
    }
}

$laravelTables = [
    'cache', 'cache_locks', 'failed_jobs', 'job_batches', 'jobs', 
    'migrations', 'password_reset_tokens', 'sessions', 'telescope_entries', 
    'telescope_entries_tags', 'telescope_monitoring'
];

echo "STRICT MISSING MODELS CHECK:" . PHP_EOL;
$missing = false;
foreach ($tables as $table) {
    if (in_array($table, $laravelTables)) continue;
    
    $found = false;
    foreach ($availableModels as $m) {
        // Match by explicitly defined table name
        if ($m['table'] === $table) {
            $found = true;
            break;
        }
        
        // Match by standard Laravel convention
        $standardModelName = Str::studly(Str::singular($table));
        if ($m['name'] === $standardModelName) {
            $found = true;
            break;
        }

        // Match by plural model name (rare but sometimes happens)
        if ($m['name'] === Str::studly($table)) {
            $found = true;
            break;
        }

        // Hardcoded special cases we found earlier
        if ($table === 'classes' && $m['name'] === 'Classes') { $found = true; break; }
        if ($table === 'parents' && ($m['name'] === 'ParentModel' || $m['name'] === 'Parents')) { $found = true; break; }
    }
    
    if (!$found) {
        echo "- Table: '$table' has NO Model." . PHP_EOL;
        $missing = true;
    }
}

if (!$missing) {
    echo "Congratulations! Every database table has a corresponding Model." . PHP_EOL;
}
