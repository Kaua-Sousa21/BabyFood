<?php
$user = require_role($pdo, 'parent');
$pageTitle = 'Finalizar pedido';
$cart = $_SESSION['cart'] ?? [];
if (!$cart) redirect('index.php?page=carrinho');

$ids = array_map('intval', array_keys($cart));
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$products = db_all($pdo, "SELECT * FROM products WHERE id IN ($placeholders) AND active = 1", $ids);
$subtotal = 0.0;
foreach ($products as $product) $subtotal += (float)$product['price'] * max(1, (int)($cart[$product['id']] ?? 1));
$delivery = 9.90;
$total = $subtotal + $delivery;
require __DIR__ . '/../partials/header.php';
?>
<main class="page-shell"><section class="page-hero"><div class="container"><div class="eyebrow">Checkout</div><h1 style="font-size:clamp(2.8rem,5vw,4.8rem)">Finalizar pedido</h1><p>Preencha os dados da entrega. O pagamento está em modo demonstrativo nesta versão.</p></div></section>
<section class="section-sm"><div class="container cart-layout"><form class="form-card form-grid" method="post" action="actions/checkout.php"><?= csrf_field() ?>
<label class="field field--wide"><span>Nome de quem vai receber</span><input type="text" name="recipient_name" value="<?= e($user['name']) ?>" required></label>
<label class="field"><span>Telefone</span><input type="tel" name="phone" value="<?= e($user['phone'] ?? '') ?>" required></label>
<label class="field">
    <span>CEP</span>
    <input type="text" name="zip_code" inputmode="numeric" autocomplete="postal-code" maxlength="9" placeholder="00000-000" data-cep-input required>
    <small class="field-hint" data-cep-status>Digite o CEP para identificar a cidade automaticamente.</small>
</label>
<label class="field field--wide">
    <span>Endereço e número</span>
    <input type="text" name="address_line" autocomplete="street-address" data-cep-address required>
</label>
<label class="field">
    <span>Cidade</span>
    <input type="text" name="city" autocomplete="address-level2" data-cep-city required>
</label>
<label class="field">
    <span>Estado</span>
    <input type="text" name="state" autocomplete="address-level1" maxlength="2" data-cep-state required>
</label>
<label class="field"><span>Pagamento</span><select name="payment_method"><option value="demo">Modo demonstrativo</option><option value="pix">PIX (integração futura)</option><option value="card">Cartão (integração futura)</option></select></label>
<label class="field field--wide"><span>Observações</span><textarea name="notes" placeholder="Ex.: referência para entrega"></textarea></label>
<button class="btn btn-primary field--wide" type="submit">Criar pedido</button>
</form><aside class="summary-card"><h2>Resumo</h2><div class="summary-row"><span>Subtotal</span><strong><?= money($subtotal) ?></strong></div><div class="summary-row"><span>Entrega</span><strong><?= money($delivery) ?></strong></div><div class="summary-row total"><span>Total</span><span><?= money($total) ?></span></div><div class="notice" style="margin-top:18px">Não há cobrança real nesta versão. O pedido será registrado para testar o fluxo do sistema.</div></aside></div></section></main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
