<?php
$pageTitle = 'Receitas em vídeo';
$ageRanges = db_all($pdo, 'SELECT * FROM age_ranges ORDER BY sort_order');
$selectedAge = isset($_GET['idade']) ? (int)$_GET['idade'] : 0;
$sql = 'SELECT v.*, a.name AS age_name FROM videos v JOIN age_ranges a ON a.id = v.age_range_id WHERE v.status = "published"'; $params=[];
if($selectedAge){$sql.=' AND v.age_range_id = ?';$params[]=$selectedAge;} $sql.=' ORDER BY a.sort_order, v.id DESC';
$videos = db_all($pdo,$sql,$params);
require __DIR__ . '/../partials/header.php';
?>
<main class="page-shell">
    <section class="page-hero">
        <div class="container page-hero-grid">
            <div>
                <div class="eyebrow">Baby Food Play</div>
                <h1>Receitas para assistir no tempo da família.</h1>
                <p>Vídeos de preparo e conteúdos educativos organizados pelas mesmas faixas etárias das receitas e marmitas.</p>
            </div>
            <div class="page-hero-note">
                <strong>Hospedagem de vídeo</strong>
                <span>O projeto aceita links incorporados, como YouTube não listado, evitando sobrecarregar a hospedagem.</span>
            </div>
        </div>
    </section>
    <section class="section-sm">
        <div class="container">
            <div class="filter-bar">
                <a class="filter-chip <?= !$selectedAge?'active':'' ?>" href="index.php?page=videos">Todos</a>
                <?php foreach($ageRanges as $range): ?>
                    <a class="filter-chip <?= $selectedAge===(int)$range['id']?'active':'' ?>" href="index.php?page=videos&idade=<?= (int)$range['id'] ?>">
                        <?= e($range['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="video-grid">
                <?php foreach($videos as $video): ?>
                    <article class="video-card">
                        <a class="card-media" href="index.php?page=video&id=<?= (int)$video['id'] ?>">
                            <img src="<?= e(product_image($video['thumbnail_url'])) ?>" alt="<?= e($video['title']) ?>">
                            <span class="badge card-media-badge">
                                <?= e($video['age_name']) ?>
                            </span>
                            <span class="video-play">▶</span>
                        </a>
                        <div class="card-body">
                            <div class="card-kicker">
                                <span>
                                    <?= (int)$video['duration_minutes'] ?> min</span>
                                    <span>Vídeo</span>
                                </div>
                                <h3 class="card-title">
                                    <?= e($video['title']) ?>
                                </h3>
                                <p>
                                    <?= e(excerpt($video['description'],110)) ?>
                                </p>
                                <a class="text-link" href="index.php?page=video&id=<?= (int)$video['id'] ?>">Assistir agora →</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>
    <?php require __DIR__ . '/../partials/footer.php'; ?>
