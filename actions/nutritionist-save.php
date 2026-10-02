<?php

declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$user=require_role($pdo,'nutritionist');
if(!is_post()) redirect('../index.php?page=nutri-dashboard');
verify_csrf();
$entity=$_POST['entity']??'';
function nutri_slug(string $value): string {
    $value=trim(mb_strtolower($value));
    $value=strtr($value,['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c']);
    $value=preg_replace('/[^a-z0-9]+/','-',$value)??'';
    return trim($value,'-').'-'.bin2hex(random_bytes(2));
}

function nutri_upload_image(string $fieldName = 'image_file'): ?string {
    if (empty($_FILES[$fieldName]) || ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Falha no envio da imagem.');
    if ($file['size'] > 4 * 1024 * 1024) throw new RuntimeException('A imagem deve ter no máximo 4 MB.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if(!isset($ext[$mime])) throw new RuntimeException('Formato de imagem não permitido.');
    $name='img-'.bin2hex(random_bytes(10)).'.'.$ext[$mime];
    if(!move_uploaded_file($file['tmp_name'],__DIR__.'/../uploads/'.$name)) throw new RuntimeException('Não foi possível salvar a imagem.');
    return 'uploads/'.$name;
}
function nutri_valid_embed(string $url): bool {
    if(!filter_var($url,FILTER_VALIDATE_URL)) return false;
    return in_array(strtolower((string)parse_url($url,PHP_URL_SCHEME)),['http','https'],true);
}
try{
    if($entity==='recipe'){
        $image=nutri_upload_image() ?: (trim((string)($_POST['image_url']??'')) ?: null);
        $pdo->prepare('INSERT INTO recipes (title,slug,description,age_range_id,prep_minutes,ingredients,steps,nutrition_notes,allergens,image_url,created_by,reviewed_by,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,"published")')->execute([trim($_POST['title']),nutri_slug($_POST['title']),trim($_POST['description']),(int)$_POST['age_range_id'],(int)($_POST['prep_minutes']??0),trim($_POST['ingredients']),trim($_POST['steps']),trim((string)($_POST['nutrition_notes']??'')),trim((string)($_POST['allergens']??'')),$image,(int)$user['id'],(int)$user['id']]);
        flash('success','Receita publicada.');redirect('../index.php?page=nutri-dashboard&tab=recipes');
    }
    if($entity==='video'){
        $videoUrl=trim((string)($_POST['video_url']??''));
        if(!nutri_valid_embed($videoUrl)) throw new RuntimeException('Informe uma URL de vídeo http/https válida.');
        $image=nutri_upload_image() ?: (trim((string)($_POST['thumbnail_url']??'')) ?: null);
        $pdo->prepare('INSERT INTO videos (title,slug,description,age_range_id,duration_minutes,video_url,thumbnail_url,created_by,status) VALUES (?,?,?,?,?,?,?,?,"published")')->execute([trim($_POST['title']),nutri_slug($_POST['title']),trim($_POST['description']),(int)$_POST['age_range_id'],(int)($_POST['duration_minutes']??0),$videoUrl,$image,(int)$user['id']]);
        flash('success','Vídeo publicado.');redirect('../index.php?page=nutri-dashboard&tab=videos');
    }
    if($entity==='profile'){
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE users SET name=?,phone=? WHERE id=?')->execute([trim($_POST['name']),trim((string)($_POST['phone']??''))?:null,(int)$user['id']]);
        $pdo->prepare('UPDATE nutritionists SET crn=?,specialty=?,bio=?,availability_note=? WHERE user_id=?')->execute([trim($_POST['crn']),trim($_POST['specialty']),trim((string)($_POST['bio']??'')),trim((string)($_POST['availability_note']??'')),(int)$user['id']]);
        $pdo->commit();flash('success','Perfil atualizado.');redirect('../index.php?page=nutri-dashboard&tab=profile');
    }
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash('danger','Não foi possível salvar: '.$e->getMessage());}
redirect('../index.php?page=nutri-dashboard');
