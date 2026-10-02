<?php

declare(strict_types=1);

function seed_database(PDO $pdo): array
{
    $ageRanges = [
        ['6 a 8 meses', '6-8-meses', 6, 8, 1],
        ['8 a 10 meses', '8-10-meses', 8, 10, 2],
        ['10 a 12 meses', '10-12-meses', 10, 12, 3],
        ['1 a 3 anos', '1-3-anos', 12, 36, 4],
    ];

    $stmt = $pdo->prepare('INSERT IGNORE INTO age_ranges (name, slug, min_months, max_months, sort_order) VALUES (?, ?, ?, ?, ?)');
    foreach ($ageRanges as $range) {
        $stmt->execute($range);
    }

    $adminEmail = 'admin@babyfood.local';
    $nutritionistEmail = 'nutri@babyfood.local';
    $parentEmail = 'familia@babyfood.local';
    // Senha temporária gerada em cada instalação. Não mantenha credenciais fixas no repositório.
    $demoPassword = 'BF-' . bin2hex(random_bytes(6));

    $insertUser = $pdo->prepare(
        'INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE name = VALUES(name), role = VALUES(role), phone = VALUES(phone)'
    );

    $insertUser->execute(['Administrador Baby Food', $adminEmail, password_hash($demoPassword, PASSWORD_DEFAULT), 'admin', '(88) 99999-0001']);
    $insertUser->execute(['Dra. Marina Alves', $nutritionistEmail, password_hash($demoPassword, PASSWORD_DEFAULT), 'nutritionist', '(88) 99999-0002']);
    $insertUser->execute(['Família Demonstração', $parentEmail, password_hash($demoPassword, PASSWORD_DEFAULT), 'parent', '(88) 99999-0003']);

    $adminId = (int) $pdo->query("SELECT id FROM users WHERE email = 'admin@babyfood.local'")->fetchColumn();
    $nutritionistId = (int) $pdo->query("SELECT id FROM users WHERE email = 'nutri@babyfood.local'")->fetchColumn();
    $parentId = (int) $pdo->query("SELECT id FROM users WHERE email = 'familia@babyfood.local'")->fetchColumn();

    $pdo->prepare(
        'INSERT INTO nutritionists (user_id, crn, specialty, bio, availability_note, featured)
         VALUES (?, ?, ?, ?, ?, 1)
         ON DUPLICATE KEY UPDATE crn = VALUES(crn), specialty = VALUES(specialty), bio = VALUES(bio), availability_note = VALUES(availability_note), featured = 1'
    )->execute([
        $nutritionistId,
        'CRN 00/00000',
        'Nutrição materno-infantil e introdução alimentar',
        'Atendimento dedicado a famílias com crianças em fase de introdução alimentar. Perfil demonstrativo: substitua pelos dados do profissional real antes de publicar o sistema.',
        'Atendimentos pelo chat interno em horário comercial.',
    ]);

    if ((int) $pdo->query("SELECT COUNT(*) FROM children WHERE user_id = {$parentId}")->fetchColumn() === 0) {
        $pdo->prepare('INSERT INTO children (user_id, name, birth_date) VALUES (?, ?, DATE_SUB(CURDATE(), INTERVAL 9 MONTH))')
            ->execute([$parentId, 'Theo']);
    }

    $ageIds = [];
    foreach ($pdo->query('SELECT id, slug FROM age_ranges') as $row) {
        $ageIds[$row['slug']] = (int) $row['id'];
    }

    $products = [
        ['Papinha de abóbora e frango', 'papinha-abobora-frango', 'Textura macia e combinação simples para refeições do dia a dia.', '6-8-meses', 16.90, 180, 'Abóbora\nFrango desfiado\nBatata\nCheiro-verde', 'Consulte a rotulagem e o nutricionista responsável.', 'Manter refrigerado conforme orientação da embalagem.', 40, 'assets/img/product-01.svg'],
        ['Arroz cremoso com legumes', 'arroz-cremoso-legumes', 'Refeição com textura adaptada e ingredientes de sabor suave.', '8-10-meses', 18.90, 200, 'Arroz\nCenoura\nAbobrinha\nFrango', 'Consulte a rotulagem e o nutricionista responsável.', 'Manter refrigerado conforme orientação da embalagem.', 35, 'assets/img/product-02.svg'],
        ['Carne, feijão e legumes', 'carne-feijao-legumes', 'Combinação caseira pensada para ampliar variedade de sabores e texturas.', '10-12-meses', 20.90, 220, 'Carne bovina\nFeijão\nBatata-doce\nCenoura', 'Consulte a rotulagem e o nutricionista responsável.', 'Manter refrigerado conforme orientação da embalagem.', 32, 'assets/img/product-03.svg'],
        ['Mini almoço colorido', 'mini-almoco-colorido', 'Prato completo em porção infantil para crianças de 1 a 3 anos.', '1-3-anos', 23.90, 260, 'Arroz\nFeijão\nFrango\nBrócolis\nCenoura', 'Consulte a rotulagem e o nutricionista responsável.', 'Manter refrigerado conforme orientação da embalagem.', 28, 'assets/img/product-04.svg'],
    ];

    $stmt = $pdo->prepare(
        'INSERT INTO products (name, slug, description, age_range_id, price, weight_grams, ingredients, allergens, storage_info, stock, image_url)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), age_range_id = VALUES(age_range_id), price = VALUES(price), weight_grams = VALUES(weight_grams), ingredients = VALUES(ingredients), allergens = VALUES(allergens), storage_info = VALUES(storage_info), stock = VALUES(stock), image_url = VALUES(image_url), active = 1'
    );

    foreach ($products as $product) {
        $ageSlug = $product[3];
        $stmt->execute([$product[0], $product[1], $product[2], $ageIds[$ageSlug], $product[4], $product[5], $product[6], $product[7], $product[8], $product[9], $product[10]]);
    }


    $recipes = [
        ['Creme de abóbora com frango', 'creme-abobora-frango', 'Receita simples com textura adequada à fase inicial do cardápio Baby Food.', '6-8-meses', 20, "Abóbora cozida\nFrango bem cozido\nÁgua do cozimento", "Cozinhe os ingredientes até ficarem macios.\nAjuste a textura conforme a orientação profissional para a fase da criança.\nSirva em temperatura segura.", 'Conteúdo demonstrativo. Quantidades e textura devem ser revisadas por nutricionista responsável antes de uso real.', 'Verifique alergias e orientações individuais.', 'assets/img/recipe-01.svg'],
        ['Arroz macio com cenoura e abobrinha', 'arroz-cenoura-abobrinha', 'Preparação colorida e prática para ampliar a variedade de alimentos.', '8-10-meses', 25, "Arroz\nCenoura\nAbobrinha\nÁgua", "Cozinhe o arroz e os legumes até ficarem bem macios.\nAjuste a apresentação e a textura para a fase da criança.\nEspere esfriar antes de servir.", 'Conteúdo demonstrativo e não substitui orientação profissional.', 'Verifique alergias e orientações individuais.', 'assets/img/recipe-02.svg'],
        ['Feijão cremoso com carne e legumes', 'feijao-carne-legumes', 'Receita completa para variar sabores no fim do primeiro ano.', '10-12-meses', 30, "Feijão cozido\nCarne bem cozida\nCenoura\nBatata", "Cozinhe todos os ingredientes adequadamente.\nMonte a refeição respeitando a textura orientada para a criança.\nSirva em temperatura segura.", 'Conteúdo demonstrativo. A equipe profissional deve validar a composição final.', 'Verifique alergias e orientações individuais.', 'assets/img/recipe-03.svg'],
        ['Pratinho colorido da família', 'pratinho-colorido-familia', 'Ideia de refeição com alimentos variados para crianças de 1 a 3 anos.', '1-3-anos', 30, "Arroz\nFeijão\nFrango\nBrócolis\nCenoura", "Prepare os alimentos com pouco processamento.\nOrganize os itens separadamente para apresentar cores e texturas.\nSirva em porção definida pelo profissional responsável.", 'Conteúdo demonstrativo. Porções e necessidades variam por criança.', 'Verifique alergias e orientações individuais.', 'assets/img/recipe-04.svg'],
    ];

    $stmt = $pdo->prepare(
        'INSERT INTO recipes (title, slug, description, age_range_id, prep_minutes, ingredients, steps, nutrition_notes, allergens, image_url, created_by, reviewed_by, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "published")
         ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), age_range_id = VALUES(age_range_id), prep_minutes = VALUES(prep_minutes), ingredients = VALUES(ingredients), steps = VALUES(steps), nutrition_notes = VALUES(nutrition_notes), allergens = VALUES(allergens), image_url = VALUES(image_url), status = "published"'
    );

    foreach ($recipes as $recipe) {
        $stmt->execute([$recipe[0], $recipe[1], $recipe[2], $ageIds[$recipe[3]], $recipe[4], $recipe[5], $recipe[6], $recipe[7], $recipe[8], $recipe[9], $nutritionistId, $nutritionistId]);
    }

    $recipeMap = [];
    foreach ($pdo->query('SELECT id, slug FROM recipes') as $row) {
        $recipeMap[$row['slug']] = (int) $row['id'];
    }

    $videos = [
        ['Como preparar um creme de abóbora', 'video-creme-abobora', 'Demonstração de preparo e organização dos ingredientes.', '6-8-meses', 6, 'demo', 'assets/img/video-01.svg', $recipeMap['creme-abobora-frango'] ?? null],
        ['Texturas na fase de 8 a 10 meses', 'video-texturas-8-10', 'Vídeo demonstrativo para explicar como a plataforma pode organizar conteúdos por fase.', '8-10-meses', 8, 'demo', 'assets/img/video-02.svg', $recipeMap['arroz-cenoura-abobrinha'] ?? null],
        ['Montando uma refeição colorida', 'video-refeicao-colorida', 'Exemplo de conteúdo em vídeo para responsáveis.', '1-3-anos', 7, 'demo', 'assets/img/video-03.svg', $recipeMap['pratinho-colorido-familia'] ?? null],
    ];

    $stmt = $pdo->prepare(
        'INSERT INTO videos (title, slug, description, age_range_id, duration_minutes, video_url, thumbnail_url, related_recipe_id, created_by, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "published")
         ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), age_range_id = VALUES(age_range_id), duration_minutes = VALUES(duration_minutes), video_url = VALUES(video_url), thumbnail_url = VALUES(thumbnail_url), related_recipe_id = VALUES(related_recipe_id), status = "published"'
    );
    foreach ($videos as $video) {
        $stmt->execute([$video[0], $video[1], $video[2], $ageIds[$video[3]], $video[4], $video[5], $video[6], $video[7], $nutritionistId]);
    }

    $articles = [
        ['Proteínas na alimentação infantil', 'proteinas-alimentacao-infantil', 'protein', 'Entenda como a plataforma pode apresentar fontes de proteína em uma linguagem simples para responsáveis.', 'Proteínas participam de diversas funções do organismo e podem estar presentes em alimentos como feijões, ovos, carnes e outras opções. No Baby Food, este conteúdo deve ser revisado por nutricionista antes da publicação e adaptado à faixa etária e ao contexto de cada criança.', null],
        ['Variedade de cores no prato', 'variedade-cores-prato', 'nutrition', 'Ideias para apresentar uma alimentação variada de forma visual e organizada.', 'A variedade de alimentos pode ajudar a família a oferecer experiências diferentes de sabor, aroma e textura. O sistema pode destacar receitas, ingredientes e conteúdos educativos, sempre com revisão profissional para cada fase da criança.', null],
        ['Higiene e conservação das refeições', 'higiene-conservacao-refeicoes', 'food', 'Boas práticas de organização, armazenamento e segurança alimentar.', 'A área educativa pode reunir orientações sobre higiene de mãos, utensílios, armazenamento e temperatura. Como o produto será usado por famílias com crianças pequenas, as recomendações finais devem ser validadas por profissional habilitado e seguir a legislação sanitária aplicável.', null],
    ];

    $stmt = $pdo->prepare(
        'INSERT INTO articles (title, slug, category, excerpt, content, age_range_id, status)
         VALUES (?, ?, ?, ?, ?, ?, "published")
         ON DUPLICATE KEY UPDATE title = VALUES(title), category = VALUES(category), excerpt = VALUES(excerpt), content = VALUES(content), age_range_id = VALUES(age_range_id), status = "published"'
    );
    foreach ($articles as $article) {
        $stmt->execute([$article[0], $article[1], $article[2], $article[3], $article[4], $article[5]]);
    }

    $conversationId = db_value($pdo, 'SELECT id FROM conversations WHERE parent_user_id = ? AND nutritionist_user_id = ?', [$parentId, $nutritionistId]);
    if (!$conversationId) {
        $pdo->prepare('INSERT INTO conversations (parent_user_id, nutritionist_user_id, last_message_at) VALUES (?, ?, NOW())')
            ->execute([$parentId, $nutritionistId]);
        $conversationId = (int) $pdo->lastInsertId();
        $pdo->prepare('INSERT INTO messages (conversation_id, sender_id, body, is_read) VALUES (?, ?, ?, 0)')
            ->execute([$conversationId, $nutritionistId, 'Olá! Este é o chat interno demonstrativo do Baby Food. Você pode responder por aqui.']);
    }

    return [
        'admin' => [$adminEmail, $demoPassword],
        'nutritionist' => [$nutritionistEmail, $demoPassword],
        'parent' => [$parentEmail, $demoPassword],
    ];
}
