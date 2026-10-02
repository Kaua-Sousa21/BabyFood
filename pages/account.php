<?php
$user = require_role($pdo, 'parent');
$pageTitle = 'Minha conta';
$tab = $_GET['tab'] ?? 'visao-geral';
$allowedTabs = ['visao-geral','criancas','pedidos','notificacoes'];
if (!in_array($tab, $allowedTabs, true)) $tab = 'visao-geral';
$children = db_all($pdo, 'SELECT * FROM children WHERE user_id = ? ORDER BY name', [(int)$user['id']]);
$orders = db_all($pdo, 'SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 30', [(int)$user['id']]);
$notifications = db_all($pdo, 'SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 30', [(int)$user['id']]);
$unread = (int) db_value($pdo, 'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0', [(int)$user['id']]);
require __DIR__ . '/../partials/header.php';
$statusLabels = [
    'received'=>'Recebido','preparing'=>'Em preparo','out_for_delivery'=>'Saiu para entrega','delivered'=>'Entregue','cancelled'=>'Cancelado',
    'pending'=>'Pendente','active'=>'Ativo','paused'=>'Pausado','expired'=>'Expirado'
];
?>
<main class="page-shell">
    <section class="section-sm">
        <div class="container account-layout">
            <aside class="sidebar">
                <div class="sidebar-title">Minha conta</div>
                <nav>
                    <a class="<?= $tab==='visao-geral'?'active':'' ?>" href="index.php?page=minha-conta">Visão geral</a>
                    <a class="<?= $tab==='criancas'?'active':'' ?>" href="index.php?page=minha-conta&tab=criancas">Minhas crianças</a>
                    <a class="<?= $tab==='pedidos'?'active':'' ?>" href="index.php?page=minha-conta&tab=pedidos">Pedidos</a>
                    <a class="<?= $tab==='notificacoes'?'active':'' ?>" href="index.php?page=minha-conta&tab=notificacoes">Notificações<?= $unread ? ' (' . $unread . ')' : '' ?>
                    </a>
                    <a href="index.php?page=chat">Conversas</a>
                </nav>
            </aside>
            <section>
                <div class="dashboard-head">
                    <div>
                        <div class="eyebrow">Área do responsável</div>
                        <h1>Olá, <?= e(explode(' ', $user['name'])[0]) ?>.</h1>
                        <p>Gerencie as crianças, pedidos e conversas da família.</p>
                    </div>
                </div>
                <?php if ($tab === 'visao-geral'): ?>
                    <div class="stat-grid">
                        <div class="stat-card">
                            <small>Crianças cadastradas</small>
                            <strong>
                                <?= count($children) ?>
                            </strong>
                        </div>
                        <div class="stat-card">
                            <small>Pedidos</small>
                            <strong>
                                <?= count($orders) ?>
                            </strong>
                        </div>
                        <div class="stat-card">
                            <small>Conversas</small>
                            <strong>
                                <?= (int) db_value($pdo, 'SELECT COUNT(*) FROM conversations WHERE parent_user_id = ?', [(int)$user['id']]) ?>
                            </strong>
                        </div>
                        <div class="stat-card">
                            <small>Notificações</small>
                            <strong>
                                <?= $unread ?>
                            </strong>
                        </div>
                    </div>
                    <div class="panel">
                        <div class="panel-head">
                            <h2>Perfis infantis</h2>
                            <a class="text-link" href="index.php?page=minha-conta&tab=criancas">Gerenciar →</a>
                        </div>
                        <div class="panel-body">
                            <?php if (!$children): ?>
                                <p class="muted">Nenhuma criança cadastrada.</p>
                            <?php else: ?>
                                <div class="child-card-grid">
                                    <?php foreach($children as $child): $months=age_in_months($child['birth_date']); $range=age_range_for_months($pdo,$months); ?>
                                    <div class="child-card">
                                        <div class="child-avatar">
                                            <?= e(mb_strtoupper(mb_substr($child['name'],0,1))) ?>
                                        </div>
                                        <div>
                                            <strong>
                                                <?= e($child['name']) ?>
                                            </strong>
                                            <span>
                                                <?= e(child_age_label($child['birth_date'])) ?>
                                                <?= $range ? ' · ' . e($range['name']) : ' · fora das faixas do projeto' ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-head">
                        <h2>Pedidos recentes</h2>
                        <a class="text-link" href="index.php?page=minha-conta&tab=pedidos">Ver todos →</a>
                    </div>
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach(array_slice($orders,0,5) as $order): ?>
                                    <tr>
                                        <td>#<?= (int)$order['id'] ?>
                                        </td>
                                        <td>
                                            <?= e(date('d/m/Y',strtotime($order['created_at']))) ?>
                                        </td>
                                        <td>
                                            <span class="status-pill">
                                                <?= e($statusLabels[$order['status']]??$order['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= money($order['total']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php elseif ($tab === 'criancas'): ?>
                <div class="panel">
                    <div class="panel-head">
                        <h2>Cadastrar criança</h2>
                    </div>
                    <div class="panel-body">
                        <form class="form-grid" method="post" action="actions/children.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="create">
                            <label class="field">
                                <span>Nome</span>
                                <input type="text" name="name" required>
                            </label>
                            <label class="field">
                                <span>Data de nascimento</span>
                                <input type="date" name="birth_date" required>
                            </label>
                            <label class="field field--wide">
                                <span>Observação interna (opcional)</span>
                                <textarea name="notes" placeholder="Evite registrar informações sensíveis que não sejam necessárias.">
                                </textarea>
                            </label>
                            <button class="btn btn-primary field--wide" type="submit">Adicionar perfil</button>
                        </form>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-head">
                        <h2>Perfis cadastrados</h2>
                    </div>
                    <div class="panel-body">
                        <?php if(!$children): ?>
                            <p class="muted">Cadastre a primeira criança.</p>
                        <?php else: ?>
                            <div class="child-card-grid">
                                <?php foreach($children as $child): $months=age_in_months($child['birth_date']);$range=age_range_for_months($pdo,$months); ?>
                                <div class="child-card">
                                    <div class="child-avatar">
                                        <?= e(mb_strtoupper(mb_substr($child['name'],0,1))) ?>
                                    </div>
                                    <div style="flex:1">
                                        <strong>
                                            <?= e($child['name']) ?>
                                        </strong>
                                        <span>
                                            <?= e(child_age_label($child['birth_date'])) ?>
                                            <?= $range?' · '.e($range['name']):'' ?>
                                        </span>
                                    </div>
                                    <form method="post" action="actions/children.php" onsubmit="return confirm('Remover este perfil?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="child_id" value="<?= (int)$child['id'] ?>">
                                        <button class="btn btn-ghost btn-sm" type="submit">Remover</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($tab === 'pedidos'): ?>
            <div class="panel">
                <div class="panel-head">
                    <h2>Meus pedidos</h2>
                    <a class="btn btn-primary btn-sm" href="index.php?page=marmitas">Nova compra</a>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Pagamento</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                                <tr>
                                    <td>#<?= (int)$order['id'] ?>
                                    </td>
                                    <td>
                                        <?= e(date('d/m/Y H:i',strtotime($order['created_at']))) ?>
                                    </td>
                                    <td>
                                        <span class="status-pill">
                                            <?= e($statusLabels[$order['status']]??$order['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= e(strtoupper($order['payment_method'])) ?> · <?= e($order['payment_status']) ?>
                                    </td>
                                    <td>
                                        <?= money($order['total']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
    <?php elseif ($tab === 'notificacoes'): ?>
        <?php $pdo->prepare('UPDATE notifications SET is_read=1 WHERE user_id=?')->execute([(int)$user['id']]); ?>
        <div class="panel">
            <div class="panel-head">
                <h2>Notificações</h2>
            </div>
            <div class="panel-body" style="display:grid;gap:10px">
                <?php if(!$notifications): ?>
                    <p class="muted">Nenhuma notificação.</p>
                <?php endif; ?>
                <?php foreach($notifications as $notification): ?>
                    <a href="<?= e($notification['link'] ?: '#') ?>" class="child-card">
                        <div class="child-avatar">!</div>
                        <div>
                            <strong>
                                <?= e($notification['title']) ?>
                            </strong>
                            <span>
                                <?= e($notification['body']) ?> · <?= e(date('d/m H:i',strtotime($notification['created_at']))) ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
</div>
</section>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
