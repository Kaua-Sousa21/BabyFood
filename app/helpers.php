<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return $path === '' ? 'index.php' : $path;
}

function asset(string $path): string
{
    return 'assets/' . ltrim($path, '/');
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Sessão expirada. Atualize a página e tente novamente.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pull_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function money(float|int|string $value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

function page_is(string $page): bool
{
    return ($_GET['page'] ?? 'home') === $page;
}

function age_in_months(string $birthDate): int
{
    $birth = new DateTimeImmutable($birthDate);
    $today = new DateTimeImmutable('today');

    if ($birth > $today) {
        return 0;
    }

    $diff = $birth->diff($today);
    return ($diff->y * 12) + $diff->m;
}

function age_range_for_months(PDO $pdo, int $months): ?array
{
    return db_one(
        $pdo,
        'SELECT * FROM age_ranges WHERE ? >= min_months AND ? < max_months ORDER BY sort_order LIMIT 1',
        [$months, $months]
    );
}

function child_age_label(string $birthDate): string
{
    $months = age_in_months($birthDate);

    if ($months < 12) {
        return $months . ' ' . ($months === 1 ? 'mês' : 'meses');
    }

    $years = intdiv($months, 12);
    $remaining = $months % 12;

    if ($remaining === 0) {
        return $years . ' ' . ($years === 1 ? 'ano' : 'anos');
    }

    return $years . ' ano' . ($years > 1 ? 's' : '') . ' e ' . $remaining . ' mes' . ($remaining > 1 ? 'es' : '');
}

function safe_return_to(string $fallback = 'index.php'): string
{
    $returnTo = $_POST['return_to'] ?? $_GET['return_to'] ?? '';

    if (!is_string($returnTo) || $returnTo === '' || str_contains($returnTo, '://') || str_starts_with($returnTo, '//')) {
        return $fallback;
    }

    return $returnTo;
}

function product_image(?string $url, string $fallback = 'food'): string
{
    if ($url) {
        return $url;
    }

    return 'assets/img/' . $fallback . '-placeholder.svg';
}

function cart_count(): int
{
    $cart = $_SESSION['cart'] ?? [];
    $count = 0;

    foreach ($cart as $quantity) {
        $count += (int) $quantity;
    }

    return $count;
}

function text_lines(string $text): array
{
    $lines = preg_split('/\r\n|\r|\n/', trim($text)) ?: [];
    return array_values(array_filter(array_map('trim', $lines)));
}

function excerpt(string $text, int $length = 150): string
{
    $plain = trim(strip_tags($text));

    if (mb_strlen($plain) <= $length) {
        return $plain;
    }

    return rtrim(mb_substr($plain, 0, $length - 1)) . '…';
}
