<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = DB::table('users')->select('id', 'name', 'email')->get();
foreach ($users as $u) {
    echo $u->id . ' | ' . $u->name . ' | ' . $u->email . PHP_EOL;
}
