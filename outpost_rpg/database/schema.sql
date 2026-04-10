-- База данных для РПГ игры "ФОРПОСТ"
-- Создание базы данных
CREATE DATABASE IF NOT EXISTS outpost_rpg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE outpost_rpg;

-- Таблица пользователей (игроков)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    avatar_path VARCHAR(255) DEFAULT 'assets/images/characters/default.png',
    rank_id INT DEFAULT 1,
    experience BIGINT DEFAULT 0,
    level INT DEFAULT 1,
    gold BIGINT DEFAULT 100,
    gems INT DEFAULT 0,
    energy INT DEFAULT 100,
    max_energy INT DEFAULT 100,
    health INT DEFAULT 100,
    max_health INT DEFAULT 100,
    strength INT DEFAULT 10,
    agility INT DEFAULT 10,
    intelligence INT DEFAULT 10,
    stamina INT DEFAULT 10,
    luck INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_online BOOLEAN DEFAULT FALSE,
    is_banned BOOLEAN DEFAULT FALSE,
    ban_reason TEXT NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_rank (rank_id),
    INDEX idx_level (level)
) ENGINE=InnoDB;

-- Таблица рангов
CREATE TABLE ranks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    min_experience BIGINT NOT NULL,
    bonus_gold DECIMAL(5,2) DEFAULT 0,
    bonus_xp DECIMAL(5,2) DEFAULT 0,
    color VARCHAR(20) DEFAULT '#FFFFFF',
    icon_path VARCHAR(255) DEFAULT 'assets/images/ranks/default.png',
    INDEX idx_min_exp (min_experience)
) ENGINE=InnoDB;

-- Заполнение рангов
INSERT INTO ranks (name, min_experience, bonus_gold, bonus_xp, color) VALUES
('Новичок', 0, 0, 0, '#808080'),
('Опытный', 1000, 5, 5, '#008000'),
('Ветеран', 5000, 10, 10, '#0000FF'),
('Элита', 15000, 15, 15, '#800080'),
('Мастер', 50000, 20, 20, '#FFA500'),
('Легенда', 100000, 25, 25, '#FF0000'),
('Герой', 250000, 30, 30, '#FFD700'),
('Бог', 1000000, 50, 50, '#9370DB');

-- Таблица предметов
CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('weapon', 'armor', 'helmet', 'boots', 'ring', 'amulet', 'potion', 'material', 'quest', 'other') NOT NULL,
    rarity ENUM('common', 'uncommon', 'rare', 'epic', 'legendary', 'artifact') DEFAULT 'common',
    min_level INT DEFAULT 1,
    image_path VARCHAR(255) DEFAULT 'assets/images/items/default.png',
    stackable BOOLEAN DEFAULT TRUE,
    max_stack INT DEFAULT 999,
    sell_price INT DEFAULT 1,
    buy_price INT DEFAULT 1,
    strength_bonus INT DEFAULT 0,
    agility_bonus INT DEFAULT 0,
    intelligence_bonus INT DEFAULT 0,
    stamina_bonus INT DEFAULT 0,
    luck_bonus INT DEFAULT 0,
    health_bonus INT DEFAULT 0,
    energy_bonus INT DEFAULT 0,
    damage_min INT DEFAULT 0,
    damage_max INT DEFAULT 0,
    defense INT DEFAULT 0,
    durability INT DEFAULT 100,
    max_durability INT DEFAULT 100,
    is_tradable BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_rarity (rarity),
    INDEX idx_level (min_level)
) ENGINE=InnoDB;

-- Инвентарь игрока
CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT DEFAULT 1,
    slot_number INT NOT NULL,
    durability INT DEFAULT 100,
    enchantment_level INT DEFAULT 0,
    socket_count INT DEFAULT 0,
    acquired_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_slot (user_id, slot_number),
    INDEX idx_user (user_id),
    INDEX idx_item (item_id)
) ENGINE=InnoDB;

-- Экипировка
CREATE TABLE equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    slot_type ENUM('weapon', 'armor', 'helmet', 'boots', 'ring', 'amulet') NOT NULL,
    item_id INT NOT NULL,
    durability INT DEFAULT 100,
    enchantment_level INT DEFAULT 0,
    equipped_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_slot_type (user_id, slot_type),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- Локации
CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('city', 'forest', 'mine', 'dungeon', 'field', 'special') NOT NULL,
    min_level INT DEFAULT 1,
    max_level INT DEFAULT 100,
    background_path VARCHAR(255) DEFAULT 'assets/images/backgrounds/default.png',
    music_path VARCHAR(255) DEFAULT '',
    danger_level INT DEFAULT 1,
    resource_nodes INT DEFAULT 0,
    monster_spawn_rate DECIMAL(5,2) DEFAULT 0.5,
    is_pvp BOOLEAN DEFAULT FALSE,
    parent_location_id INT NULL,
    FOREIGN KEY (parent_location_id) REFERENCES locations(id) ON DELETE SET NULL,
    INDEX idx_type (type),
    INDEX idx_level (min_level, max_level)
) ENGINE=InnoDB;

-- Монстры
CREATE TABLE monsters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    level INT NOT NULL,
    image_path VARCHAR(255) DEFAULT 'assets/images/monsters/default.png',
    health INT NOT NULL,
    damage_min INT NOT NULL,
    damage_max INT NOT NULL,
    defense INT DEFAULT 0,
    experience_reward INT NOT NULL,
    gold_reward_min INT NOT NULL,
    gold_reward_max INT NOT NULL,
    loot_table_id INT NULL,
    spawn_locations JSON NULL,
    is_boss BOOLEAN DEFAULT FALSE,
    abilities JSON NULL,
    INDEX idx_level (level),
    INDEX idx_boss (is_boss)
) ENGINE=InnoDB;

-- Боты (NPC)
CREATE TABLE bots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('merchant', 'quest_giver', 'trainer', 'guard', 'civilian', 'special') NOT NULL,
    location_id INT NOT NULL,
    image_path VARCHAR(255) DEFAULT 'assets/images/characters/bot_default.png',
    dialogue JSON NULL,
    shop_items JSON NULL,
    quests_offered JSON NULL,
    services JSON NULL,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE,
    INDEX idx_location (location_id),
    INDEX idx_type (type)
) ENGINE=InnoDB;

-- Чат
CREATE TABLE chat_messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    channel ENUM('global', 'local', 'guild', 'party', 'trade', 'whisper') NOT NULL,
    message TEXT NOT NULL,
    recipient_id INT NULL,
    location_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_deleted BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    INDEX idx_channel (channel),
    INDEX idx_created (created_at),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- Лог действий игрока
CREATE TABLE action_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action_type ENUM('login', 'logout', 'combat', 'trade', 'craft', 'quest', 'achievement', 'chat', 'movement', 'other') NOT NULL,
    description TEXT NOT NULL,
    details JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_action (action_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Умения/Навыки
CREATE TABLE skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('combat', 'crafting', 'gathering', 'passive', 'active') NOT NULL,
    max_level INT DEFAULT 10,
    icon_path VARCHAR(255) DEFAULT 'assets/images/skills/default.png',
    cooldown INT DEFAULT 0,
    energy_cost INT DEFAULT 0,
    requirements JSON NULL,
    effects JSON NULL,
    INDEX idx_type (type)
) ENGINE=InnoDB;

-- Навыки игрока
CREATE TABLE user_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skill_id INT NOT NULL,
    level INT DEFAULT 1,
    experience BIGINT DEFAULT 0,
    last_used TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_skill (user_id, skill_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- Достижения
CREATE TABLE achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category ENUM('combat', 'exploration', 'social', 'crafting', 'collection', 'special') NOT NULL,
    points INT DEFAULT 10,
    icon_path VARCHAR(255) DEFAULT 'assets/images/achievements/default.png',
    requirements JSON NOT NULL,
    reward_json JSON NULL,
    INDEX idx_category (category)
) ENGINE=InnoDB;

-- Достижения игрока
CREATE TABLE user_achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    achievement_id INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    progress JSON NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_achievement (user_id, achievement_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- Квесты
CREATE TABLE quests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('main', 'side', 'daily', 'weekly', 'event') NOT NULL,
    giver_npc_id INT NULL,
    min_level INT DEFAULT 1,
    prerequisites JSON NULL,
    objectives JSON NOT NULL,
    rewards JSON NOT NULL,
    is_repeatable BOOLEAN DEFAULT FALSE,
    time_limit INT NULL,
    FOREIGN KEY (giver_npc_id) REFERENCES bots(id) ON DELETE SET NULL,
    INDEX idx_type (type),
    INDEX idx_level (min_level)
) ENGINE=InnoDB;

