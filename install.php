<?php

declare(strict_types=1);

require_once __DIR__ . '/app/database.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/database/seed.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$lockFile = __DIR__ . '/config/installed.lock';
$errors = [];
$success = null;
$credentials = null;

if (is_file($lockFile)) {
    $success = 'O Baby Food já foi instalado. Exclua config/installed.lock apenas se realmente quiser executar a instalação novamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !is_file($lockFile)) {
    verify_csrf();
    if (!extension_loaded('pdo_mysql')) {
        $errors[] = 'A extensão pdo_mysql do PHP precisa estar habilitada na hospedagem.';
    }
    $host = trim($_POST['host'] ?? 'localhost');
    $port = (int) ($_POST['port'] ?? 3306);
    $name = trim($_POST['name'] ?? '');
    $user = trim($_POST['user'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($host === '' || $name === '' || $user === '') {
        $errors[] = 'Preencha host, banco e usuário do MySQL.';
    }

    if (!$errors) {
        try {
            $config = [
                'host' => $host,
                'port' => $port,
                'name' => $name,
                'user' => $user,
                'password' => $password,
                'charset' => 'utf8mb4',
            ];

            $pdo = db_connect($config);
            $schema = file_get_contents(__DIR__ . '/database/schema.sql');
            $statements = preg_split('/;\s*(?:\r?\n|$)/', $schema) ?: [];
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if ($statement !== '') {
                    $pdo->exec($statement);
                }
            }
            $credentials = seed_database($pdo);

            $configContent = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($config, true) . ";\n";
            file_put_contents(__DIR__ . '/config/database.local.php', $configContent, LOCK_EX);
            file_put_contents($lockFile, 'Installed at ' . date(DATE_ATOM), LOCK_EX);
            $success = 'Instalação concluída com sucesso.';
        } catch (Throwable $exception) {
            $errors[] = 'Não foi possível instalar: ' . $exception->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instalar Baby Food</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="install-page">
    <main class="install-shell">
        <section class="install-brand">
            <a href="index.php" class="brand brand--large">
                <span class="brand-mark">BF</span>
                <span><strong>Baby Food</strong><small>Plataforma de alimentação infantil</small></span>
            </a>
            <h1>Instalação em poucos passos.</h1>
            <p>Crie o banco MySQL na sua hospedagem, informe os dados abaixo e o sistema prepara as tabelas e os acessos de demonstração.</p>
            <div class="install-note">
                <strong>Importante</strong>
                <span>Depois de publicar, troque as senhas de demonstração e revise todo conteúdo alimentar com profissionais habilitados.</span>
            </div>
        </section>

        <section class="install-card">
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endforeach; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>

                <?php if ($credentials): ?>
                    <div class="credential-list">
                        <h2>Acessos de demonstração</h2>
                        <?php foreach ($credentials as $role => [$email, $pass]): ?>
                            <div>
                                <span><?= e(ucfirst($role)) ?></span>
                                <code><?= e($email) ?></code>
                                <code><?= e($pass) ?></code>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <a class="btn btn-primary btn-block" href="index.php">Abrir Baby Food</a>
            <?php else: ?>
                <div class="eyebrow">Configuração inicial</div>
                <h2>Conectar ao MySQL</h2>
                <form method="post" class="form-grid">
                    <?= csrf_field() ?>
                    <label class="field field--wide">
                        <span>Host</span>
                        <input type="text" name="host" value="<?= e($_POST['host'] ?? 'localhost') ?>" required>
                    </label>
                    <label class="field">
                        <span>Porta</span>
                        <input type="number" name="port" value="<?= e($_POST['port'] ?? '3306') ?>" required>
                    </label>
                    <label class="field">
                        <span>Nome do banco</span>
                        <input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" required>
                    </label>
                    <label class="field field--wide">
                        <span>Usuário MySQL</span>
                        <input type="text" name="user" value="<?= e($_POST['user'] ?? '') ?>" required>
                    </label>
                    <label class="field field--wide">
                        <span>Senha MySQL</span>
                        <input type="password" name="password">
                    </label>
                    <button class="btn btn-primary btn-block field--wide" type="submit">Instalar sistema</button>
                </form>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
