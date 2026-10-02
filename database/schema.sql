CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('parent','nutritionist','admin') NOT NULL DEFAULT 'parent',
    phone VARCHAR(30) NULL,
    avatar_url VARCHAR(500) NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS age_ranges (
    id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(40) NOT NULL,
    slug VARCHAR(40) NOT NULL UNIQUE,
    min_months TINYINT UNSIGNED NOT NULL,
    max_months TINYINT UNSIGNED NOT NULL,
    sort_order TINYINT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS children (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    birth_date DATE NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_children_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_children_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS nutritionists (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    crn VARCHAR(40) NOT NULL,
    specialty VARCHAR(160) NOT NULL,
    bio TEXT NULL,
    availability_note VARCHAR(255) NULL,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_nutritionists_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    age_range_id TINYINT UNSIGNED NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    weight_grams INT UNSIGNED NULL,
    ingredients TEXT NULL,
    allergens TEXT NULL,
    storage_info TEXT NULL,
    stock INT NOT NULL DEFAULT 0,
    image_url VARCHAR(500) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_age_range FOREIGN KEY (age_range_id) REFERENCES age_ranges(id),
    INDEX idx_products_active_age (active, age_range_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS recipes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    age_range_id TINYINT UNSIGNED NOT NULL,
    prep_minutes INT UNSIGNED NOT NULL DEFAULT 0,
    ingredients TEXT NOT NULL,
    steps TEXT NOT NULL,
    nutrition_notes TEXT NULL,
    allergens TEXT NULL,
    image_url VARCHAR(500) NULL,
    video_url VARCHAR(500) NULL,
    created_by INT UNSIGNED NULL,
    reviewed_by INT UNSIGNED NULL,
    status ENUM('draft','pending','published') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_recipes_age_range FOREIGN KEY (age_range_id) REFERENCES age_ranges(id),
    CONSTRAINT fk_recipes_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_recipes_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_recipes_status_age (status, age_range_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS videos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    age_range_id TINYINT UNSIGNED NOT NULL,
    duration_minutes INT UNSIGNED NOT NULL DEFAULT 0,
    video_url VARCHAR(500) NOT NULL,
    thumbnail_url VARCHAR(500) NULL,
    related_recipe_id INT UNSIGNED NULL,
    created_by INT UNSIGNED NULL,
    status ENUM('draft','pending','published') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_videos_age_range FOREIGN KEY (age_range_id) REFERENCES age_ranges(id),
    CONSTRAINT fk_videos_recipe FOREIGN KEY (related_recipe_id) REFERENCES recipes(id) ON DELETE SET NULL,
    CONSTRAINT fk_videos_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_videos_status_age (status, age_range_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS articles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    category ENUM('nutrition','protein','vitamins','food') NOT NULL DEFAULT 'nutrition',
    excerpt TEXT NOT NULL,
    content MEDIUMTEXT NOT NULL,
    age_range_id TINYINT UNSIGNED NULL,
    image_url VARCHAR(500) NULL,
    status ENUM('draft','published') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_articles_age_range FOREIGN KEY (age_range_id) REFERENCES age_ranges(id) ON DELETE SET NULL,
    INDEX idx_articles_status_category (status, category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('received','preparing','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'received',
    payment_method ENUM('pix','card','demo') NOT NULL DEFAULT 'demo',
    payment_status ENUM('pending','paid','refunded') NOT NULL DEFAULT 'pending',
    recipient_name VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address_line VARCHAR(255) NOT NULL,
    city VARCHAR(120) NOT NULL,
    state VARCHAR(80) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    notes VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_orders_user_status (user_id, status),
    INDEX idx_orders_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    item_name VARCHAR(160) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    total DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order_items_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS conversations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_user_id INT UNSIGNED NOT NULL,
    nutritionist_user_id INT UNSIGNED NOT NULL,
    status ENUM('open','closed') NOT NULL DEFAULT 'open',
    last_message_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_conversations_parent FOREIGN KEY (parent_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_conversations_nutritionist FOREIGN KEY (nutritionist_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_conversation_pair (parent_user_id, nutritionist_user_id),
    INDEX idx_conversations_parent (parent_user_id, last_message_at),
    INDEX idx_conversations_nutritionist (nutritionist_user_id, last_message_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT UNSIGNED NOT NULL,
    sender_id INT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_messages_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_messages_conversation (conversation_id, id),
    INDEX idx_messages_unread (conversation_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(160) NOT NULL,
    body VARCHAR(500) NOT NULL,
    link VARCHAR(255) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_user (user_id, is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
