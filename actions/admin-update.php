<?php

declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
require_role($pdo, 'admin');
if (!is_post()) redirect('../index.php?page=admin');
verify_csrf();
$type = $_POST['type'] ?? '';
$id = (int) ($_POST['id'] ?? 0);
$allowed = ['product','recipe','video','article'];
if (!in_array($type, $allowed, true) || $id <= 0) redirect('../index.php?page=admin');

function save_uploaded_image(string $fieldName): ?string
{
    if (empty($_FILES[$fieldName]) || ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Falha no envio da imagem.');
    if ($file['size'] > 4 * 1024 * 1024) throw new RuntimeException('A imagem deve ter no máximo 4 MB.');
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $extensions = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!isset($extensions[$mime])) throw new RuntimeException('Formato de imagem não permitido.');
    $filename = 'img-' . bin2hex(random_bytes(10)) . '.' . $extensions[$mime];
    $target = __DIR__ . '/../uploads/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) throw new RuntimeException('Não foi possível salvar a imagem.');
    return 'uploads/' . $filename;
}

$tab = ['product'=>'products','recipe'=>'recipes','video'=>'videos','article'=>'articles'][$type];
try {
    $uploaded = save_uploaded_image('image_file');
    if ($type === 'product') {
        $image = $uploaded ?: (trim((string)($_POST['image_url'] ?? '')) ?: null);
        $pdo->prepare('UPDATE products SET name=?,description=?,age_range_id=?,price=?,weight_grams=?,ingredients=?,allergens=?,storage_info=?,stock=?,image_url=? WHERE id=?')->execute([trim($_POST['name']),trim($_POST['description']),(int)$_POST['age_range_id'],(float)$_POST['price'],(int)($_POST['weight_grams']??0),trim((string)($_POST['ingredients']??'')),trim((string)($_POST['allergens']??'')),trim((string)($_POST['storage_info']??'')),(int)($_POST['stock']??0),$image,$id]);
    } elseif ($type === 'recipe') {
        $image = $uploaded ?: (trim((string)($_POST['image_url'] ?? '')) ?: null);
        $status = in_array($_POST['status']??'published',['draft','pending','published'],true) ? $_POST['status'] : 'published';
        $pdo->prepare('UPDATE recipes SET title=?,description=?,age_range_id=?,prep_minutes=?,ingredients=?,steps=?,nutrition_notes=?,allergens=?,image_url=?,status=? WHERE id=?')->execute([trim($_POST['title']),trim($_POST['description']),(int)$_POST['age_range_id'],(int)($_POST['prep_minutes']??0),trim($_POST['ingredients']),trim($_POST['steps']),trim((string)($_POST['nutrition_notes']??'')),trim((string)($_POST['allergens']??'')),$image,$status,$id]);
    } elseif ($type === 'video') {
        $image = $uploaded ?: (trim((string)($_POST['thumbnail_url'] ?? '')) ?: null);
        $status = in_array($_POST['status']??'published',['draft','pending','published'],true) ? $_POST['status'] : 'published';
        $videoUrl = trim((string)($_POST['video_url'] ?? ''));
        if ($videoUrl !== 'demo' && (!filter_var($videoUrl, FILTER_VALIDATE_URL) || !in_array(strtolower((string)parse_url($videoUrl, PHP_URL_SCHEME)), ['http','https'], true))) {
            throw new RuntimeException('Informe uma URL de vídeo http/https válida.');
        }
        $pdo->prepare('UPDATE videos SET title=?,description=?,age_range_id=?,duration_minutes=?,video_url=?,thumbnail_url=?,status=? WHERE id=?')->execute([trim($_POST['title']),trim($_POST['description']),(int)$_POST['age_range_id'],(int)($_POST['duration_minutes']??0),$videoUrl,$image,$status,$id]);
    } elseif ($type === 'article') {
        $status = in_array($_POST['status']??'published',['draft','published'],true) ? $_POST['status'] : 'published';
        $pdo->prepare('UPDATE articles SET title=?,category=?,excerpt=?,content=?,status=? WHERE id=?')->execute([trim($_POST['title']),$_POST['category'],trim($_POST['excerpt']),trim($_POST['content']),$status,$id]);
    }
    flash('success','Alterações salvas.');
} catch (Throwable $e) {
    flash('danger','Não foi possível atualizar: ' . $e->getMessage());
}
redirect('../index.php?page=admin&tab=' . $tab);
