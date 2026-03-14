<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

try {
    $tables = DB::select('SHOW TABLES');
    $dbName = DB::connection()->getDatabaseName();
    $tableNames = [];
    foreach ($tables as $table) {
        $tableNames[] = (array)$table;
    }
    $tablesList = array_map(function($t) { return array_values($t)[0]; }, $tableNames);
} catch (\Exception $e) {
    echo "Error getting tables: " . $e->getMessage() . "\n";
    exit;
}

$modelFiles = glob(app_path('Models/*.php'));
$controllerFiles = glob(app_path('Http/Controllers/*.php'));

$models = array_map(function($file) {
    return basename($file, '.php');
}, $modelFiles);

$controllers = array_map(function($file) {
    return basename($file, '.php');
}, $controllerFiles);

// Specific pivot tables and laravel system tables
$ignoreTables = [
    'migrations', 'password_reset_tokens', 'password_resets', 'personal_access_tokens', 'sessions', 
    'cache', 'cache_locks', 'jobs', 'failed_jobs', 'job_batches', 'model_has_permissions', 
    'model_has_roles', 'role_has_permissions', 'class_subject', 'exam_class'
];

$missingModels = [];
$missingControllers = [];

$modelTableMap = [];
foreach ($models as $m) {
    try {
        $className = "App\\Models\\{$m}";
        if (class_exists($className)) {
            $instance = new $className;
            $modelTableMap[$m] = $instance->getTable();
        }
    } catch (\Throwable $e) { }
}

foreach ($tablesList as $table) {
    if (in_array($table, $ignoreTables)) continue;

    $modelAssignedToTable = array_search($table, $modelTableMap);
    
    if ($modelAssignedToTable === false) {
        // Build expected model name manually as studly singular isn't always accurate
        $expectedModel = Str::studly(Str::singular($table));
        
        $missingModels[] = [
            'table' => $table,
            'expected_model' => $expectedModel
        ];
        $modelAssignedToTable = $expectedModel; 
    }
    
    $expectedController = $modelAssignedToTable . 'Controller';
    if (!in_array($expectedController, $controllers)) {
        if (!in_array($expectedController, ['UserExtraPermissionController', 'RoleController', 'PermissionController'])) {
             $missingControllers[] = [
                 'model' => $modelAssignedToTable,
                 'expected_controller' => $expectedController
             ];
        }
    }
}

file_put_contents('missing.json', json_encode([
    'missing_models' => $missingModels,
    'missing_controllers' => $missingControllers
], JSON_PRETTY_PRINT));

echo "Done writing to missing.json\n";
