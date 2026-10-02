<?php

declare(strict_types=1);

function current_user(?PDO $pdo): ?array
{
    static $cachedUser = false;

    if ($cachedUser !== false) {
        return $cachedUser;
    }

    $id = $_SESSION['user_id'] ?? null;

    if (!$id || !$pdo) {
        $cachedUser = null;
        return null;
    }

    $cachedUser = db_one($pdo, 'SELECT * FROM users WHERE id = ? AND status = "active" LIMIT 1', [(int) $id]);
    return $cachedUser;
}

function is_logged_in(?PDO $pdo): bool
{
    return current_user($pdo) !== null;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
}

function logout_user(): void
{
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
}

function require_login(?PDO $pdo): array
{
    $user = current_user($pdo);

    if (!$user) {
        flash('warning', 'Entre na sua conta para continuar.');
        redirect('index.php?page=login');
    }

    return $user;
}

function require_role(?PDO $pdo, array|string $roles): array
{
    $user = require_login($pdo);
    $roles = (array) $roles;

    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('Você não tem permissão para acessar esta área.');
    }

    return $user;
}
