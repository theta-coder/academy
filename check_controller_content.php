<?php

$controllersDir = 'app/Http/Controllers';
$modelsDir = 'app/Models';

$controllers = glob($controllersDir . '/*Controller.php');

$results = [];

foreach ($controllers as $controllerPath) {
    if(!str_contains($controllerPath, 'Fee') && 
       !str_contains($controllerPath, 'Payment') && 
       !str_contains($controllerPath, 'Voucher') && 
       !str_contains($controllerPath, 'Installment') &&
       !str_contains($controllerPath, 'Scholarship') &&
       !str_contains($controllerPath, 'Concession') &&
       !str_contains($controllerPath, 'Cheque')
       ) {
        continue;
    }

    $name = basename($controllerPath, '.php');
    $content = file_get_contents($controllerPath);
    
    // Check if it's practically empty (just scaffolding)
    $lines = substr_count($content, "\n");
    $methodsCount = preg_match_all('/public\s+function/', $content, $matches);
    
    // Check for Eager Loading
    $hasWith = str_contains($content, '::with(') || str_contains($content, '->with(');
    
    $results[] = [
        'name' => $name,
        'lines' => $lines,
        'methods_count' => $methodsCount,
        'has_eager_loading' => $hasWith ? 'Yes' : 'No'
    ];
}

echo str_pad("Controller Name", 45) . " | " . str_pad("Lines", 7) . " | " . str_pad("Methods", 7) . " | " . "Has Eager Loading\n";
echo str_repeat("-", 85) . "\n";

foreach ($results as $res) {
    echo str_pad($res['name'], 45) . " | " . str_pad($res['lines'], 7) . " | " . str_pad($res['methods_count'], 7) . " | " . $res['has_eager_loading'] . "\n";
}
