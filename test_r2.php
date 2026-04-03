<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$path = sys_get_temp_dir() . '/test.txt'; 
file_put_contents($path, 'fake document content'); 
$file = new \Illuminate\Http\UploadedFile($path, 'test.txt', 'text/plain', null, true); 
try { 
    $res = $file->store('documents', 'r2'); 
    echo 'Result: ' . var_export($res, true) . PHP_EOL; 
} catch (\Exception $e) { 
    echo 'Error: (' . get_class($e) . ') ' . $e->getMessage() . PHP_EOL; 
}
