<?php

declare(strict_types=1);

$kernel = require __DIR__ . '/../bootstrap/app.php';

$response = $kernel->handleFromGlobals();

$kernel->emit($response);

