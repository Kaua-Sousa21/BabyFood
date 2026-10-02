<?php

declare(strict_types=1);

$appConfig = require __DIR__ . '/../config/app.php';
$dbConfig = require __DIR__ . '/../config/database.php';

if (!function_exists('mb_strlen')) {
    function mb_strlen(string $value, ?string $encoding = null): int { return strlen($value); }
}
if (!function_exists('mb_substr')) {
    function mb_substr(string $value, int $start, ?int $length = null, ?string $encoding = null): string { return $length === null ? substr($value, $start) : substr($value, $start, $length); }
}
if (!function_exists('mb_strtolower')) {
    function mb_strtolower(string $value, ?string $encoding = null): string { return strtolower($value); }
}
if (!function_exists('mb_strtoupper')) {
    function mb_strtoupper(string $value, ?string $encoding = null): string { return strtoupper($value); }
}

date_default_timezone_set($appConfig['timezone'] ?? 'America/Fortaleza');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($appConfig['session_name'] ?? 'baby_food_session');
    session_start();
}

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

$pdo = null;
$dbError = null;

try {
    $pdo = db_connect($dbConfig);
} catch (Throwable $exception) {
    $dbError = $exception->getMessage();
}
