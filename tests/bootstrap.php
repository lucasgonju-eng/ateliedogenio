<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/helpers.php';

$autoload = __DIR__ . '/../vendor/autoload.php';

if (file_exists($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'AtelieDoGenio\\';
        $baseDir = dirname(__DIR__) . '/src/';

        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        }
    });
}

