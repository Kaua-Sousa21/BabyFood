<?php
$user = require_role($pdo, 'admin');
$pageTitle = 'Painel administrativo';
$tab = $_GET['tab'] ?? 'overview';
$tabs = ['overview','products','recipes','videos','articles','nutritionists','orders','users'];
if (!in_array($tab, $tabs, true)) $tab = 'overview';
$ageRanges = db_all($pdo,'SELECT * FROM age_ranges ORDER BY sort_order');
$stats = [
    'orders' => (int)db_value($pdo,'SELECT COUNT(*) FROM orders'),
    'customers' => (int)db_value($pdo,'SELECT COUNT(*) FROM users WHERE role="parent"'),
    'products' => (int)db_value($pdo,'SELECT COUNT(*) FROM products WHERE active=1'),
    'messages' => (int)db_value($pdo,'SELECT COUNT(*) FROM messages'),
];
$products = db_all($pdo,'SELECT p.*, a.name AS age_name FROM products p JOIN age_ranges a ON a.id=p.age_range_id ORDER BY p.id DESC');
$recipes = db_all($pdo,'SELECT r.*, a.name AS age_name FROM recipes r JOIN age_ranges a ON a.id=r.age_range_id ORDER BY r.id DESC');
$videos = db_all($pdo,'SELECT v.*, a.name AS age_name FROM videos v JOIN age_ranges a ON a.id=v.age_range_id ORDER BY v.id DESC');
$articles = db_all($pdo,'SELECT * FROM articles ORDER BY id DESC');
$nutritionists = db_all($pdo,'SELECT n.*, u.name, u.email FROM nutritionists n JOIN users u ON u.id=n.user_id ORDER BY u.name');
$orders = db_all($pdo,'SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.id DESC LIMIT 100');
$users = db_all($pdo,'SELECT id,name,email,role,status,created_at FROM users ORDER BY id DESC LIMIT 150');
$recentOrders = array_slice($orders,0,8);
require __DIR__ . '/../../partials/header.php';
$orderLabels=['received'=>'Recebido','preparing'=>'Em preparo','out_for_delivery'=>'Saiu p/ entrega','delivered'=>'Entregue','cancelled'=>'Cancelado'];
?>
<main class="page-shell">
    <section class="section-sm">
        <div class="container dashboard-layout">
            <aside class="sidebar">
                <div class="sidebar-title">Administração</div>
                <nav>
                    <?php foreach(['overview'=>'Visão geral','products'=>'Marmitas','recipes'=>'Receitas','videos'=>'Vídeos','articles'=>'Conteúdos','nutritionists'=>'Nutricionistas','orders'=>'Pedidos','users'=>'Usuários'] as $key=>$label): ?>
                        <a class="<?= $tab===$key?'active':'' ?>" href="index.php?page=admin&tab=<?= e($key) ?>">
                            <?= e($label) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </aside>
            <section>
                <div class="dashboard-head">
                    <div>
                        <div class="eyebrow">Baby Food Admin</div>
                        <h1>Painel administrativo</h1>
                        <p>Gerencie o catálogo, conteúdo, usuários e operação.</p>
                    </div>
                    <a class="btn btn-ghost btn-sm" href="index.php">Ver site</a>
                </div>
                <?php if($tab==='overview'): ?>
                    <div class="stat-grid">
                        <div class="stat-card">
                            <small>Pedidos</small>
                            <strong>
                                <?= $stats['orders'] ?>
                            </strong>
                        </div>
                        <div class="stat-card">
                            <small>Responsáveis</small>
                            <strong>
                                <?= $stats['customers'] ?>
                            </strong>
                        </div>
                        <div class="stat-card">
                            <small>Marmitas ativas</small>
                            <strong>
                                <?= $stats['products'] ?>
                            </strong>
                        </div>
                        <div class="stat-card">
                            <small>Mensagens</small>
                            <strong>
                                <?= $stats['messages'] ?>
                            </strong>
                        </div>
                    </div>
                    <div class="panel">
                        <div class="panel-head">
                            <h2>Pedidos recentes</h2>
                            <a class="text-link" href="index.php?page=admin&tab=orders">Gerenciar →</a>
                        </div>
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Pedido</th>
                                        <th>Cliente</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?= (int)$order['id'] ?>
                                            </td>
                                            <td>
                                                <?= e($order['customer_name']) ?>
                                            </td>
                                            <td>
                                                <span class="status-pill">
                                                    <?= e($orderLabels[$order['status']]??$order['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= money($order['total']) ?>
                                            </td>
                                            <td>
                                                <?= e(date('d/m/Y H:i',strtotime($order['created_at']))) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php elseif($tab==='products'): ?>
                    <div class="panel">
                        <div class="panel-head">
                            <h2>Nova marmita</h2>
                        </div>
                        <div class="panel-body">
                            <form class="form-grid" method="post" action="actions/admin-save.php" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <input type="hidden" name="entity" value="product">
                                <label class="field">
                                    <span>Nome</span>
                                    <input name="name" required>
                                </label>
                                <label class="field">
                                    <span>Faixa etária</span>
                                    <select name="age_range_id" required>
                                        <?php foreach($ageRanges as $age): ?>
                                            <option value="<?= (int)$age['id'] ?>">
                                                <?= e($age['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label class="field">
                                    <span>Preço</span>
                                    <input name="price" type="number" step="0.01" min="0" required>
                                </label>
                                <label class="field">
                                    <span>Peso (g)</span>
                                    <input name="weight_grams" type="number" min="0">
                                </label>
                                <label class="field">
                                    <span>Estoque</span>
                                    <input name="stock" type="number" min="0" value="0">
                                </label>
                                <label class="field">
                                    <span>Imagem (URL ou caminho)</span>
                                    <input name="image_url" placeholder="assets/img/... ou https://...">
                                </label>
                                <label class="field">
                                    <span>Ou enviar imagem</span>
                                    <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp">
                                </label>
                                <label class="field field--wide">
                                    <span>Descrição</span>
                                    <textarea name="description" required>
                                    </textarea>
                                </label>
                                <label class="field field--wide">
                                    <span>Ingredientes (um por linha)</span>
                                    <textarea name="ingredients">
                                    </textarea>
                                </label>
                                <label class="field field--wide">
                                    <span>Alergênicos</span>
                                    <textarea name="allergens">
                                    </textarea>
                                </label>
                                <label class="field field--wide">
                                    <span>Conservação</span>
                                    <textarea name="storage_info">
                                    </textarea>
                                </label>
                                <button class="btn btn-primary field--wide" type="submit">Cadastrar marmita</button>
                            </form>
                        </div>
                    </div>
                    <div class="panel">
                        <div class="panel-head">
                            <h2>Marmitas cadastradas</h2>
                        </div>
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Faixa</th>
                                        <th>Preço</th>
                                        <th>Estoque</th>
                                        <th>Status</th>
                                        <th>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($products as $p): ?>
                                        <tr>
                                            <td>
                                                <?= e($p['name']) ?>
                                            </td>
                                            <td>
                                                <?= e($p['age_name']) ?>
                                            </td>
                                            <td>
                                                <?= money($p['price']) ?>
                                            </td>
                                            <td>
                                                <?= (int)$p['stock'] ?>
                                            </td>
                                            <td>
                                                <?= $p['active']?'Ativo':'Inativo' ?>
                                            </td>
                                            <td>
                                                <form method="post" action="actions/admin-save.php">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="entity" value="toggle-product">
                                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                                    <button class="btn btn-ghost btn-sm" type="submit">
                                                        <?= $p['active']?'Desativar':'Ativar' ?>
                                                    </button>
                                                </form>
                                                <a class="text-link" style="margin-left:8px" href="index.php?page=admin-edit&type=product&id=<?= (int)$p['id'] ?>">Editar</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php elseif($tab==='recipes'): ?>
                    <div class="panel">
                        <div class="panel-head">
                            <h2>Nova receita</h2>
                        </div>
                        <div class="panel-body">
                            <?php include __DIR__ . '/recipe-form.php'; ?>
                        </div>
                    </div>
                    <div class="panel">
                        <div class="panel-head">
                            <h2>Receitas</h2>
                        </div>
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Faixa</th>
                                        <th>Tempo</th>
                                        <th>Status</th>
                                        <th>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recipes as $r): ?>
                                        <tr>
                                            <td>
                                                <?= e($r['title']) ?>
                                            </td>
                                            <td>
                                                <?= e($r['age_name']) ?>
                                            </td>
                                            <td>
                                                <?= (int)$r['prep_minutes'] ?> min</td>
                                                <td>
                                                    <?= e($r['status']) ?>
                                                </td>
                                                <td>
                                                    <a class="text-link" href="index.php?page=admin-edit&type=recipe&id=<?= (int)$r['id'] ?>">Editar</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php elseif($tab==='videos'): ?>
                        <div class="panel">
                            <div class="panel-head">
                                <h2>Novo vídeo</h2>
                            </div>
                            <div class="panel-body">
                                <form class="form-grid" method="post" action="actions/admin-save.php" enctype="multipart/form-data">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="entity" value="video">
                                    <label class="field">
                                        <span>Título</span>
                                        <input name="title" required>
                                    </label>
                                    <label class="field">
                                        <span>Faixa etária</span>
                                        <select name="age_range_id">
                                            <?php foreach($ageRanges as $age): ?>
                                                <option value="<?= (int)$age['id'] ?>">
                                                    <?= e($age['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <label class="field">
                                        <span>Duração (min)</span>
                                        <input type="number" name="duration_minutes" min="0">
                                    </label>
                                    <label class="field">
                                        <span>URL incorporada do vídeo</span>
                                        <input name="video_url" placeholder="https://www.youtube.com/embed/..." required>
                                    </label>
                                    <label class="field">
                                        <span>Miniatura (URL/caminho)</span>
                                        <input name="thumbnail_url" placeholder="assets/img/... ou URL">
                                    </label>
                                    <label class="field">
                                        <span>Ou enviar miniatura</span>
                                        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp">
                                    </label>
                                    <label class="field field--wide">
                                        <span>Descrição</span>
                                        <textarea name="description" required>
                                        </textarea>
                                    </label>
                                    <button class="btn btn-primary field--wide" type="submit">Cadastrar vídeo</button>
                                </form>
                            </div>
                        </div>
                        <div class="panel">
                            <div class="panel-head">
                                <h2>Vídeos</h2>
                            </div>
                            <div class="table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Faixa</th>
                                            <th>Duração</th>
                                            <th>Status</th>
                                            <th>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($videos as $v): ?>
                                            <tr>
                                                <td>
                                                    <?= e($v['title']) ?>
                                                </td>
                                                <td>
                                                    <?= e($v['age_name']) ?>
                                                </td>
                                                <td>
                                                    <?= (int)$v['duration_minutes'] ?> min</td>
                                                    <td>
                                                        <?= e($v['status']) ?>
                                                    </td>
                                                    <td>
                                                        <a class="text-link" href="index.php?page=admin-edit&type=video&id=<?= (int)$v['id'] ?>">Editar</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php elseif($tab==='articles'): ?>
                            <div class="panel">
                                <div class="panel-head">
                                    <h2>Novo conteúdo</h2>
                                </div>
                                <div class="panel-body">
                                    <form class="form-grid" method="post" action="actions/admin-save.php">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="entity" value="article">
                                        <label class="field">
                                            <span>Título</span>
                                            <input name="title" required>
                                        </label>
                                        <label class="field">
                                            <span>Categoria</span>
                                            <select name="category">
                                                <option value="nutrition">Nutrição</option>
                                                <option value="protein">Proteínas</option>
                                                <option value="vitamins">Vitaminas</option>
                                                <option value="food">Alimentos</option>
                                            </select>
                                        </label>
                                        <label class="field field--wide">
                                            <span>Resumo</span>
                                            <textarea name="excerpt" required>
                                            </textarea>
                                        </label>
                                        <label class="field field--wide">
                                            <span>Conteúdo</span>
                                            <textarea name="content" required>
                                            </textarea>
                                        </label>
                                        <button class="btn btn-primary field--wide" type="submit">Publicar conteúdo</button>
                                    </form>
                                </div>
                            </div>
                            <div class="panel">
                                <div class="panel-head">
                                    <h2>Conteúdos</h2>
                                </div>
                                <div class="table-wrap">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Título</th>
                                                <th>Categoria</th>
                                                <th>Status</th>
                                                <th>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($articles as $a): ?>
                                                <tr>
                                                    <td>
                                                        <?= e($a['title']) ?>
                                                    </td>
                                                    <td>
                                                        <?= e($a['category']) ?>
                                                    </td>
                                                    <td>
                                                        <?= e($a['status']) ?>
                                                    </td>
                                                    <td>
                                                        <a class="text-link" href="index.php?page=admin-edit&type=article&id=<?= (int)$a['id'] ?>">Editar</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php elseif($tab==='nutritionists'): ?>
                            <div class="panel">
                                <div class="panel-head">
                                    <h2>Novo nutricionista</h2>
                                </div>
                                <div class="panel-body">
                                    <form class="form-grid" method="post" action="actions/admin-save.php">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="entity" value="nutritionist">
                                        <label class="field">
                                            <span>Nome</span>
                                            <input name="name" required>
                                        </label>
                                        <label class="field">
                                            <span>E-mail</span>
                                            <input type="email" name="email" required>
                                        </label>
                                        <label class="field">
                                            <span>Senha inicial</span>
                                            <input type="password" name="password" minlength="8" required>
                                        </label>
                                        <label class="field">
                                            <span>CRN</span>
                                            <input name="crn" required>
                                        </label>
                                        <label class="field field--wide">
                                            <span>Especialidade</span>
                                            <input name="specialty" required>
                                        </label>
                                        <label class="field field--wide">
                                            <span>Biografia</span>
                                            <textarea name="bio">
                                            </textarea>
                                        </label>
                                        <label class="field field--wide">
                                            <span>Disponibilidade</span>
                                            <input name="availability_note">
                                        </label>
                                        <button class="btn btn-primary field--wide" type="submit">Cadastrar nutricionista</button>
                                    </form>
                                </div>
                            </div>
                            <div class="panel">
                                <div class="panel-head">
                                    <h2>Nutricionistas</h2>
                                </div>
                                <div class="table-wrap">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>E-mail</th>
                                                <th>CRN</th>
                                                <th>Especialidade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($nutritionists as $n): ?>
                                                <tr>
                                                    <td>
                                                        <?= e($n['name']) ?>
                                                    </td>
                                                    <td>
                                                        <?= e($n['email']) ?>
                                                    </td>
                                                    <td>
                                                        <?= e($n['crn']) ?>
                                                    </td>
                                                    <td>
                                                        <?= e($n['specialty']) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php elseif($tab==='orders'): ?>
                            <div class="panel">
                                <div class="panel-head">
                                    <h2>Pedidos</h2>
                                </div>
                                <div class="table-wrap">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Pedido</th>
                                                <th>Cliente</th>
                                                <th>Total</th>
                                                <th>Pagamento</th>
                                                <th>Status</th>
                                                <th>Atualizar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($orders as $o): ?>
                                                <tr>
                                                    <td>#<?= (int)$o['id'] ?>
                                                    </td>
                                                    <td>
                                                        <?= e($o['customer_name']) ?>
                                                    </td>
                                                    <td>
                                                        <?= money($o['total']) ?>
                                                    </td>
                                                    <td>
                                                        <?= e(strtoupper($o['payment_method'])) ?>
                                                    </td>
                                                    <td>
                                                        <?= e($orderLabels[$o['status']]??$o['status']) ?>
                                                    </td>
                                                    <td>
                                                        <form method="post" action="actions/admin-save.php" style="display:flex;gap:6px">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="entity" value="order-status">
                                                            <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                                                            <select name="status" style="min-height:36px;border:1px solid var(--line);border-radius:9px">
                                                                <option value="received">Recebido</option>
                                                                <option value="preparing">Em preparo</option>
                                                                <option value="out_for_delivery">Saiu p/ entrega</option>
                                                                <option value="delivered">Entregue</option>
                                                                <option value="cancelled">Cancelado</option>
                                                            </select>
                                                            <button class="btn btn-primary btn-sm" type="submit">Salvar</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php elseif($tab==='users'): ?>
                            <div class="panel">
                                <div class="panel-head">
                                    <h2>Usuários</h2>
                                </div>
                                <div class="table-wrap">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>E-mail</th>
                                                <th>Perfil</th>
                                                <th>Status</th>
                                                <th>Cadastro</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($users as $u): ?>
                                                <tr>
                                                    <td>
                                                        <?= e($u['name']) ?>
                                                    </td>
                                                    <td>
                                                        <?= e($u['email']) ?>
                                                    </td>
                                                    <td>
                                                        <?= e($u['role']) ?>
                                                    </td>
                                                    <td>
                                                        <?= e($u['status']) ?>
                                                    </td>
                                                    <td>
                                                        <?= e(date('d/m/Y',strtotime($u['created_at']))) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            </section>
        </main>
        <?php require __DIR__ . '/../../partials/footer.php'; ?>
