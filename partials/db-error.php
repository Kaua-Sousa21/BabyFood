<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurar Baby Food</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="empty-state-page">
    <main class="empty-state">
        <span class="brand-mark">BF</span>
        <div class="eyebrow">Configuração necessária</div>
        <h1>O banco de dados ainda não está conectado.</h1>
        <p>Abra o instalador para informar os dados do MySQL e preparar o Baby Food automaticamente.</p>
        <a class="btn btn-primary" href="install.php">Abrir instalador</a>
        <?php if (!empty($dbError)): ?>
            <details>
                <summary>Detalhes técnicos</summary>
                <code><?= e($dbError) ?></code>
            </details>
        <?php endif; ?>
    </main>
</body>
</html>
