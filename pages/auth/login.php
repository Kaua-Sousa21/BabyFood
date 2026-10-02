<?php
if ($pdo && is_logged_in($pdo)) {
    $user = current_user($pdo);
    redirect($user['role'] === 'admin' ? 'index.php?page=admin' : ($user['role'] === 'nutritionist' ? 'index.php?page=nutri-dashboard' : 'index.php?page=minha-conta'));
}
$pageTitle = 'Entrar';
$errors = [];
$returnTo = $_GET['return_to'] ?? 'index.php?page=minha-conta';

if (is_post()) {
    verify_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $returnTo = safe_return_to('index.php?page=minha-conta');

    if (!$pdo) {
        $errors[] = 'Banco de dados indisponível.';
    } else {
        $user = db_one($pdo, 'SELECT * FROM users WHERE email = ? AND status = "active" LIMIT 1', [$email]);
        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'E-mail ou senha incorretos.';
        } else {
            login_user($user);
            flash('success', 'Bem-vindo ao Baby Food.');
            if ($returnTo !== 'index.php?page=minha-conta') {
                redirect($returnTo);
            }
            redirect($user['role'] === 'admin' ? 'index.php?page=admin' : ($user['role'] === 'nutritionist' ? 'index.php?page=nutri-dashboard' : 'index.php?page=minha-conta'));
        }
    }
}
require __DIR__ . '/../../partials/header.php';
?>
<main class="auth-shell">
    <section class="auth-visual">
        <a href="index.php" class="brand brand--light"><span class="brand-mark">BF</span><span class="brand-copy"><strong>Baby Food</strong><small>Alimentação infantil</small></span></a>
        <h1>Uma conta para organizar toda a rotina.</h1>
        <p>Pedidos, perfis das crianças, receitas e conversas com nutricionistas em um único lugar.</p>
    </section>
    <section class="auth-form-wrap">
        <div class="auth-form">
            <div class="eyebrow">Acesso</div>
            <h2>Entre na sua conta</h2>
            <p>Use seu e-mail e senha cadastrados.</p>
            <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
            <form method="post" class="form-grid">
                <?= csrf_field() ?>
                <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                <label class="field"><span>E-mail</span><input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" autocomplete="email" required></label>
                <label class="field"><span>Senha</span><input type="password" name="password" autocomplete="current-password" required></label>
                <button class="btn btn-primary btn-block" type="submit">Entrar</button>
            </form>
            <div class="auth-footer">Ainda não tem conta? <a class="text-link" href="index.php?page=cadastro">Criar conta</a></div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../../partials/footer.php'; ?>
