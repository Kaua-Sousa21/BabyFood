<?php

declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
if (!is_post()) redirect('../index.php');
verify_csrf();
logout_user();
flash('success', 'Você saiu da sua conta.');
redirect('../index.php');
