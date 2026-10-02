<?php
$pageTitle = 'Marmitas infantis';
$ageRanges = db_all($pdo, 'SELECT * FROM age_ranges ORDER BY sort_order');
$selectedAge = isset($_GET['idade']) ? (int) $_GET['idade'] : 0;
$sql = 'SELECT p.*, a.name AS age_name FROM products p JOIN age_ranges a ON a.id = p.age_range_id WHERE p.active = 1';
$params = [];
if ($selectedAge > 0) {
    $sql .= ' AND p.age_range_id = ?';
    $params[] = $selectedAge;
}
$sql .= ' ORDER BY a.sort_order, p.id DESC';
$products = db_all($pdo, $sql, $params);
require __DIR__ . '/../partials/header.php';
?>
<main class="page-shell">
    <section class="page-hero">
        <div class="container page-hero-grid">
            <div><div class="eyebrow">Loja Baby Food</div><h1>Marmitas pensadas para cada fase.</h1><p>Filtre por idade e encontre refeições com apresentação clara de ingredientes, peso, conservação e informações relevantes.</p></div>
            <div class="page-hero-note"><strong>Faixas do projeto</strong><span>6–8 meses · 8–10 meses · 10–12 meses · 1–3 anos</span></div>
        </div>
    </section>
    <section class="section-sm">
        <div class="container">
            <div class="filter-bar">
                <a class="filter-chip <?= $selectedAge === 0 ? 'active' : '' ?>" href="index.php?page=marmitas">Todas</a>
                <?php foreach ($ageRanges as $range): ?><a class="filter-chip <?= $selectedAge === (int)$range['id'] ? 'active' : '' ?>" href="index.php?page=marmitas&idade=<?= (int)$range['id'] ?>"><?= e($range['name']) ?></a><?php endforeach; ?>
            </div>
            <?php if (!$products): ?>
                <div class="empty-state"><h2>Nenhuma marmita nesta faixa.</h2><p>Cadastre novos produtos no painel administrativo.</p></div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <article class="product-card">
                            <a class="card-media" href="index.php?page=marmita&id=<?= (int)$product['id'] ?>"><img src="<?= e(product_image($product['image_url'])) ?>" alt="<?= e($product['name']) ?>"><span class="badge card-media-badge"><?= e($product['age_name']) ?></span></a>
                            <div class="card-body">
                                <div class="card-kicker"><span><?= (int)$product['weight_grams'] ?>g</span><span><?= $product['stock'] > 0 ? 'Disponível' : 'Indisponível' ?></span></div>
                                <h3 class="card-title"><a href="index.php?page=marmita&id=<?= (int)$product['id'] ?>"><?= e($product['name']) ?></a></h3>
                                <p><?= e(excerpt($product['description'], 110)) ?></p>
                                <div class="card-footer"><div class="price"><strong><?= money($product['price']) ?></strong><small>por unidade</small></div>
                                    <form method="post" action="actions/cart.php"><?= csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><input type="hidden" name="return_to" value="index.php?page=marmitas<?= $selectedAge ? '&idade=' . $selectedAge : '' ?>"><button class="btn btn-primary btn-sm" type="submit" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>Adicionar</button></form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
