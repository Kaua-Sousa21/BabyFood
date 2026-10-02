<?php
$pageTitle = 'Baby Food';
$ageRanges = db_all($pdo, 'SELECT * FROM age_ranges ORDER BY sort_order');
$featuredProducts = db_all($pdo, 'SELECT p.*, a.name AS age_name FROM products p JOIN age_ranges a ON a.id = p.age_range_id WHERE p.active = 1 ORDER BY p.id DESC LIMIT 3');
$featuredRecipes = db_all($pdo, 'SELECT r.*, a.name AS age_name FROM recipes r JOIN age_ranges a ON a.id = r.age_range_id WHERE r.status = "published" ORDER BY r.id DESC LIMIT 3');
$featuredNutri = db_one($pdo, 'SELECT n.*, u.name, u.avatar_url FROM nutritionists n JOIN users u ON u.id = n.user_id WHERE u.status = "active" ORDER BY n.featured DESC, n.id LIMIT 1');
require __DIR__ . '/../partials/header.php';
?>
<main class="page-shell">
    <section class="hero hero--dark">
        <div class="container hero-grid">
            <div class="hero-copy">
                <div class="eyebrow">6 meses a 3 anos</div>
                <h1>Comida de verdade para <em>cada nova fase.</em></h1>
                <p>Marmitas infantis, receitas, vídeos e contato direto com nutricionistas em uma única experiência.</p>
                <div class="hero-actions">
                    <a class="btn btn-light" href="index.php?page=marmitas">Explorar marmitas <span>→</span></a>
                    <a class="btn btn-ghost" style="border-color:rgba(255,255,255,.25);color:#fff" href="index.php?page=nutricionistas">Falar com nutricionista</a>
                </div>
                <div class="hero-meta">
                    <span><i>✓</i> Conteúdo separado por faixa etária</span>
                    <span><i>✓</i> Chat interno com profissionais</span>
                    <span><i>✓</i> Entrega com endereço identificado pelo CEP</span>
                </div>
            </div>

            <div class="hero-visual" aria-hidden="true">
                <div class="hero-mini-card">
                    <div class="avatar-row"><span>6m</span><span>8m</span><span>1+</span></div>
                    <strong>Uma plataforma que acompanha as fases.</strong>
                    <p>Conteúdo, produtos e vídeos filtrados pela idade.</p>
                </div>
                <div class="hero-card-main">
                    <img src="assets/img/hero-food.svg" alt="">
                    <div class="hero-card-overlay">
                        <div><strong>Cardápios com contexto</strong><small>Informação clara para responsáveis.</small></div>
                        <span class="badge">Baby Food</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="age-strip">
        <div class="container age-strip-inner">
            <div class="age-strip-label"><strong>Encontre pela fase da criança</strong></div>
            <?php foreach ($ageRanges as $range): ?>
                <a class="age-strip-link" href="index.php?page=marmitas&idade=<?= e($range['id']) ?>">
                    <small>Faixa etária</small>
                    <strong><?= e($range['name']) ?></strong>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-head">
                <div><div class="eyebrow">Marmitas</div><h2>Praticidade sem perder o cuidado.</h2></div>
                <p>Produtos organizados por fase, com ingredientes, peso, conservação e informações que podem ser revisadas pela equipe responsável.</p>
            </div>
            <div class="product-grid">
                <?php foreach ($featuredProducts as $product): ?>
                    <article class="product-card">
                        <a class="card-media" href="index.php?page=marmita&id=<?= (int) $product['id'] ?>">
                            <img src="<?= e(product_image($product['image_url'])) ?>" alt="<?= e($product['name']) ?>">
                            <span class="badge card-media-badge"><?= e($product['age_name']) ?></span>
                        </a>
                        <div class="card-body">
                            <div class="card-kicker"><span><?= (int) $product['weight_grams'] ?>g</span><span>Estoque <?= (int) $product['stock'] ?></span></div>
                            <h3 class="card-title"><a href="index.php?page=marmita&id=<?= (int) $product['id'] ?>"><?= e($product['name']) ?></a></h3>
                            <p><?= e(excerpt($product['description'], 105)) ?></p>
                            <div class="card-footer">
                                <div class="price"><strong><?= money($product['price']) ?></strong><small>por unidade</small></div>
                                <form method="post" action="actions/cart.php">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                    <input type="hidden" name="return_to" value="index.php">
                                    <button class="btn btn-primary btn-sm" type="submit">Adicionar</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div style="margin-top:28px"><a class="text-link" href="index.php?page=marmitas">Ver todas as marmitas →</a></div>
        </div>
    </section>

    <section class="section section-muted">
        <div class="container split-feature">
            <article class="feature-panel feature-panel--green">
                <div class="eyebrow">Entrega simples</div>
                <h3>Escolha suas marmitas e compre de forma avulsa.</h3>
                <p>Compre somente quando precisar. No checkout, o CEP identifica automaticamente a cidade, o estado e, quando disponível, o logradouro.</p>
                <a class="btn btn-light" href="index.php?page=marmitas">Escolher marmitas</a>
                <span class="feature-number">01</span>
            </article>
            <article class="feature-panel feature-panel--peach">
                <div class="eyebrow">Receitas em vídeo</div>
                <h3>Assistir, entender e preparar.</h3>
                <p>Uma biblioteca de vídeos organizada por idade para que os responsáveis possam acompanhar preparos e conteúdos educativos.</p>
                <a class="btn btn-primary" href="index.php?page=videos">Explorar vídeos</a>
                <span class="feature-number">02</span>
            </article>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-head">
                <div><div class="eyebrow">Como funciona</div><h2>Da escolha ao acompanhamento.</h2></div>
            </div>
            <div class="steps-grid">
                <div class="step-item"><div class="step-index">01 / PERFIL</div><h3>Cadastre a criança</h3><p>Informe a data de nascimento e deixe o sistema identificar a fase correspondente.</p></div>
                <div class="step-item"><div class="step-index">02 / ESCOLHA</div><h3>Monte a rotina</h3><p>Explore marmitas, receitas e vídeos da faixa etária.</p></div>
                <div class="step-item"><div class="step-index">03 / COMPRA</div><h3>Finalize o pedido</h3><p>Acompanhe o status das refeições pela sua conta.</p></div>
                <div class="step-item"><div class="step-index">04 / SUPORTE</div><h3>Use o chat</h3><p>Converse diretamente com os nutricionistas cadastrados na plataforma.</p></div>
            </div>
        </div>
    </section>

    <?php if ($featuredNutri): ?>
    <section class="section section-dark">
        <div class="container section-head">
            <div><div class="eyebrow">Acompanhamento</div><h2>O profissional continua perto da família.</h2></div>
            <div>
                <p><?= e(excerpt($featuredNutri['bio'] ?? '', 150)) ?></p>
                <a class="btn btn-light" style="margin-top:18px" href="index.php?page=nutricionistas">Conhecer nutricionistas</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="section">
        <div class="container">
            <div class="section-head">
                <div><div class="eyebrow">Receitas</div><h2>Ideias para cada fase da alimentação.</h2></div>
                <a class="text-link" href="index.php?page=receitas">Ver biblioteca →</a>
            </div>
            <div class="recipe-grid">
                <?php foreach ($featuredRecipes as $recipe): ?>
                    <article class="recipe-card">
                        <a class="card-media" href="index.php?page=receita&id=<?= (int) $recipe['id'] ?>">
                            <img src="<?= e(product_image($recipe['image_url'])) ?>" alt="<?= e($recipe['title']) ?>">
                            <span class="badge card-media-badge"><?= e($recipe['age_name']) ?></span>
                        </a>
                        <div class="card-body">
                            <div class="card-kicker"><span><?= (int) $recipe['prep_minutes'] ?> min</span><span>Receita</span></div>
                            <h3 class="card-title"><a href="index.php?page=receita&id=<?= (int) $recipe['id'] ?>"><?= e($recipe['title']) ?></a></h3>
                            <p><?= e(excerpt($recipe['description'], 110)) ?></p>
                            <a class="text-link" href="index.php?page=receita&id=<?= (int) $recipe['id'] ?>">Abrir receita →</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../partials/footer.php'; ?>
