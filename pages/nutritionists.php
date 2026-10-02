<?php
$pageTitle='Nutricionistas';
$professionals=db_all($pdo,'SELECT n.*, u.id AS user_id, u.name, u.avatar_url FROM nutritionists n JOIN users u ON u.id=n.user_id WHERE u.status="active" ORDER BY n.featured DESC, u.name');
$user=current_user($pdo);
require __DIR__ . '/../partials/header.php';
?>
<main class="page-shell">
    <section class="page-hero">
        <div class="container page-hero-grid">
            <div>
                <div class="eyebrow">Contato profissional</div>
                <h1>Converse sem sair do Baby Food.</h1>
                <p>O responsável escolhe um nutricionista e inicia uma conversa no chat interno. O histórico fica dentro da própria plataforma.</p>
            </div>
            <div class="page-hero-note">
                <strong>Privacidade por perfil</strong>
                <span>Cada nutricionista vê apenas as conversas associadas ao seu próprio usuário.</span>
            </div>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <div class="professional-grid">
                <?php foreach($professionals as $pro): ?>
                    <article class="professional-card">
                        <div class="professional-top">
                            <div class="professional-avatar">
                                <?= e(mb_strtoupper(mb_substr($pro['name'],0,1))) ?>
                            </div>
                            <div>
                                <h3>
                                    <?= e($pro['name']) ?>
                                </h3>
                                <span>
                                    <?= e($pro['crn']) ?>
                                </span>
                            </div>
                        </div>
                        <p>
                            <?= e(excerpt($pro['bio']??'',130)) ?>
                        </p>
                        <div class="professional-meta">
                            <span>
                                <strong>Especialidade:</strong> <?= e($pro['specialty']) ?>
                                </span>
                                <?php if($pro['availability_note']): ?>
                                    <span>
                                        <strong>Atendimento:</strong> <?= e($pro['availability_note']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if($user && $user['role']==='parent'): ?>
                                    <form method="post" action="actions/start-chat.php">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="nutritionist_user_id" value="<?= (int)$pro['user_id'] ?>">
                                        <button class="btn btn-primary btn-block" type="submit">Iniciar conversa</button>
                                    </form>
                                <?php elseif(!$user): ?>
                                    <a class="btn btn-primary btn-block" href="index.php?page=login&return_to=<?= urlencode('index.php?page=nutricionistas') ?>">Entrar para conversar</a>
                                <?php else: ?>
                                    <span class="badge">Perfil profissional</span>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </main>
        <?php require __DIR__ . '/../partials/footer.php'; ?>
