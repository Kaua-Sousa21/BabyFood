<?php
if ($pdo && is_logged_in($pdo)) {
    redirect('index.php?page=minha-conta');
}
$pageTitle = 'Criar conta';
$errors = [];

if (is_post()) {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');

    if (mb_strlen($name) < 3) $errors[] = 'Informe seu nome completo.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Informe um e-mail válido.';
    if (strlen($password) < 8) $errors[] = 'A senha deve ter pelo menos 8 caracteres.';
    if ($password !== $confirm) $errors[] = 'As senhas não coincidem.';
    if ($pdo && db_value($pdo, 'SELECT COUNT(*) FROM users WHERE email = ?', [$email])) $errors[] = 'Já existe uma conta com este e-mail.';

    if (!$errors && $pdo) {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, "parent", ?)');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $phone ?: null]);
        $user = db_one($pdo, 'SELECT * FROM users WHERE id = ?', [(int) $pdo->lastInsertId()]);
        login_user($user);
        flash('success', 'Conta criada. Agora cadastre o perfil da criança.');
        redirect('index.php?page=minha-conta&tab=criancas');
    }
}
require __DIR__ . '/../../partials/header.php';
?>
<main class="auth-shell">
    <section class="auth-visual">
        <a href="index.php" class="brand brand--light"><span class="brand-mark">BF</span><span class="brand-copy"><strong>Baby Food</strong><small>Alimentação infantil</small></span></a>
        <h1>Comece pelo responsável. Depois, cadastre a criança.</h1>
        <p>As crianças não precisam criar contas próprias. O responsável gerencia perfis, pedidos e conversas.</p>
    </section>
    <section class="auth-form-wrap">
        <div class="auth-form">
            <div class="eyebrow">Cadastro do responsável</div>
            <h2>Criar conta</h2>
            <p>Use dados do responsável pela criança.</p>
            <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
            <form method="post" class="form-grid">
                <?= csrf_field() ?>
                <label class="field"><span>Nome completo</span><input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" required></label>
                <label class="field"><span>E-mail</span><input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></label>
                <label class="field"><span>Telefone</span><input type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>"></label>
                <label class="field"><span>Senha</span><input type="password" name="password" minlength="8" required></label>
                <label class="field"><span>Confirmar senha</span><input type="password" name="password_confirm" minlength="8" required></label>
                <button class="btn btn-primary btn-block" type="submit">Criar minha conta</button>
            </form>
            <div class="auth-footer">Já tem uma conta? <a class="text-link" href="index.php?page=login">Entrar</a></div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../../partials/footer.php'; ?>
