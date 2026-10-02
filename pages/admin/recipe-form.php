<form class="form-grid" method="post" action="actions/admin-save.php" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="entity" value="recipe">
    <label class="field"><span>Título</span><input name="title" required></label>
    <label class="field"><span>Faixa etária</span><select name="age_range_id"><?php foreach($ageRanges as $age): ?><option value="<?= (int)$age['id'] ?>"><?= e($age['name']) ?></option><?php endforeach; ?></select></label>
    <label class="field"><span>Tempo (min)</span><input type="number" name="prep_minutes" min="0"></label>
    <label class="field"><span>Imagem (URL/caminho)</span><input name="image_url" placeholder="assets/img/... ou URL"></label><label class="field"><span>Ou enviar imagem</span><input type="file" name="image_file" accept="image/jpeg,image/png,image/webp"></label>
    <label class="field field--wide"><span>Descrição</span><textarea name="description" required></textarea></label>
    <label class="field field--wide"><span>Ingredientes (um por linha)</span><textarea name="ingredients" required></textarea></label>
    <label class="field field--wide"><span>Passos (um por linha)</span><textarea name="steps" required></textarea></label>
    <label class="field field--wide"><span>Observações nutricionais</span><textarea name="nutrition_notes"></textarea></label>
    <label class="field field--wide"><span>Alergênicos e cuidados</span><textarea name="allergens"></textarea></label>
    <button class="btn btn-primary field--wide" type="submit">Cadastrar receita</button>
</form>
