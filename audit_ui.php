<?php

$controllersDir = 'app/Http/Controllers';
$pagesDir = 'resources/js/Pages';
$webRouteFile = 'routes/web.php';
$sidebarFile = 'resources/js/Components/Layout/Sidebar.vue';

$controllers = glob($controllersDir . '/*Controller.php');
$webContent = file_get_contents($webRouteFile);
$sidebarContent = file_exists($sidebarFile) ? file_get_contents($sidebarFile) : '';

$results = [];

foreach ($controllers as $c) {
    if(str_contains($c, 'Fee') || 
       str_contains($c, 'Payment') || 
       str_contains($c, 'Voucher') || 
       str_contains($c, 'Installment') || 
       str_contains($c, 'Scholarship') || 
       str_contains($c, 'Concession') || 
       str_contains($c, 'Cheque')) {
           
        $name = basename($c, '.php');
        $baseFolder = str_replace('Controller', '', $name); // e.g., FeeVoucher
        
        // Sometimes folder is plural like FeeVouchers
        $folderPathNormal = $pagesDir . '/' . $baseFolder;
        $folderPathPlural = $pagesDir . '/' . \Str::plural($baseFolder); 
        // We will just check if directory exists matching some pattern, but Plural is standard
        $hasViews = false;
        if (is_dir($folderPathPlural) && file_exists($folderPathPlural . '/Index.vue')) {
            $hasViews = true;
        } elseif (is_dir($folderPathNormal) && file_exists($folderPathNormal . '/Index.vue')) {
            $hasViews = true;
        }

        $hasRoute = str_contains($webContent, $name);
        
        // guess route name
        $routeNameGuess = \Str::kebab(\Str::plural($baseFolder)) . '.index';
        $hasSidebar = str_contains($sidebarContent, $routeNameGuess);

        $results[] = [
            'controller' => $name,
            'views' => $hasViews ? 'Yes' : 'No',
            'route' => $hasRoute ? 'Yes' : 'No',
            'sidebar' => $hasSidebar ? 'Yes' : 'No',
            'base' => $baseFolder
        ];
    }
}

echo str_pad("Controller Name", 45) . " | " . str_pad("Views", 7) . " | " . str_pad("Route", 7) . " | " . "Sidebar\n";
echo str_repeat("-", 75) . "\n";
foreach ($results as $res) {
    echo str_pad($res['controller'], 45) . " | " . str_pad($res['views'], 7) . " | " . str_pad($res['route'], 7) . " | " . $res['sidebar'] . "\n";
}
