<?php

declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$user = require_role($pdo, 'parent');
if (!is_post()) redirect('../index.php?page=carrinho');
verify_csrf();
$cart = $_SESSION['cart'] ?? [];
if (!$cart) redirect('../index.php?page=carrinho');

$required = ['recipient_name', 'phone', 'address_line', 'city', 'state', 'zip_code'];
foreach ($required as $field) {
    if (trim((string)($_POST[$field] ?? '')) === '') {
        flash('danger', 'Preencha todos os dados obrigatórios da entrega.');
        redirect('../index.php?page=checkout');
    }
}

$zipCode = preg_replace('/\D+/', '', (string)($_POST['zip_code'] ?? '')) ?? '';
if (!preg_match('/^\d{8}$/', $zipCode)) {
    flash('danger', 'Informe um CEP válido com 8 dígitos.');
    redirect('../index.php?page=checkout');
}

$state = strtoupper(trim((string)($_POST['state'] ?? '')));
if (!preg_match('/^[A-Z]{2}$/', $state)) {
    flash('danger', 'Informe uma UF válida.');
    redirect('../index.php?page=checkout');
}

$ids = array_map('intval', array_keys($cart));
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$products = db_all($pdo, "SELECT * FROM products WHERE id IN ($placeholders) AND active = 1", $ids);
$subtotal = 0.0;
$items = [];
foreach ($products as $product) {
    $quantity = max(1, min((int)$product['stock'], (int)($cart[$product['id']] ?? 1)));
    if ($quantity <= 0) continue;
    $lineTotal = $quantity * (float)$product['price'];
    $subtotal += $lineTotal;
    $items[] = [$product, $quantity, $lineTotal];
}
if (!$items) {
    flash('danger', 'Os itens do carrinho não estão mais disponíveis.');
    redirect('../index.php?page=carrinho');
}
$deliveryFee = 9.90;
$total = $subtotal + $deliveryFee;
$paymentMethod = in_array($_POST['payment_method'] ?? 'demo', ['pix','card','demo'], true) ? $_POST['payment_method'] : 'demo';

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('INSERT INTO orders (user_id, subtotal, delivery_fee, total, payment_method, payment_status, recipient_name, phone, address_line, city, state, zip_code, notes) VALUES (?, ?, ?, ?, ?, "pending", ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([(int)$user['id'], $subtotal, $deliveryFee, $total, $paymentMethod, trim($_POST['recipient_name']), trim($_POST['phone']), trim($_POST['address_line']), trim($_POST['city']), $state, substr($zipCode, 0, 5) . '-' . substr($zipCode, 5), trim((string)($_POST['notes'] ?? '')) ?: null]);
    $orderId = (int)$pdo->lastInsertId();
    $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, item_name, unit_price, quantity, total) VALUES (?, ?, ?, ?, ?, ?)');
    $stockStmt = $pdo->prepare('UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?');
    foreach ($items as [$product, $quantity, $lineTotal]) {
        $itemStmt->execute([$orderId, (int)$product['id'], $product['name'], $product['price'], $quantity, $lineTotal]);
        $stockStmt->execute([$quantity, (int)$product['id']]);
    }
    $pdo->commit();
    unset($_SESSION['cart']);
    flash('success', 'Pedido #' . $orderId . ' criado com sucesso.');
    redirect('../index.php?page=minha-conta&tab=pedidos');
} catch (Throwable $exception) {
    $pdo->rollBack();
    flash('danger', 'Não foi possível criar o pedido.');
    redirect('../index.php?page=checkout');
}
