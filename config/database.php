<?php

declare(strict_types=1);

$localFile = __DIR__ . '/database.local.php';

if (is_file($localFile)) {
    return require $localFile;
}

return [
    'host' => 'localhost',
    'port' => 3306,
    'name' => 'baby_food',
    'user' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
];
