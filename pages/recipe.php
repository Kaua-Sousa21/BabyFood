<?php
$id = (int)($_GET['id'] ?? 0);
$recipe = db_one($pdo, 'SELECT r.*, a.name AS age_name, u.name AS author_name FROM recipes r JOIN age_ranges a ON a.id = r.age_range_id LEFT JOIN users u ON u.id = r.created_by WHERE r.id = ? AND r.status = "published"', [$id]);
if (!$recipe) { redirect('index.php?page=receitas'); }
$pageTitle = $recipe['title'];
$video = db_one($pdo, 'SELECT * FROM videos WHERE related_recipe_id = ? AND status = "published" LIMIT 1', [$id]);
require __DIR__ . '/../partials/header.php';
?>
<main class="page-shell"><section class="section"><div class="container detail-grid"><div><div class="detail-media" style="position:relative;top:auto"><img src="<?= e(product_image($recipe['image_url'])) ?>" alt="<?= e($recipe['title']) ?>"></div><?php if($video): ?><div style="margin-top:18px"><a class="btn btn-primary" href="index.php?page=video&id=<?= (int)$video['id'] ?>">▶ Assistir receita em vídeo</a></div><?php endif; ?></div>
<div class="detail-copy"><div class="eyebrow">Receita Baby Food</div><h1><?= e($recipe['title']) ?></h1><p><?= e($recipe['description']) ?></p><div class="detail-meta"><span class="badge"><?= e($recipe['age_name']) ?></span><span class="badge badge--warm"><?= (int)$recipe['prep_minutes'] ?> min</span><?php if($recipe['author_name']): ?><span class="badge">Por <?= e($recipe['author_name']) ?></span><?php endif; ?></div>
<div class="info-block"><h3>Ingredientes</h3><ul class="info-list"><?php foreach(text_lines($recipe['ingredients']) as $line): ?><li><?= e($line) ?></li><?php endforeach; ?></ul></div>
<div class="info-block"><h3>Modo de preparo</h3><ol class="info-list" style="counter-reset:item"><?php foreach(text_lines($recipe['steps']) as $line): ?><li><?= e($line) ?></li><?php endforeach; ?></ol></div>
<div class="info-block"><h3>Observações nutricionais</h3><p class="muted"><?= e($recipe['nutrition_notes'] ?: 'Sem observações cadastradas.') ?></p></div>
<div class="info-block"><h3>Alergênicos e cuidados</h3><p class="muted"><?= e($recipe['allergens'] ?: 'Sem informação cadastrada.') ?></p></div>
<div class="notice" style="margin-top:26px">Receitas infantis devem considerar desenvolvimento, textura segura, alergias, histórico e orientação individual. Antes de uso real, valide este conteúdo com o nutricionista responsável pelo Baby Food.</div>
</div></div></section></main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
