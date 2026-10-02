<?php
/** @var PDO|null $pdo */
$user = current_user($pdo);
$flashes = pull_flashes();
$pageTitle = $pageTitle ?? 'Baby Food';
$fullTitle = $pageTitle === 'Baby Food' ? 'Baby Food — Nutrição infantil com cuidado' : $pageTitle . ' | Baby Food';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#15533F">
    <meta name="description" content="Baby Food: marmitas infantis, receitas, vídeos e contato com nutricionistas por fase da criança.">
    <title><?= e($fullTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css?v=2.0.0">
</head>
<body>
    <header class="site-header" data-header>
        <div class="announcement-bar">
            <div class="container announcement-inner">
                <span>Conteúdo e cardápios por fase da criança.</span>
                <a href="index.php?page=nutricionistas">Converse com nossos nutricionistas <span aria-hidden="true">↗</span></a>
            </div>
        </div>

        <div class="container header-main">
            <a href="index.php" class="brand" aria-label="Baby Food - Início">
                <span class="brand-mark">BF</span>
                <span class="brand-copy">
                    <strong>Baby Food</strong>
                    <small>Alimentação infantil</small>
                </span>
            </a>

            <button class="nav-toggle" type="button" data-nav-toggle aria-label="Abrir menu" aria-expanded="false">
                <span></span><span></span>
            </button>

            <nav class="main-nav" data-main-nav>
                <a class="<?= page_is('marmitas') || page_is('marmita') ? 'active' : '' ?>" href="index.php?page=marmitas">Marmitas</a>
                <a class="<?= page_is('receitas') || page_is('receita') ? 'active' : '' ?>" href="index.php?page=receitas">Receitas</a>
                <a class="<?= page_is('videos') || page_is('video') ? 'active' : '' ?>" href="index.php?page=videos">Vídeos</a>
                <a class="<?= page_is('nutricao') ? 'active' : '' ?>" href="index.php?page=nutricao">Nutrição</a>
                <a class="<?= page_is('nutricionistas') || page_is('chat') ? 'active' : '' ?>" href="index.php?page=nutricionistas">Nutricionistas</a>
            </nav>

            <div class="header-actions">
                <a class="icon-btn cart-button" href="index.php?page=carrinho" aria-label="Carrinho">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.4 10.1a2 2 0 0 0 1.95 1.54h7.83a2 2 0 0 0 1.94-1.52L21 7H7"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                    <?php if (cart_count() > 0): ?><span class="cart-count"><?= cart_count() ?></span><?php endif; ?>
                </a>

                <?php if ($user): ?>
                    <div class="user-menu" data-user-menu>
                        <button class="user-menu-trigger" type="button" data-user-trigger aria-expanded="false">
                            <span class="avatar-chip"><?= e(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?></span>
                            <span class="user-menu-copy"><strong><?= e(explode(' ', $user['name'])[0]) ?></strong><small><?= e($user['role'] === 'parent' ? 'Responsável' : ($user['role'] === 'nutritionist' ? 'Nutricionista' : 'Administrador')) ?></small></span>
                            <span class="chevron">⌄</span>
                        </button>
                        <div class="user-dropdown" data-user-dropdown>
                            <?php if ($user['role'] === 'admin'): ?>
                                <a href="index.php?page=admin">Painel administrativo</a>
                            <?php elseif ($user['role'] === 'nutritionist'): ?>
                                <a href="index.php?page=nutri-dashboard">Painel do nutricionista</a>
                            <?php else: ?>
                                <a href="index.php?page=minha-conta">Minha conta</a>
                                <a href="index.php?page=chat">Minhas conversas</a>
                            <?php endif; ?>
                            <form method="post" action="actions/logout.php">
                                <?= csrf_field() ?>
                                <button type="submit">Sair</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <a class="btn btn-ghost btn-sm hide-mobile" href="index.php?page=login">Entrar</a>
                    <a class="btn btn-primary btn-sm" href="index.php?page=cadastro">Criar conta</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if ($flashes): ?>
        <div class="toast-stack" data-toast-stack>
            <?php foreach ($flashes as $flash): ?>
                <div class="toast toast-<?= e($flash['type']) ?>" data-toast>
                    <span><?= e($flash['message']) ?></span>
                    <button type="button" data-toast-close aria-label="Fechar">×</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
