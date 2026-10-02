<?php

declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$user = require_role($pdo, ['parent', 'nutritionist']);
if (!is_post()) redirect('../index.php?page=chat');
verify_csrf();
$conversationId = (int) ($_POST['conversation_id'] ?? 0);
$body = trim((string) ($_POST['body'] ?? ''));
if ($body === '') redirect('../index.php?page=chat&conversation=' . $conversationId);
if (mb_strlen($body) > 4000) $body = mb_substr($body, 0, 4000);

$conversation = db_one($pdo, 'SELECT * FROM conversations WHERE id = ?', [$conversationId]);
$allowed = $conversation && (
    ($user['role'] === 'parent' && (int) $conversation['parent_user_id'] === (int) $user['id']) ||
    ($user['role'] === 'nutritionist' && (int) $conversation['nutritionist_user_id'] === (int) $user['id'])
);
if (!$allowed) {
    http_response_code(403);
    exit('Conversa não autorizada.');
}
$pdo->prepare('INSERT INTO messages (conversation_id, sender_id, body) VALUES (?, ?, ?)')->execute([$conversationId, (int) $user['id'], $body]);
$pdo->prepare('UPDATE conversations SET last_message_at = NOW(), status = "open" WHERE id = ?')->execute([$conversationId]);
$recipientId = $user['role'] === 'parent' ? (int) $conversation['nutritionist_user_id'] : (int) $conversation['parent_user_id'];
$pdo->prepare('INSERT INTO notifications (user_id, title, body, link) VALUES (?, ?, ?, ?)')->execute([$recipientId, 'Nova mensagem', $user['name'] . ' enviou uma mensagem no chat.', 'index.php?page=chat&conversation=' . $conversationId]);
redirect('../index.php?page=chat&conversation=' . $conversationId);
