<?php

declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$user = require_role($pdo, 'admin');
if (!is_post()) redirect('../index.php?page=admin');
verify_csrf();
$entity = $_POST['entity'] ?? '';

function slugify_admin(string $value): string {
    $value = trim(mb_strtolower($value));
    $map = ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c'];
    $value = strtr($value,$map);
    $value = preg_replace('/[^a-z0-9]+/','-',$value) ?? '';
    return trim($value,'-') . '-' . bin2hex(random_bytes(2));
}

function admin_upload_image(string $fieldName = 'image_file'): ?string
{
    if (empty($_FILES[$fieldName]) || ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Falha no envio da imagem.');
    if ($file['size'] > 4 * 1024 * 1024) throw new RuntimeException('A imagem deve ter no máximo 4 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!isset($extensions[$mime])) throw new RuntimeException('Formato de imagem não permitido.');
    $filename = 'img-' . bin2hex(random_bytes(10)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../uploads/' . $filename)) throw new RuntimeException('Não foi possível salvar a imagem.');
    return 'uploads/' . $filename;
}

function valid_embed_url(string $url): bool
{
    if ($url === 'demo') return true;
    if (!filter_var($url, FILTER_VALIDATE_URL)) return false;
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true);
}

try {
    if ($entity === 'product') {
        $image = admin_upload_image() ?: (trim((string)($_POST['image_url'] ?? '')) ?: null);
        $pdo->prepare('INSERT INTO products (name,slug,description,age_range_id,price,weight_grams,ingredients,allergens,storage_info,stock,image_url) VALUES (?,?,?,?,?,?,?,?,?,?,?)')->execute([
            trim($_POST['name']), slugify_admin($_POST['name']), trim($_POST['description']), (int)$_POST['age_range_id'], (float)$_POST['price'], (int)($_POST['weight_grams']??0), trim((string)($_POST['ingredients']??'')), trim((string)($_POST['allergens']??'')), trim((string)($_POST['storage_info']??'')), (int)($_POST['stock']??0), $image
        ]);
        flash('success','Marmita cadastrada.'); redirect('../index.php?page=admin&tab=products');
    }
    if ($entity === 'toggle-product') {
        $pdo->prepare('UPDATE products SET active = IF(active=1,0,1) WHERE id=?')->execute([(int)$_POST['id']]);
        flash('success','Status da marmita atualizado.'); redirect('../index.php?page=admin&tab=products');
    }
    if ($entity === 'recipe') {
        $image = admin_upload_image() ?: (trim((string)($_POST['image_url'] ?? '')) ?: null);
        $pdo->prepare('INSERT INTO recipes (title,slug,description,age_range_id,prep_minutes,ingredients,steps,nutrition_notes,allergens,image_url,created_by,reviewed_by,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,"published")')->execute([trim($_POST['title']),slugify_admin($_POST['title']),trim($_POST['description']),(int)$_POST['age_range_id'],(int)($_POST['prep_minutes']??0),trim($_POST['ingredients']),trim($_POST['steps']),trim((string)($_POST['nutrition_notes']??'')),trim((string)($_POST['allergens']??'')),$image,(int)$user['id'],(int)$user['id']]);
        flash('success','Receita publicada.'); redirect('../index.php?page=admin&tab=recipes');
    }
    if ($entity === 'video') {
        $videoUrl = trim((string)($_POST['video_url'] ?? ''));
        if (!valid_embed_url($videoUrl)) throw new RuntimeException('Informe uma URL de vídeo http/https válida.');
        $image = admin_upload_image() ?: (trim((string)($_POST['thumbnail_url'] ?? '')) ?: null);
        $pdo->prepare('INSERT INTO videos (title,slug,description,age_range_id,duration_minutes,video_url,thumbnail_url,created_by,status) VALUES (?,?,?,?,?,?,?,?,"published")')->execute([trim($_POST['title']),slugify_admin($_POST['title']),trim($_POST['description']),(int)$_POST['age_range_id'],(int)($_POST['duration_minutes']??0),$videoUrl,$image,(int)$user['id']]);
        flash('success','Vídeo publicado.'); redirect('../index.php?page=admin&tab=videos');
    }
    if ($entity === 'article') {
        $pdo->prepare('INSERT INTO articles (title,slug,category,excerpt,content,status) VALUES (?,?,?,?,?,"published")')->execute([trim($_POST['title']),slugify_admin($_POST['title']),$_POST['category'],trim($_POST['excerpt']),trim($_POST['content'])]);
        flash('success','Conteúdo publicado.'); redirect('../index.php?page=admin&tab=articles');
    }
    if ($entity === 'nutritionist') {
        $email=trim($_POST['email']);
        if(db_value($pdo,'SELECT COUNT(*) FROM users WHERE email=?',[$email])) throw new RuntimeException('Já existe um usuário com esse e-mail.');
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,"nutritionist")')->execute([trim($_POST['name']),$email,password_hash($_POST['password'],PASSWORD_DEFAULT)]);
        $uid=(int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO nutritionists (user_id,crn,specialty,bio,availability_note) VALUES (?,?,?,?,?)')->execute([$uid,trim($_POST['crn']),trim($_POST['specialty']),trim((string)($_POST['bio']??'')),trim((string)($_POST['availability_note']??''))]);
        $pdo->commit(); flash('success','Nutricionista cadastrado.'); redirect('../index.php?page=admin&tab=nutritionists');
    }
    if ($entity === 'order-status') {
        $status=$_POST['status']??'received'; if(!in_array($status,['received','preparing','out_for_delivery','delivered','cancelled'],true)) $status='received';
        $order=db_one($pdo,'SELECT * FROM orders WHERE id=?',[(int)$_POST['id']]);
        if($order){$pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$status,(int)$order['id']]);$pdo->prepare('INSERT INTO notifications (user_id,title,body,link) VALUES (?,?,?,?)')->execute([(int)$order['user_id'],'Atualização do pedido','O pedido #'.$order['id'].' agora está com status: '.$status.'.','index.php?page=minha-conta&tab=pedidos']);}
        flash('success','Pedido atualizado.'); redirect('../index.php?page=admin&tab=orders');
    }
} catch(Throwable $e) {
    if($pdo->inTransaction()) $pdo->rollBack();
    flash('danger','Não foi possível salvar: '.$e->getMessage());
}
redirect('../index.php?page=admin');
