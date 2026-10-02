<?php

declare(strict_types=1);

function db_connect(array $config): PDO
{
    $host = $config['host'] ?? 'localhost';
    $port = (int) ($config['port'] ?? 3306);
    $name = $config['name'] ?? '';
    $charset = $config['charset'] ?? 'utf8mb4';

    if ($name === '') {
        throw new RuntimeException('Banco de dados não configurado.');
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

    return new PDO(
        $dsn,
        $config['user'] ?? '',
        $config['password'] ?? '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}

function db_one(PDO $pdo, string $sql, array $params = []): ?array
{
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $row = $statement->fetch();

    return $row ?: null;
}

function db_all(PDO $pdo, string $sql, array $params = []): array
{
    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

function db_value(PDO $pdo, string $sql, array $params = []): mixed
{
    $row = db_one($pdo, $sql, $params);

    if (!$row) {
        return null;
    }

    return array_values($row)[0] ?? null;
}
