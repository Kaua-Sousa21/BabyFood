<?php

declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';

if (!is_post()) redirect('../index.php?page=carrinho');
verify_csrf();
$action = $_POST['action'] ?? '';
$productId = (int) ($_POST['product_id'] ?? 0);
$returnTo = safe_return_to('../index.php?page=carrinho');
if (str_starts_with($returnTo, 'index.php')) {
    $returnTo = '../' . $returnTo;
}
$_SESSION['cart'] ??= [];

if ($action === 'add' && $productId > 0) {
    $product = $pdo ? db_one($pdo, 'SELECT id, stock FROM products WHERE id = ? AND active = 1', [$productId]) : null;
    if ($product && (int) $product['stock'] > 0) {
        $_SESSION['cart'][$productId] = min((int) $product['stock'], (int) ($_SESSION['cart'][$productId] ?? 0) + 1);
        flash('success', 'Marmita adicionada ao carrinho.');
    }
}

if ($action === 'update' && $productId > 0) {
    $quantity = max(0, (int) ($_POST['quantity'] ?? 1));
    if ($quantity === 0) unset($_SESSION['cart'][$productId]);
    else $_SESSION['cart'][$productId] = min(99, $quantity);
    flash('success', 'Carrinho atualizado.');
}

if ($action === 'remove' && $productId > 0) {
    unset($_SESSION['cart'][$productId]);
    flash('success', 'Item removido do carrinho.');
}

redirect($returnTo);
