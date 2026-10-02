<?php
$pageTitle = 'Carrinho';
$cart = $_SESSION['cart'] ?? [];
$items = [];
$subtotal = 0.0;

if ($cart && $pdo) {
    $ids = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $products = db_all($pdo, "SELECT p.*, a.name AS age_name FROM products p JOIN age_ranges a ON a.id = p.age_range_id WHERE p.id IN ($placeholders)", $ids);
    foreach ($products as $product) {
        $quantity = max(1, (int) ($cart[$product['id']] ?? 1));
        $lineTotal = $quantity * (float) $product['price'];
        $subtotal += $lineTotal;
        $items[] = ['product' => $product, 'quantity' => $quantity, 'line_total' => $lineTotal];
    }
}
$delivery = $subtotal > 0 ? 9.90 : 0.0;
$total = $subtotal + $delivery;
require __DIR__ . '/../partials/header.php';
?>
<main class="page-shell">
<section class="page-hero"><div class="container"><div class="eyebrow">Seu pedido</div><h1 style="font-size:clamp(2.8rem,5vw,4.8rem)">Carrinho</h1><p>Revise as marmitas antes de finalizar.</p></div></section>
<section class="section-sm"><div class="container">
<?php if (!$items): ?>
    <div class="empty-state"><div class="eyebrow">Carrinho vazio</div><h2>Escolha algumas marmitas.</h2><p>Você pode filtrar os produtos pela faixa etária da criança.</p><a class="btn btn-primary" href="index.php?page=marmitas">Ver marmitas</a></div>
<?php else: ?>
<div class="cart-layout">
    <div class="cart-list">
        <?php foreach ($items as $item): $product = $item['product']; ?>
            <article class="cart-item">
                <div class="cart-item-image"><img src="<?= e(product_image($product['image_url'])) ?>" alt="<?= e($product['name']) ?>"></div>
                <div><h3><?= e($product['name']) ?></h3><p><?= e($product['age_name']) ?> · <?= (int)$product['weight_grams'] ?>g</p><strong><?= money($product['price']) ?></strong></div>
                <div class="cart-item-actions">
                    <form method="post" action="actions/cart.php" style="display:flex;gap:8px;align-items:center">
                        <?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><input type="hidden" name="return_to" value="index.php?page=carrinho">
                        <div class="quantity-control" data-quantity><button type="button" data-qty-minus>−</button><input type="number" name="quantity" min="1" max="99" value="<?= (int)$item['quantity'] ?>"><button type="button" data-qty-plus>+</button></div>
                        <button class="btn btn-ghost btn-sm" type="submit">Atualizar</button>
                    </form>
                    <form method="post" action="actions/cart.php" style="margin-top:7px"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><input type="hidden" name="return_to" value="index.php?page=carrinho"><button class="text-link" style="border:0;background:none;cursor:pointer" type="submit">Remover</button></form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <aside class="summary-card"><h2>Resumo</h2><div class="summary-row"><span>Subtotal</span><strong><?= money($subtotal) ?></strong></div><div class="summary-row"><span>Entrega demonstrativa</span><strong><?= money($delivery) ?></strong></div><div class="summary-row total"><span>Total</span><span><?= money($total) ?></span></div><a class="btn btn-primary btn-block" href="index.php?page=checkout">Finalizar pedido</a></aside>
</div>
<?php endif; ?>
</div></section></main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
