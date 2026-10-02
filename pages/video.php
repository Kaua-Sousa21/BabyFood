<?php
$id=(int)($_GET['id']??0);
$video=db_one($pdo,'SELECT v.*, a.name AS age_name, r.title AS recipe_title FROM videos v JOIN age_ranges a ON a.id=v.age_range_id LEFT JOIN recipes r ON r.id=v.related_recipe_id WHERE v.id=? AND v.status="published"',[$id]);
if(!$video){redirect('index.php?page=videos');}
$pageTitle=$video['title'];
require __DIR__ . '/../partials/header.php';
?>
<main class="page-shell">
    <section class="section">
        <div class="container">
            <div class="eyebrow">Receita em vídeo</div>
            <h1 style="max-width:900px;font-size:clamp(2.4rem,5vw,4.8rem);margin-bottom:18px">
                <?= e($video['title']) ?>
            </h1>
            <div class="detail-meta">
                <span class="badge">
                    <?= e($video['age_name']) ?>
                </span>
                <span class="badge badge--warm">
                    <?= (int)$video['duration_minutes'] ?> min</span>
                </div>
                <?php if ($video['video_url'] === 'demo'): ?>
                    <div class="video-frame" style="display:grid;place-items:center;color:#fff;text-align:center;padding:30px">
                        <div>
                            <div class="eyebrow" style="justify-content:center">Vídeo demonstrativo</div>
                            <h2 style="font-size:2rem">Adicione o vídeo real pelo painel.</h2>
                            <p style="color:rgba(255,255,255,.65)">Use uma URL incorporável, por exemplo do YouTube não listado.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="video-frame">
                        <iframe src="<?= e($video['video_url']) ?>" title="<?= e($video['title']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
                        </iframe>
                    </div>
                <?php endif; ?>
                <div style="max-width:760px;margin-top:28px">
                    <p class="muted">
                        <?= e($video['description']) ?>
                    </p>
                    <?php if($video['related_recipe_id']): ?>
                        <a class="btn btn-primary" href="index.php?page=receita&id=<?= (int)$video['related_recipe_id'] ?>">Abrir receita completa</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
    <?php require __DIR__ . '/../partials/footer.php'; ?>
