<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$user = current_user($pdo);
if (!$user || !in_array($user['role'], ['parent', 'nutritionist'], true)) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'messages' => []]);
    exit;
}

$conversationId = (int) ($_GET['conversation_id'] ?? 0);
$after = max(0, (int) ($_GET['after'] ?? 0));
$conversation = db_one($pdo, 'SELECT * FROM conversations WHERE id = ?', [$conversationId]);

$allowed = $conversation && (
    ($user['role'] === 'parent' && (int) $conversation['parent_user_id'] === (int) $user['id']) ||
    ($user['role'] === 'nutritionist' && (int) $conversation['nutritionist_user_id'] === (int) $user['id'])
);

if (!$allowed) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'messages' => []]);
    exit;
}

$messages = db_all(
    $pdo,
    'SELECT id, sender_id, body, created_at FROM messages WHERE conversation_id = ? AND id > ? ORDER BY id ASC LIMIT 100',
    [$conversationId, $after]
);

$pdo->prepare('UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id <> ?')->execute([$conversationId, (int) $user['id']]);

echo json_encode([
    'ok' => true,
    'messages' => $messages,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