-- Активные квесты игрока
CREATE TABLE user_quests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quest_id INT NOT NULL,
    status ENUM('active', 'completed', 'failed', 'rewarded') DEFAULT 'active',
    progress JSON NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quest_id) REFERENCES quests(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_quest_status (user_id, quest_id, status),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Рынок/Торговля
CREATE TABLE market_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    item_instance_id INT NOT NULL,
    price INT NOT NULL,
    quantity INT DEFAULT 1,
    status ENUM('active', 'sold', 'cancelled', 'expired') DEFAULT 'active',
    buyer_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    sold_at TIMESTAMP NULL,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (item_instance_id) REFERENCES inventory(id) ON DELETE CASCADE,
    INDEX idx_seller (seller_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- История транзакций
CREATE TABLE transactions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    from_user_id INT NULL,
    to_user_id INT NOT NULL,
    amount_gold BIGINT DEFAULT 0,
    amount_gems INT DEFAULT 0,
    transaction_type ENUM('trade', 'market', 'quest_reward', 'monster_loot', 'system', 'donation') NOT NULL,
    description TEXT,
    item_id INT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    related_listing_id INT NULL,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE SET NULL,
    FOREIGN KEY (related_listing_id) REFERENCES market_listings(id) ON DELETE SET NULL,
    INDEX idx_from_user (from_user_id),
    INDEX idx_to_user (to_user_id),
    INDEX idx_type (transaction_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Гильдии/Кланы
CREATE TABLE guilds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    leader_id INT NOT NULL,
    level INT DEFAULT 1,
    experience BIGINT DEFAULT 0,
    gold BIGINT DEFAULT 0,
    max_members INT DEFAULT 50,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logo_path VARCHAR(255) DEFAULT 'assets/images/guilds/default.png',
    FOREIGN KEY (leader_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_leader (leader_id)
) ENGINE=InnoDB;

-- Члены гильдии
CREATE TABLE guild_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guild_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('leader', 'officer', 'member', 'recruit') DEFAULT 'recruit',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    contribution_points BIGINT DEFAULT 0,
    FOREIGN KEY (guild_id) REFERENCES guilds(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_guild_user (guild_id, user_id),
    INDEX idx_guild (guild_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- PvP бои
CREATE TABLE pvp_battles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    challenger_id INT NOT NULL,
    defender_id INT NOT NULL,
    winner_id INT NULL,
    loser_id INT NULL,
    battle_log JSON NULL,
    reward_gold INT DEFAULT 0,
    reward_xp INT DEFAULT 0,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    FOREIGN KEY (challenger_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (defender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (winner_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (loser_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_challenger (challenger_id),
    INDEX idx_defender (defender_id),
    INDEX idx_created (started_at)
) ENGINE=InnoDB;

-- Сессии игроков
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_token (session_token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB;

-- Настройки игры
CREATE TABLE game_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Заполнение начальными настройками
INSERT INTO game_settings (setting_key, setting_value, description) VALUES
('game_name', 'ФОРПОСТ', 'Название игры'),
('max_energy', '100', 'Максимальная энергия'),
('energy_regen_rate', '1', 'Восстановление энергии в минуту'),
('max_chat_message_length', '500', 'Максимальная длина сообщения'),
('market_tax_rate', '5', 'Налог на рынке в процентах'),
('max_inventory_slots', '50', 'Максимальное количество слотов инвентаря');

-- Представление для лидерборда
CREATE VIEW leaderboard AS
SELECT 
    u.id,
    u.username,
    u.avatar_path,
    u.level,
    u.experience,
    u.rank_id,
    r.name as rank_name,
    r.color as rank_color,
    COUNT(DISTINCT ua.achievement_id) as achievements_count,
    SUM(CASE WHEN t.to_user_id = u.id THEN t.amount_gold ELSE 0 END) - 
    SUM(CASE WHEN t.from_user_id = u.id THEN t.amount_gold ELSE 0 END) as total_gold_earned
FROM users u
LEFT JOIN ranks r ON u.rank_id = r.id
LEFT JOIN user_achievements ua ON u.id = ua.user_id
LEFT JOIN transactions t ON u.id = t.to_user_id OR u.id = t.from_user_id
WHERE u.is_banned = FALSE
GROUP BY u.id
ORDER BY u.experience DESC, u.level DESC;

-- Триггер для обновления ранга при изменении опыта
DELIMITER $$
CREATE TRIGGER update_user_rank
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    DECLARE new_rank_id INT;
    SELECT id INTO new_rank_id FROM ranks 
    WHERE NEW.experience >= min_experience 
    ORDER BY min_experience DESC 
    LIMIT 1;
    
    IF new_rank_id IS NOT NULL AND new_rank_id != NEW.rank_id THEN
        UPDATE users SET rank_id = new_rank_id WHERE id = NEW.id;
    END IF;
END$$
DELIMITER ;
