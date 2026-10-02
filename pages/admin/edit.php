<?php
$user = require_role($pdo, 'admin');
$type = $_GET['type'] ?? '';
$id = (int) ($_GET['id'] ?? 0);
$allowed = ['product','recipe','video','article'];
if (!in_array($type, $allowed, true) || $id <= 0) {
    redirect('index.php?page=admin');
}
$ageRanges = db_all($pdo, 'SELECT * FROM age_ranges ORDER BY sort_order');
$tableMap = ['product'=>'products','recipe'=>'recipes','video'=>'videos','article'=>'articles'];
$item = db_one($pdo, 'SELECT * FROM ' . $tableMap[$type] . ' WHERE id = ?', [$id]);
if (!$item) redirect('index.php?page=admin');
$pageTitle = 'Editar conteúdo';
require __DIR__ . '/../../partials/header.php';
$backTab = ['product'=>'products','recipe'=>'recipes','video'=>'videos','article'=>'articles'][$type];
?>
<main class="page-shell"><section class="section-sm"><div class="container" style="max-width:900px">
    <div class="dashboard-head"><div><div class="eyebrow">Administração</div><h1>Editar <?= e($type) ?></h1><p>Atualize os dados e salve as alterações.</p></div><a class="btn btn-ghost btn-sm" href="index.php?page=admin&tab=<?= e($backTab) ?>">Voltar</a></div>
    <div class="panel"><div class="panel-body">
        <form class="form-grid" method="post" action="actions/admin-update.php" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="type" value="<?= e($type) ?>">
            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">

            <?php if ($type === 'product'): ?>
                <label class="field"><span>Nome</span><input name="name" value="<?= e($item['name']) ?>" required></label>
                <label class="field"><span>Faixa etária</span><select name="age_range_id"><?php foreach($ageRanges as $age): ?><option value="<?= (int)$age['id'] ?>" <?= (int)$item['age_range_id']===(int)$age['id']?'selected':'' ?>><?= e($age['name']) ?></option><?php endforeach; ?></select></label>
                <label class="field"><span>Preço</span><input type="number" step="0.01" name="price" value="<?= e($item['price']) ?>" required></label>
                <label class="field"><span>Peso (g)</span><input type="number" name="weight_grams" value="<?= (int)$item['weight_grams'] ?>"></label>
                <label class="field"><span>Estoque</span><input type="number" name="stock" value="<?= (int)$item['stock'] ?>"></label>
                <label class="field"><span>Imagem atual/URL</span><input name="image_url" value="<?= e($item['image_url'] ?? '') ?>"></label>
                <label class="field field--wide"><span>Enviar nova imagem (opcional)</span><input type="file" name="image_file" accept="image/jpeg,image/png,image/webp"></label>
                <label class="field field--wide"><span>Descrição</span><textarea name="description" required><?= e($item['description']) ?></textarea></label>
                <label class="field field--wide"><span>Ingredientes</span><textarea name="ingredients"><?= e($item['ingredients'] ?? '') ?></textarea></label>
                <label class="field field--wide"><span>Alergênicos</span><textarea name="allergens"><?= e($item['allergens'] ?? '') ?></textarea></label>
                <label class="field field--wide"><span>Conservação</span><textarea name="storage_info"><?= e($item['storage_info'] ?? '') ?></textarea></label>
            <?php elseif ($type === 'recipe'): ?>
                <label class="field"><span>Título</span><input name="title" value="<?= e($item['title']) ?>" required></label>
                <label class="field"><span>Faixa etária</span><select name="age_range_id"><?php foreach($ageRanges as $age): ?><option value="<?= (int)$age['id'] ?>" <?= (int)$item['age_range_id']===(int)$age['id']?'selected':'' ?>><?= e($age['name']) ?></option><?php endforeach; ?></select></label>
                <label class="field"><span>Tempo (min)</span><input type="number" name="prep_minutes" value="<?= (int)$item['prep_minutes'] ?>"></label>
                <label class="field"><span>Imagem atual/URL</span><input name="image_url" value="<?= e($item['image_url'] ?? '') ?>"></label>
                <label class="field field--wide"><span>Enviar nova imagem (opcional)</span><input type="file" name="image_file" accept="image/jpeg,image/png,image/webp"></label>
                <label class="field field--wide"><span>Descrição</span><textarea name="description" required><?= e($item['description']) ?></textarea></label>
                <label class="field field--wide"><span>Ingredientes</span><textarea name="ingredients" required><?= e($item['ingredients']) ?></textarea></label>
                <label class="field field--wide"><span>Passos</span><textarea name="steps" required><?= e($item['steps']) ?></textarea></label>
                <label class="field field--wide"><span>Observações nutricionais</span><textarea name="nutrition_notes"><?= e($item['nutrition_notes'] ?? '') ?></textarea></label>
                <label class="field field--wide"><span>Alergênicos</span><textarea name="allergens"><?= e($item['allergens'] ?? '') ?></textarea></label>
                <label class="field"><span>Status</span><select name="status"><option value="draft" <?= $item['status']==='draft'?'selected':'' ?>>Rascunho</option><option value="pending" <?= $item['status']==='pending'?'selected':'' ?>>Pendente</option><option value="published" <?= $item['status']==='published'?'selected':'' ?>>Publicado</option></select></label>
            <?php elseif ($type === 'video'): ?>
                <label class="field"><span>Título</span><input name="title" value="<?= e($item['title']) ?>" required></label>
                <label class="field"><span>Faixa etária</span><select name="age_range_id"><?php foreach($ageRanges as $age): ?><option value="<?= (int)$age['id'] ?>" <?= (int)$item['age_range_id']===(int)$age['id']?'selected':'' ?>><?= e($age['name']) ?></option><?php endforeach; ?></select></label>
                <label class="field"><span>Duração (min)</span><input type="number" name="duration_minutes" value="<?= (int)$item['duration_minutes'] ?>"></label>
                <label class="field"><span>URL incorporada</span><input name="video_url" value="<?= e($item['video_url']) ?>" required></label>
                <label class="field"><span>Miniatura atual/URL</span><input name="thumbnail_url" value="<?= e($item['thumbnail_url'] ?? '') ?>"></label>
                <label class="field"><span>Enviar nova miniatura</span><input type="file" name="image_file" accept="image/jpeg,image/png,image/webp"></label>
                <label class="field field--wide"><span>Descrição</span><textarea name="description" required><?= e($item['description']) ?></textarea></label>
                <label class="field"><span>Status</span><select name="status"><option value="draft" <?= $item['status']==='draft'?'selected':'' ?>>Rascunho</option><option value="pending" <?= $item['status']==='pending'?'selected':'' ?>>Pendente</option><option value="published" <?= $item['status']==='published'?'selected':'' ?>>Publicado</option></select></label>
            <?php elseif ($type === 'article'): ?>
                <label class="field"><span>Título</span><input name="title" value="<?= e($item['title']) ?>" required></label>
                <label class="field"><span>Categoria</span><select name="category"><option value="nutrition" <?= $item['category']==='nutrition'?'selected':'' ?>>Nutrição</option><option value="protein" <?= $item['category']==='protein'?'selected':'' ?>>Proteínas</option><option value="vitamins" <?= $item['category']==='vitamins'?'selected':'' ?>>Vitaminas</option><option value="food" <?= $item['category']==='food'?'selected':'' ?>>Alimentos</option></select></label>
                <label class="field field--wide"><span>Resumo</span><textarea name="excerpt" required><?= e($item['excerpt']) ?></textarea></label>
                <label class="field field--wide"><span>Conteúdo</span><textarea name="content" required><?= e($item['content']) ?></textarea></label>
                <label class="field"><span>Status</span><select name="status"><option value="draft" <?= $item['status']==='draft'?'selected':'' ?>>Rascunho</option><option value="published" <?= $item['status']==='published'?'selected':'' ?>>Publicado</option></select></label>
            <?php endif; ?>
            <button class="btn btn-primary field--wide" type="submit">Salvar alterações</button>
        </form>
    </div></div>
</div></section></main>
<?php require __DIR__ . '/../../partials/footer.php'; ?>
