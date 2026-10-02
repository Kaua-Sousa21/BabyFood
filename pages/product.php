<?php
$id = (int) ($_GET['id'] ?? 0);
$product = db_one($pdo, 'SELECT p.*, a.name AS age_name FROM products p JOIN age_ranges a ON a.id = p.age_range_id WHERE p.id = ? AND p.active = 1', [$id]);
if (!$product) { http_response_code(404); redirect('index.php?page=marmitas'); }
$pageTitle = $product['name'];
require __DIR__ . '/../partials/header.php';
?>
<main class="page-shell"><section class="section"><div class="container detail-grid">
    <div class="detail-media"><img src="<?= e(product_image($product['image_url'])) ?>" alt="<?= e($product['name']) ?>"></div>
    <div class="detail-copy"><div class="eyebrow">Marmita Baby Food</div><h1><?= e($product['name']) ?></h1><p><?= e($product['description']) ?></p>
        <div class="detail-meta"><span class="badge"><?= e($product['age_name']) ?></span><span class="badge badge--warm"><?= (int)$product['weight_grams'] ?>g</span><span class="badge"><?= $product['stock'] > 0 ? 'Em estoque' : 'Indisponível' ?></span></div>
        <div class="detail-price"><strong><?= money($product['price']) ?></strong><span>por unidade</span></div>
        <form method="post" action="actions/cart.php"><?= csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><input type="hidden" name="return_to" value="index.php?page=marmita&id=<?= (int)$product['id'] ?>"><button class="btn btn-primary" type="submit" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>Adicionar ao carrinho</button></form>
        <div class="info-block"><h3>Ingredientes</h3><ul class="info-list"><?php foreach (text_lines($product['ingredients'] ?? '') as $line): ?><li><?= e($line) ?></li><?php endforeach; ?></ul></div>
        <div class="info-block"><h3>Possíveis alergênicos</h3><p class="muted"><?= e($product['allergens'] ?: 'Não informado.') ?></p></div>
        <div class="info-block"><h3>Conservação</h3><p class="muted"><?= e($product['storage_info'] ?: 'Não informado.') ?></p></div>
        <div class="notice" style="margin-top:26px">As informações alimentares deste projeto são demonstrativas. Antes de comercializar, revise ingredientes, rotulagem, alergênicos, textura e adequação etária com nutricionista responsável e conforme as exigências sanitárias aplicáveis.</div>
    </div>
</div></section></main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
