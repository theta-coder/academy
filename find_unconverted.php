<?php

$pagesDir = 'resources/js/Pages';
$files = [];

function getFiles($dir, &$files) {
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                if (is_dir($dir . "/" . $entry)) {
                    getFiles($dir . "/" . $entry, $files);
                } else {
                    if ($entry === 'Create.vue' || $entry === 'Edit.vue') {
                        $files[] = $dir . "/" . $entry;
                    }
                }
            }
        }
        closedir($handle);
    }
}

getFiles($pagesDir, $files);

$unconverted = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (!str_contains($content, 'FormPage')) {
        $unconverted[] = $file;
    }
}

echo "Total Create/Edit files: " . count($files) . "\n";
echo "Unconverted files: " . count($unconverted) . "\n\n";

foreach ($unconverted as $file) {
    echo $file . "\n";
}
