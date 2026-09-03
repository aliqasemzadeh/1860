<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'Kernel: '.get_class($kernel).PHP_EOL;

$ref = new ReflectionClass($kernel);
$method = $ref->getMethod('getArtisan');
$method->setAccessible(true);
$artisan = $method->invoke($kernel);

echo 'Artisan: '.get_class($artisan).PHP_EOL;
echo 'doRunCommand defined in: '.((new ReflectionMethod($artisan, 'doRunCommand'))->getDeclaringClass()->getName()).PHP_EOL;
