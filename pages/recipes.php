<?php
$pageTitle = 'Receitas por faixa etária';
$ageRanges = db_all($pdo, 'SELECT * FROM age_ranges ORDER BY sort_order');
$selectedAge = isset($_GET['idade']) ? (int) $_GET['idade'] : 0;
$sql = 'SELECT r.*, a.name AS age_name FROM recipes r JOIN age_ranges a ON a.id = r.age_range_id WHERE r.status = "published"';
$params = [];
if ($selectedAge) { $sql .= ' AND r.age_range_id = ?'; $params[] = $selectedAge; }
$sql .= ' ORDER BY a.sort_order, r.id DESC';
$recipes = db_all($pdo, $sql, $params);
require __DIR__ . '/../partials/header.php';
?>
<main class="page-shell">
    <section class="page-hero">
        <div class="container page-hero-grid">
            <div>
                <div class="eyebrow">Biblioteca de receitas</div>
                <h1>Receitas organizadas pela fase da criança.</h1>
                <p>Cada receita pode reunir ingredientes, preparo, observações, alergênicos e um vídeo relacionado.</p>
            </div>
            <div class="page-hero-note">
                <strong>Conteúdo profissional</strong>
                <span>Use o painel para revisar e publicar apenas materiais validados pela equipe responsável.</span>
            </div>
        </div>
    </section>
    <section class="section-sm">
        <div class="container">
            <div class="filter-bar">
                <a class="filter-chip <?= !$selectedAge ? 'active' : '' ?>" href="index.php?page=receitas">Todas</a>
                <?php foreach($ageRanges as $range): ?>
                    <a class="filter-chip <?= $selectedAge === (int)$range['id'] ? 'active' : '' ?>" href="index.php?page=receitas&idade=<?= (int)$range['id'] ?>">
                        <?= e($range['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="recipe-grid">
                <?php foreach($recipes as $recipe): ?>
                    <article class="recipe-card">
                        <a class="card-media" href="index.php?page=receita&id=<?= (int)$recipe['id'] ?>">
                            <img src="<?= e(product_image($recipe['image_url'])) ?>" alt="<?= e($recipe['title']) ?>">
                            <span class="badge card-media-badge">
                                <?= e($recipe['age_name']) ?>
                            </span>
                        </a>
                        <div class="card-body">
                            <div class="card-kicker">
                                <span>
                                    <?= (int)$recipe['prep_minutes'] ?> min</span>
                                    <span>Receita</span>
                                </div>
                                <h3 class="card-title">
                                    <?= e($recipe['title']) ?>
                                </h3>
                                <p>
                                    <?= e(excerpt($recipe['description'], 110)) ?>
                                </p>
                                <a class="text-link" href="index.php?page=receita&id=<?= (int)$recipe['id'] ?>">Ver receita →</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>
    <?php require __DIR__ . '/../partials/footer.php'; ?>
