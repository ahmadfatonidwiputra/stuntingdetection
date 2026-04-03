<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try { 
    $files = \Illuminate\Support\Facades\Storage::disk('r2')->allFiles(); 
    echo 'Read SUCCESS. Files: ' . count($files) . PHP_EOL; 
} catch (\Exception $e) { 
    echo 'Read Error: (' . get_class($e) . ') ' . $e->getMessage() . PHP_EOL; 
}
