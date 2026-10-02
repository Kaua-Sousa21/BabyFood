<?php
$user = require_role($pdo, ['parent', 'nutritionist']);
$pageTitle = 'Chat com nutricionista';

if ($user['role'] === 'parent') {
    $conversations = db_all($pdo,
        'SELECT c.*, u.name AS other_name, n.specialty,
                (SELECT body FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_message
         FROM conversations c
         JOIN users u ON u.id = c.nutritionist_user_id
         LEFT JOIN nutritionists n ON n.user_id = u.id
         WHERE c.parent_user_id = ?
         ORDER BY COALESCE(c.last_message_at, c.created_at) DESC',
        [(int) $user['id']]
    );
} else {
    $conversations = db_all($pdo,
        'SELECT c.*, u.name AS other_name,
                (SELECT body FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_message
         FROM conversations c
         JOIN users u ON u.id = c.parent_user_id
         WHERE c.nutritionist_user_id = ?
         ORDER BY COALESCE(c.last_message_at, c.created_at) DESC',
        [(int) $user['id']]
    );
}

$conversationId = (int) ($_GET['conversation'] ?? ($conversations[0]['id'] ?? 0));
$activeConversation = null;
$messages = [];

if ($conversationId > 0) {
    $activeConversation = db_one($pdo,
        'SELECT c.*, p.name AS parent_name, nuser.name AS nutritionist_name, n.specialty
         FROM conversations c
         JOIN users p ON p.id = c.parent_user_id
         JOIN users nuser ON nuser.id = c.nutritionist_user_id
         LEFT JOIN nutritionists n ON n.user_id = nuser.id
         WHERE c.id = ?',
        [$conversationId]
    );

    $allowed = $activeConversation && (
        ($user['role'] === 'parent' && (int) $activeConversation['parent_user_id'] === (int) $user['id']) ||
        ($user['role'] === 'nutritionist' && (int) $activeConversation['nutritionist_user_id'] === (int) $user['id'])
    );

    if (!$allowed) {
        $activeConversation = null;
    } else {
        $messages = db_all($pdo,
            'SELECT m.*, u.name AS sender_name FROM messages m JOIN users u ON u.id = m.sender_id WHERE m.conversation_id = ? ORDER BY m.id ASC',
            [$conversationId]
        );
        $pdo->prepare('UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id <> ?')->execute([$conversationId, (int) $user['id']]);
    }
}

require __DIR__ . '/../partials/header.php';
?>
<main class="page-shell">
    <section class="page-hero">
        <div class="container page-hero-grid">
            <div>
                <div class="eyebrow">Chat interno</div>
                <h1><?= $user['role'] === 'nutritionist' ? 'Atendimentos em um só lugar.' : 'Converse com um nutricionista.' ?></h1>
                <p><?= $user['role'] === 'nutritionist' ? 'Responda responsáveis e mantenha o histórico dentro do Baby Food.' : 'Escolha um profissional e continue a conversa sem depender de aplicativos externos.' ?></p>
            </div>
            <div class="page-hero-note"><strong>Uso responsável</strong><span>Evite compartilhar dados além do necessário. Informações sensíveis devem ter acesso restrito e política de privacidade adequada.</span></div>
        </div>
    </section>

    <section class="section-sm">
        <div class="container">
            <div class="chat-shell <?= $activeConversation ? 'has-active' : '' ?>">
                <aside class="chat-list">
                    <div class="chat-list-head">
                        <h2>Conversas</h2>
                        <?php if ($user['role'] === 'parent'): ?><a class="text-link" href="index.php?page=nutricionistas">+ Novo atendimento</a><?php endif; ?>
                    </div>
                    <?php if (!$conversations): ?>
                        <div style="padding:20px" class="muted">Nenhuma conversa iniciada.</div>
                    <?php endif; ?>
                    <?php foreach ($conversations as $conversation): ?>
                        <a class="chat-conversation-link <?= $conversationId === (int) $conversation['id'] ? 'active' : '' ?>" href="index.php?page=chat&conversation=<?= (int) $conversation['id'] ?>">
                            <span class="avatar-chip"><?= e(mb_strtoupper(mb_substr($conversation['other_name'], 0, 1))) ?></span>
                            <span class="chat-conversation-copy"><strong><?= e($conversation['other_name']) ?></strong><span><?= e(excerpt($conversation['last_message'] ?: 'Conversa iniciada', 48)) ?></span></span>
                        </a>
                    <?php endforeach; ?>
                </aside>

                <?php if ($activeConversation): ?>
                    <?php $otherName = $user['role'] === 'parent' ? $activeConversation['nutritionist_name'] : $activeConversation['parent_name']; ?>
                    <section class="chat-main">
                        <div class="chat-head">
                            <div class="chat-head-user"><span class="avatar-chip"><?= e(mb_strtoupper(mb_substr($otherName, 0, 1))) ?></span><div><strong><?= e($otherName) ?></strong><span><?= $user['role'] === 'parent' ? e($activeConversation['specialty'] ?: 'Nutricionista') : 'Responsável' ?></span></div></div>
                        </div>
                        <div class="chat-messages" data-chat-messages data-chat-conversation="<?= (int) $activeConversation['id'] ?>" data-current-user="<?= (int) $user['id'] ?>">
                            <?php foreach ($messages as $message): ?>
                                <article class="message <?= (int) $message['sender_id'] === (int) $user['id'] ? 'mine' : '' ?>" data-message-id="<?= (int) $message['id'] ?>">
                                    <p><?= nl2br(e($message['body'])) ?></p>
                                    <time><?= e(date('d/m H:i', strtotime($message['created_at']))) ?></time>
                                </article>
                            <?php endforeach; ?>
                        </div>
                        <form class="chat-compose" method="post" action="actions/send-message.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="conversation_id" value="<?= (int) $activeConversation['id'] ?>">
                            <textarea name="body" placeholder="Digite sua mensagem..." required></textarea>
                            <button class="btn btn-primary" type="submit">Enviar</button>
                        </form>
                    </section>
                <?php else: ?>
                    <div class="chat-empty"><div><div class="eyebrow">Chat Baby Food</div><h2>Selecione uma conversa.</h2><p>O histórico do atendimento aparecerá aqui.</p></div></div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
