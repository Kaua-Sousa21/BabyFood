<?php
$pageTitle='Nutrição e alimentação';
$category=$_GET['categoria']??'';
$valid=['nutrition','protein','vitamins','food'];
$sql='SELECT a.*, ar.name AS age_name FROM articles a LEFT JOIN age_ranges ar ON ar.id=a.age_range_id WHERE a.status="published"';$params=[];
if(in_array($category,$valid,true)){$sql.=' AND a.category=?';$params[]=$category;}$sql.=' ORDER BY a.id DESC';
$articles=db_all($pdo,$sql,$params);
$labels=['nutrition'=>'Nutrição','protein'=>'Proteínas','vitamins'=>'Vitaminas','food'=>'Alimentos'];
require __DIR__ . '/../partials/header.php';
?>
<main class="page-shell">
    <section class="page-hero">
        <div class="container page-hero-grid">
            <div>
                <div class="eyebrow">Baby Food Aprende</div>
                <h1>Informação simples, revisada e fácil de consultar.</h1>
                <p>Uma biblioteca para dicas de alimentação, fontes de proteína, vitaminas, higiene, conservação e outros conteúdos para os responsáveis.</p>
            </div>
            <div class="page-hero-note">
                <strong>Sem prescrição automática</strong>
                <span>O sistema informa e organiza conteúdos; orientações individualizadas devem acontecer com profissional habilitado.</span>
            </div>
        </div>
    </section>
    <section class="section-sm">
        <div class="container">
            <div class="filter-bar">
                <a class="filter-chip <?= $category===''?'active':'' ?>" href="index.php?page=nutricao">Todos</a>
                <?php foreach($labels as $key=>$label): ?>
                    <a class="filter-chip <?= $category===$key?'active':'' ?>" href="index.php?page=nutricao&categoria=<?= e($key) ?>">
                        <?= e($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="article-grid">
                <?php foreach($articles as $article): ?>
                    <article class="article-card">
                        <div class="card-body">
                            <div class="card-kicker">
                                <span>
                                    <?= e($labels[$article['category']]??$article['category']) ?>
                                </span>
                                <?php if($article['age_name']): ?>
                                    <span>
                                        <?= e($article['age_name']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h3 class="card-title">
                                <?= e($article['title']) ?>
                            </h3>
                            <p>
                                <?= e($article['excerpt']) ?>
                            </p>
                            <div class="info-block">
                                <p class="muted" style="margin-bottom:0">
                                    <?= e($article['content']) ?>
                                </p>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
