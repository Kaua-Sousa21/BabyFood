<?php

declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$user = require_role($pdo, 'parent');
if (!is_post()) redirect('../index.php?page=nutricionistas');
verify_csrf();
$nutritionistUserId = (int) ($_POST['nutritionist_user_id'] ?? 0);
$valid = db_one($pdo, 'SELECT u.id FROM users u JOIN nutritionists n ON n.user_id = u.id WHERE u.id = ? AND u.role = "nutritionist" AND u.status = "active"', [$nutritionistUserId]);
if (!$valid) {
    flash('danger', 'Nutricionista não encontrado.');
    redirect('../index.php?page=nutricionistas');
}
$conversationId = db_value($pdo, 'SELECT id FROM conversations WHERE parent_user_id = ? AND nutritionist_user_id = ?', [(int) $user['id'], $nutritionistUserId]);
if (!$conversationId) {
    $pdo->prepare('INSERT INTO conversations (parent_user_id, nutritionist_user_id, last_message_at) VALUES (?, ?, NOW())')->execute([(int) $user['id'], $nutritionistUserId]);
    $conversationId = (int) $pdo->lastInsertId();
}
redirect('../index.php?page=chat&conversation=' . (int) $conversationId);
