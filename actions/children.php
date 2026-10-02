<?php

declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';
$user = require_role($pdo, 'parent');
if (!is_post()) redirect('../index.php?page=minha-conta&tab=criancas');
verify_csrf();
$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $name = trim((string)($_POST['name'] ?? ''));
    $birthDate = (string)($_POST['birth_date'] ?? '');
    $notes = trim((string)($_POST['notes'] ?? ''));
    try { $birth = new DateTimeImmutable($birthDate); } catch (Throwable $e) { $birth = null; }
    if ($name === '' || !$birth || $birth > new DateTimeImmutable('today')) {
        flash('danger','Informe um nome e uma data de nascimento válida.');
        redirect('../index.php?page=minha-conta&tab=criancas');
    }
    $pdo->prepare('INSERT INTO children (user_id, name, birth_date, notes) VALUES (?, ?, ?, ?)')->execute([(int)$user['id'],$name,$birthDate,$notes?:null]);
    flash('success','Perfil da criança cadastrado.');
    redirect('../index.php?page=minha-conta&tab=criancas');
}

if ($action === 'delete') {
    $childId=(int)($_POST['child_id']??0);
    $pdo->prepare('DELETE FROM children WHERE id=? AND user_id=?')->execute([$childId,(int)$user['id']]);
    flash('success','Perfil removido.');
    redirect('../index.php?page=minha-conta&tab=criancas');
}

redirect('../index.php?page=minha-conta');
