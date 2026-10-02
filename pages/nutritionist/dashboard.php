<?php
$user = require_role($pdo, 'nutritionist');
$pageTitle = 'Painel do nutricionista';
$tab = $_GET['tab'] ?? 'overview';
$tabs = ['overview','conversations','recipes','videos','profile'];
if (!in_array($tab,$tabs,true)) $tab='overview';
$profile = db_one($pdo,'SELECT n.*,u.name,u.email,u.phone FROM nutritionists n JOIN users u ON u.id=n.user_id WHERE n.user_id=?',[(int)$user['id']]);
$ageRanges = db_all($pdo,'SELECT * FROM age_ranges ORDER BY sort_order');
$conversationCount=(int)db_value($pdo,'SELECT COUNT(*) FROM conversations WHERE nutritionist_user_id=?',[(int)$user['id']]);
$unreadCount=(int)db_value($pdo,'SELECT COUNT(*) FROM messages m JOIN conversations c ON c.id=m.conversation_id WHERE c.nutritionist_user_id=? AND m.sender_id<>? AND m.is_read=0',[(int)$user['id'],(int)$user['id']]);
$recipeCount=(int)db_value($pdo,'SELECT COUNT(*) FROM recipes WHERE created_by=?',[(int)$user['id']]);
$videoCount=(int)db_value($pdo,'SELECT COUNT(*) FROM videos WHERE created_by=?',[(int)$user['id']]);
$conversations=db_all($pdo,'SELECT c.*,u.name AS parent_name,(SELECT body FROM messages m WHERE m.conversation_id=c.id ORDER BY m.id DESC LIMIT 1) AS last_message FROM conversations c JOIN users u ON u.id=c.parent_user_id WHERE c.nutritionist_user_id=? ORDER BY COALESCE(c.last_message_at,c.created_at) DESC',[(int)$user['id']]);
$recipes=db_all($pdo,'SELECT r.*,a.name AS age_name FROM recipes r JOIN age_ranges a ON a.id=r.age_range_id WHERE r.created_by=? ORDER BY r.id DESC',[(int)$user['id']]);
$videos=db_all($pdo,'SELECT v.*,a.name AS age_name FROM videos v JOIN age_ranges a ON a.id=v.age_range_id WHERE v.created_by=? ORDER BY v.id DESC',[(int)$user['id']]);
require __DIR__ . '/../../partials/header.php';
?>
<main class="page-shell">
    <section class="section-sm">
        <div class="container dashboard-layout">
            <aside class="sidebar">
                <div class="sidebar-title">Nutricionista</div>
                <nav>
                    <a class="<?= $tab==='overview'?'active':'' ?>" href="index.php?page=nutri-dashboard">Visão geral</a>
                    <a class="<?= $tab==='conversations'?'active':'' ?>" href="index.php?page=nutri-dashboard&tab=conversations">Conversas<?= $unreadCount?' ('.$unreadCount.')':'' ?>
                    </a>
                    <a class="<?= $tab==='recipes'?'active':'' ?>" href="index.php?page=nutri-dashboard&tab=recipes">Receitas</a>
                    <a class="<?= $tab==='videos'?'active':'' ?>" href="index.php?page=nutri-dashboard&tab=videos">Vídeos</a>
                    <a class="<?= $tab==='profile'?'active':'' ?>" href="index.php?page=nutri-dashboard&tab=profile">Meu perfil</a>
                </nav>
            </aside>
            <section>
                <div class="dashboard-head">
                    <div>
                        <div class="eyebrow">Área profissional</div>
                        <h1>
                            <?= e($user['name']) ?>
                        </h1>
                        <p>
                            <?= e($profile['specialty']??'Nutricionista Baby Food') ?>
                        </p>
                    </div>
                    <a class="btn btn-primary btn-sm" href="index.php?page=chat">Abrir chat</a>
                </div>
                <?php if($tab==='overview'): ?>
                    <div class="stat-grid">
                        <div class="stat-card">
                            <small>Conversas</small>
                            <strong>
                                <?= $conversationCount ?>
                            </strong>
                        </div>
                        <div class="stat-card">
                            <small>Não lidas</small>
                            <strong>
                                <?= $unreadCount ?>
                            </strong>
                        </div>
                        <div class="stat-card">
                            <small>Receitas</small>
                            <strong>
                                <?= $recipeCount ?>
                            </strong>
                        </div>
                        <div class="stat-card">
                            <small>Vídeos</small>
                            <strong>
                                <?= $videoCount ?>
                            </strong>
                        </div>
                    </div>
                    <div class="panel">
                        <div class="panel-head">
                            <h2>Atendimentos recentes</h2>
                            <a class="text-link" href="index.php?page=chat">Ver chat →</a>
                        </div>
                        <div class="panel-body" style="display:grid;gap:10px">
                            <?php foreach(array_slice($conversations,0,5) as $c): ?>
                                <a class="child-card" href="index.php?page=chat&conversation=<?= (int)$c['id'] ?>">
                                    <span class="avatar-chip">
                                        <?= e(mb_strtoupper(mb_substr($c['parent_name'],0,1))) ?>
                                    </span>
                                    <div>
                                        <strong>
                                            <?= e($c['parent_name']) ?>
                                        </strong>
                                        <span>
                                            <?= e(excerpt($c['last_message']?:'Conversa iniciada',80)) ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                            <?php if(!$conversations): ?>
                                <p class="muted">Nenhum atendimento iniciado.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif($tab==='conversations'): ?>
                    <div class="panel">
                        <div class="panel-head">
                            <h2>Conversas</h2>
                            <a class="btn btn-primary btn-sm" href="index.php?page=chat">Abrir central de chat</a>
                        </div>
                        <div class="panel-body" style="display:grid;gap:10px">
                            <?php foreach($conversations as $c): ?>
                                <a class="child-card" href="index.php?page=chat&conversation=<?= (int)$c['id'] ?>">
                                    <span class="avatar-chip">
                                        <?= e(mb_strtoupper(mb_substr($c['parent_name'],0,1))) ?>
                                    </span>
                                    <div>
                                        <strong>
                                            <?= e($c['parent_name']) ?>
                                        </strong>
                                        <span>
                                            <?= e(excerpt($c['last_message']?:'Conversa iniciada',100)) ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php elseif($tab==='recipes'): ?>
                    <div class="panel">
                        <div class="panel-head">
                            <h2>Nova receita</h2>
                            <span class="badge">Publicação profissional</span>
                        </div>
                        <div class="panel-body">
                            <form class="form-grid" method="post" action="actions/nutritionist-save.php" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <input type="hidden" name="entity" value="recipe">
                                <label class="field">
                                    <span>Título</span>
                                    <input name="title" required>
                                </label>
                                <label class="field">
                                    <span>Faixa</span>
                                    <select name="age_range_id">
                                        <?php foreach($ageRanges as $age): ?>
                                            <option value="<?= (int)$age['id'] ?>">
                                                <?= e($age['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label class="field">
                                    <span>Tempo (min)</span>
                                    <input type="number" name="prep_minutes" min="0">
                                </label>
                                <label class="field">
                                    <span>Imagem (URL/caminho)</span>
                                    <input name="image_url">
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
                                    <textarea name="ingredients" required>
                                    </textarea>
                                </label>
                                <label class="field field--wide">
                                    <span>Passos (um por linha)</span>
                                    <textarea name="steps" required>
                                    </textarea>
                                </label>
                                <label class="field field--wide">
                                    <span>Observações nutricionais</span>
                                    <textarea name="nutrition_notes">
                                    </textarea>
                                </label>
                                <label class="field field--wide">
                                    <span>Alergênicos e cuidados</span>
                                    <textarea name="allergens">
                                    </textarea>
                                </label>
                                <button class="btn btn-primary field--wide" type="submit">Publicar receita</button>
                            </form>
                        </div>
                    </div>
                    <div class="panel">
                        <div class="panel-head">
                            <h2>Minhas receitas</h2>
                        </div>
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Faixa</th>
                                        <th>Status</th>
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
                                                <?= e($r['status']) ?>
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
                            <form class="form-grid" method="post" action="actions/nutritionist-save.php" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <input type="hidden" name="entity" value="video">
                                <label class="field">
                                    <span>Título</span>
                                    <input name="title" required>
                                </label>
                                <label class="field">
                                    <span>Faixa</span>
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
                                    <span>URL incorporada</span>
                                    <input name="video_url" placeholder="https://www.youtube.com/embed/..." required>
                                </label>
                                <label class="field">
                                    <span>Miniatura (URL/caminho)</span>
                                    <input name="thumbnail_url">
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
                                <button class="btn btn-primary field--wide" type="submit">Publicar vídeo</button>
                            </form>
                        </div>
                    </div>
                    <div class="panel">
                        <div class="panel-head">
                            <h2>Meus vídeos</h2>
                        </div>
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Faixa</th>
                                        <th>Duração</th>
                                        <th>Status</th>
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
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php elseif($tab==='profile'): ?>
                        <div class="panel">
                            <div class="panel-head">
                                <h2>Perfil profissional</h2>
                            </div>
                            <div class="panel-body">
                                <form class="form-grid" method="post" action="actions/nutritionist-save.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="entity" value="profile">
                                    <label class="field">
                                        <span>Nome</span>
                                        <input name="name" value="<?= e($user['name']) ?>" required>
                                    </label>
                                    <label class="field">
                                        <span>Telefone</span>
                                        <input name="phone" value="<?= e($profile['phone']??'') ?>">
                                    </label>
                                    <label class="field">
                                        <span>CRN</span>
                                        <input name="crn" value="<?= e($profile['crn']??'') ?>" required>
                                    </label>
                                    <label class="field">
                                        <span>Especialidade</span>
                                        <input name="specialty" value="<?= e($profile['specialty']??'') ?>" required>
                                    </label>
                                    <label class="field field--wide">
                                        <span>Biografia</span>
                                        <textarea name="bio">
                                            <?= e($profile['bio']??'') ?>
                                        </textarea>
                                    </label>
                                    <label class="field field--wide">
                                        <span>Disponibilidade</span>
                                        <input name="availability_note" value="<?= e($profile['availability_note']??'') ?>">
                                    </label>
                                    <button class="btn btn-primary field--wide" type="submit">Salvar perfil</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </section>
    </main>
    <?php require __DIR__ . '/../../partials/footer.php'; ?>
