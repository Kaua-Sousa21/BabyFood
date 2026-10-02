<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$page = $_GET['page'] ?? 'home';
$page = is_string($page) ? preg_replace('/[^a-z0-9_-]/i', '', $page) : 'home';

$routes = [
    'home' => 'pages/home.php',
    'marmitas' => 'pages/products.php',
    'marmita' => 'pages/product.php',
    'receitas' => 'pages/recipes.php',
    'receita' => 'pages/recipe.php',
    'videos' => 'pages/videos.php',
    'video' => 'pages/video.php',
    'nutricao' => 'pages/nutrition.php',
    'nutricionistas' => 'pages/nutritionists.php',
    'chat' => 'pages/chat.php',
    'carrinho' => 'pages/cart.php',
    'checkout' => 'pages/checkout.php',
    'minha-conta' => 'pages/account.php',
    'login' => 'pages/auth/login.php',
    'cadastro' => 'pages/auth/register.php',
    'admin' => 'pages/admin/dashboard.php',
    'admin-edit' => 'pages/admin/edit.php',
    'nutri-dashboard' => 'pages/nutritionist/dashboard.php',
];

if (!$pdo && !in_array($page, ['login', 'cadastro'], true)) {
    require __DIR__ . '/partials/db-error.php';
    exit;
}

if (!isset($routes[$page])) {
    http_response_code(404);
    $pageTitle = 'Página não encontrada';
    require __DIR__ . '/pages/404.php';
    exit;
}

require __DIR__ . '/' . $routes[$page];
