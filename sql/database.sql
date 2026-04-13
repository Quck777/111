-- Medieval Realm RPG - Database v3.0.0
-- Полная база данных для браузерной MMORPG
-- Версия: 3.0.0 (Глобальный релиз)

SET NAMES utf8mb4;

-- =====================
-- ОСНОВНЫЕ ТАБЛИЦЫ
-- =====================

-- Пользователи
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    race ENUM('human','elf','dwarf','orc','undead') DEFAULT 'human',
    class ENUM('warrior','mage','archer','rogue','paladin') DEFAULT 'warrior',
    level INT DEFAULT 1,
    exp INT DEFAULT 0,
    hp INT DEFAULT 100,
    max_hp INT DEFAULT 100,
    mp INT DEFAULT 50,
    max_mp INT DEFAULT 50,
    atk INT DEFAULT 10,
    def INT DEFAULT 5,
    str INT DEFAULT 10,
    dex INT DEFAULT 10,
    intel INT DEFAULT 10,
    vit INT DEFAULT 10,
    luck INT DEFAULT 10,
    gold INT DEFAULT 100,
    crystals INT DEFAULT 0,
    pvp_rating INT DEFAULT 1000,
    pvp_wins INT DEFAULT 0,
    pvp_losses INT DEFAULT 0,
    battles_won INT DEFAULT 0,
    battles_lost INT DEFAULT 0,
    deaths INT DEFAULT 0,
    last_login DATETIME DEFAULT NULL,
    last_ip VARCHAR(45) DEFAULT NULL,
    role ENUM('player','moderator','admin','banned') DEFAULT 'player',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Предметы (магазин)
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('weapon','armor','helmet','boots','ring','amulet','potion','food','scroll','material') NOT NULL,
    rarity ENUM('common','uncommon','rare','epic','legendary') DEFAULT 'common',
    value INT DEFAULT 10,
    required_level INT DEFAULT 1,
    atk_bonus INT DEFAULT 0,
    def_bonus INT DEFAULT 0,
    hp_bonus INT DEFAULT 0,
    mp_bonus INT DEFAULT 0,
    description VARCHAR(255),
    is_shop_item TINYINT(1) DEFAULT 0,
    is_temporary TINYINT(1) DEFAULT 0,
    duration_minutes INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Инвентарь игроков
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT DEFAULT 1,
    equipped TINYINT(1) DEFAULT 0,
    durability INT DEFAULT 100,
    max_durability INT DEFAULT 100,
    expires_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Монстры (PvE)
CREATE TABLE IF NOT EXISTS enemies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    level INT DEFAULT 1,
    hp INT DEFAULT 50,
    max_hp INT DEFAULT 50,
    atk INT DEFAULT 5,
    def INT DEFAULT 2,
    exp INT DEFAULT 10,
    gold INT DEFAULT 5,
    crystals_reward INT DEFAULT 0,
    is_boss TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Квесты
CREATE TABLE IF NOT EXISTS quests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('kill','collect','gold','level') NOT NULL,
    target_id INT DEFAULT 0,
    target_count INT DEFAULT 1,
    exp_reward INT DEFAULT 50,
    gold_reward INT DEFAULT 25,
    required_level INT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Квесты игроков
CREATE TABLE IF NOT EXISTS user_quests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quest_id INT NOT NULL,
    progress INT DEFAULT 0,
    completed TINYINT(1) DEFAULT 0,
    claimed TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quest_id) REFERENCES quests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Достижения
CREATE TABLE IF NOT EXISTS achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type VARCHAR(50) NOT NULL,
    target_value INT DEFAULT 1,
    reward_gold INT DEFAULT 0,
    reward_exp INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Достижения игроков
CREATE TABLE IF NOT EXISTS user_achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    achievement_id INT NOT NULL,
    completed TINYINT(1) DEFAULT 0,
    claimed TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Навыки классов
CREATE TABLE IF NOT EXISTS skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    mp_cost INT DEFAULT 10,
    cooldown INT DEFAULT 3,
    damage_multiplier DECIMAL(3,2) DEFAULT 1.0,
    heal_amount INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Навыки игроков
CREATE TABLE IF NOT EXISTS user_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skill_id INT NOT NULL,
    current_cooldown INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Рецепты крафта
CREATE TABLE IF NOT EXISTS recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    result_item_id INT DEFAULT 0,
    result_quantity INT DEFAULT 1,
    required_level INT DEFAULT 1,
    materials VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Локации
CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    min_level INT DEFAULT 1,
    unlock_price INT DEFAULT 0,
    is_pvp TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Доступные локации игроков
CREATE TABLE IF NOT EXISTS user_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    location_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Боевые лицензии
CREATE TABLE IF NOT EXISTS battle_licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('pvp','pve_bot') NOT NULL,
    price_gold INT DEFAULT 0,
    price_crystals INT DEFAULT 0,
    attacks_allowed INT DEFAULT 10,
    duration_hours INT DEFAULT 24,
    required_level INT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Лицензии игроков
CREATE TABLE IF NOT EXISTS user_battle_licenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    license_id INT NOT NULL,
    attacks_remaining INT DEFAULT 0,
    expires_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (license_id) REFERENCES battle_licenses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- История боев
CREATE TABLE IF NOT EXISTS battles_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    battle_type ENUM('pve','pvp','raid') NOT NULL,
    opponent_name VARCHAR(50),
    opponent_level INT,
    result ENUM('win','lose'),
    damage_dealt INT DEFAULT 0,
    damage_taken INT DEFAULT 0,
    exp_gained INT DEFAULT 0,
    gold_gained INT DEFAULT 0,
    rating_change INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Чат
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    channel VARCHAR(20) DEFAULT 'global',
    message_type ENUM('user', 'system', 'announcement') DEFAULT 'user',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Анти-спам
CREATE TABLE IF NOT EXISTS chat_spam (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    last_message_time INT NOT NULL,
    message_count INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_chat_channel ON chat_messages(channel);
CREATE INDEX idx_chat_timestamp ON chat_messages(timestamp);

-- Гильдии
CREATE TABLE IF NOT EXISTS guilds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255),
    leader_id INT NOT NULL,
    level INT DEFAULT 1,
    exp INT DEFAULT 0,
    gold INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Участники гильдий
CREATE TABLE IF NOT EXISTS guild_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guild_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('leader','officer','member') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guild_id) REFERENCES guilds(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Чат гильдии
CREATE TABLE IF NOT EXISTS guild_chat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guild_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guild_id) REFERENCES guilds(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Рейд боссы
CREATE TABLE IF NOT EXISTS raid_bosses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    level INT DEFAULT 10,
    hp INT DEFAULT 500,
    max_hp INT DEFAULT 500,
    atk INT DEFAULT 30,
    exp INT DEFAULT 200,
    gold INT DEFAULT 150,
    player_limit INT DEFAULT 10,
    is_active TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Участники рейда
CREATE TABLE IF NOT EXISTS raid_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    raid_id INT NOT NULL,
    user_id INT NOT NULL,
    damage_dealt INT DEFAULT 0,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (raid_id) REFERENCES raid_bosses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ежедневные награды
CREATE TABLE IF NOT EXISTS daily_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    last_claim DATETIME,
    streak INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PvP бои
CREATE TABLE IF NOT EXISTS pvp_battles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player1_id INT NOT NULL,
    player2_id INT NOT NULL,
    player1_hp INT DEFAULT 100,
    player2_hp INT DEFAULT 100,
    player1_mp INT DEFAULT 50,
    player2_mp INT DEFAULT 50,
    status ENUM('active','finished') DEFAULT 'active',
    turn_player_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (player1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (player2_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================
-- НАЧАЛЬНЫЕ ДАННЫЕ
-- =====================

-- Тестовый пользователь Hero (пароль: hero123)
INSERT INTO users (username, password, race, class, level, exp, hp, max_hp, mp, max_mp, atk, def, str, dex, intel, vit, luck, gold, crystals, pvp_rating, pvp_wins, pvp_losses, battles_won, battles_lost, role) 
VALUES ('Hero', '$2y$10$abcdefghijklmnopqrst', 'human', 'warrior', 5, 500, 120, 120, 60, 60, 18, 8, 12, 10, 8, 12, 10, 500, 10, 1000, 3, 2, 15, 8, 'admin');

-- Предметы (20 штук)
INSERT INTO items (name, type, rarity, value, required_level, atk_bonus, def_bonus, hp_bonus, mp_bonus, description, is_temporary, duration_minutes) VALUES
('Деревянный меч', 'weapon', 'common', 10, 1, 5, 0, 0, 0, 'Простой деревянный меч', 0, 0),
('Железный меч', 'weapon', 'common', 50, 3, 10, 0, 0, 0, 'Обычный железный меч', 0, 0),
('Стальной меч', 'weapon', 'uncommon', 150, 5, 18, 0, 0, 0, 'Хороший стальной меч', 0, 0),
('Посеребренный меч', 'weapon', 'rare', 400, 8, 28, 0, 0, 0, 'Меч с серебряным напылением', 0, 0),
('Божественный меч', 'weapon', 'legendary', 2000, 15, 50, 0, 0, 0, 'Легендарный меч богов', 0, 0),
('Кожаная броня', 'armor', 'common', 30, 1, 0, 3, 0, 0, 'Простая кожаная броня', 0, 0),
('Кольчуга', 'armor', 'common', 80, 3, 0, 8, 0, 0, 'Стальная кольчуга', 0, 0),
('Латный доспех', 'armor', 'uncommon', 250, 6, 0, 18, 0, 0, 'Полный латный доспех', 0, 0),
('Элитный доспех', 'armor', 'rare', 600, 10, 0, 35, 0, 0, 'Редкий элитный доспех', 0, 0),
('Божественные латы', 'armor', 'legendary', 3000, 15, 0, 60, 0, 0, 'Легендарные латы', 0, 0),
('Малое зелье HP', 'potion', 'common', 20, 1, 0, 0, 30, 0, 'Восстанавливает 30 HP', 0, 0),
('Среднее зелье HP', 'potion', 'uncommon', 50, 3, 0, 0, 75, 0, 'Восстанавливает 75 HP', 0, 0),
('Большое зелье HP', 'potion', 'rare', 150, 6, 0, 0, 150, 0, 'Восстанавливает 150 HP', 0, 0),
('Малое зелье MP', 'potion', 'common', 20, 1, 0, 0, 0, 20, 'Восстанавливает 20 MP', 0, 0),
('Среднее зелье MP', 'potion', 'uncommon', 50, 3, 0, 0, 0, 50, 'Восстанавливает 50 MP', 0, 0),
('Большое зелье MP', 'potion', 'rare', 150, 6, 0, 0, 0, 100, 'Восстанавливает 100 MP', 0, 0),
('Шлем воина', 'helmet', 'uncommon', 100, 3, 0, 15, 0, 0, 'Увеличивает HP на 15', 1, 60),
('Ботинки скорости', 'boots', 'rare', 200, 5, 0, 0, 0, 0, 'Увеличивает ловкость', 1, 60),
('Кольцо удачи', 'ring', 'epic', 500, 8, 0, 0, 0, 0, 'Увеличивает удачу', 1, 30),
('Амулет силы', 'amulet', 'legendary', 1000, 10, 0, 0, 0, 0, 'Увеличивает силу', 1, 60);

-- Монстры (12 штук)
INSERT INTO enemies (name, level, hp, max_hp, atk, def, exp, gold, crystals_reward, is_boss) VALUES
('Крыса', 1, 30, 30, 4, 1, 10, 5, 0, 0),
('Паук', 2, 45, 45, 6, 2, 15, 8, 0, 0),
('Волк', 3, 60, 60, 8, 3, 20, 12, 0, 0),
('Орк', 4, 80, 80, 10, 4, 30, 20, 0, 0),
('Скелет', 5, 100, 100, 12, 5, 40, 30, 0, 0),
('Гоблин', 6, 130, 130, 15, 6, 55, 40, 0, 0),
('Призрак', 7, 160, 160, 18, 7, 70, 55, 0, 0),
('Огр', 8, 200, 200, 22, 9, 90, 75, 1, 1),
('Дракончик', 9, 250, 250, 28, 12, 120, 100, 2, 0),
('Древний дракон', 10, 500, 500, 40, 20, 300, 250, 5, 1),
('Тролль', 12, 600, 600, 35, 15, 250, 200, 3, 0),
('Балрог', 15, 1000, 1000, 50, 25, 500, 400, 10, 1);

-- Квесты (5 штук)
INSERT INTO quests (title, description, type, target_id, target_count, exp_reward, gold_reward, required_level) VALUES
('Первая кровь', 'Убейте 3 крыс', 'kill', 1, 3, 30, 20, 1),
('Охотник на пауков', 'Убейте 5 пауков', 'kill', 2, 5, 50, 35, 2),
('Защитник деревни', 'Убейте 10 орков', 'kill', 4, 10, 100, 75, 4),
('Накоптельщик', 'Соберите 100 золота', 'gold', 0, 100, 80, 50, 3),
('Покоритель', 'Достигните 5 уровня', 'level', 0, 5, 200, 100, 1);

-- Достижения (4 штуки)
INSERT INTO achievements (name, description, type, target_value, reward_gold, reward_exp) VALUES
('Новичок', 'Достигните 5 уровня', 'level', 5, 50, 100),
('Опытный', 'Достигните 10 уровня', 'level', 10, 100, 250),
('Мастер', 'Достигните 20 уровня', 'level', 20, 250, 500),
('PvP Чемпион', 'Выиграйте 50 PvP боев', 'pvp_wins', 50, 500, 1000);

-- Навыки (15 штук - по 3 на класс)
INSERT INTO skills (class, name, description, mp_cost, cooldown, damage_multiplier, heal_amount) VALUES
('warrior', 'Сильный удар', 'Мощный удар', 15, 2, 1.5, 0),
('warrior', 'Вихрь', 'Вращающийся удар', 25, 4, 2.0, 0),
('warrior', 'Берсерк', 'Восстановление HP', 20, 3, 0, 30),
('mage', 'Огненный шар', 'Огненная магия', 20, 2, 1.8, 0),
('mage', 'Ледяная стрела', 'Ледяная магия', 15, 2, 1.5, 0),
('mage', 'Восстановление', 'Лечение', 30, 4, 0, 50),
('archer', 'Точный выстрел', 'Прицельный удар', 10, 1, 1.4, 0),
('archer', 'Залп', 'Несколько выстрелов', 25, 3, 2.2, 0),
('archer', 'Концентрация', 'Усиление', 15, 3, 0, 20),
('rogue', 'Удар в спину', 'Критический удар', 15, 2, 2.0, 0),
('rogue', 'Яд', 'Отравление', 20, 3, 1.3, 0),
('rogue', 'Исчезновение', 'Скрытность', 25, 5, 0, 0),
('paladin', 'Святой удар', 'Атака святостью', 20, 2, 1.6, 0),
('paladin', 'Щит веры', 'Защита', 15, 3, 0, 40),
('paladin', 'Возложение рук', 'Лечение', 35, 4, 0, 60);

-- Локации (8 штук)
INSERT INTO locations (name, description, min_level, unlock_price, is_pvp) VALUES
('Тихий лес', 'Начальная локация', 1, 0, 0),
('Тёмная пещера', 'Опасная пещера', 3, 50, 0),
('Заброшенная шахта', 'Шахта с монстрами', 5, 100, 0),
('Долина орков', 'Лагерь орков', 7, 200, 0),
('Кладбище нежити', 'Нежить', 8, 250, 0),
('Башня мага', 'Башня злого мага', 10, 500, 0),
('Логово дракона', 'Древний дракон', 12, 1000, 0),
('PvP Арена', 'PvP бои', 5, 0, 1);

-- Лицензии (7 штук)
INSERT INTO battle_licenses (name, type, price_gold, price_crystals, attacks_allowed, duration_hours, required_level) VALUES
('Новичок PvE', 'pve_bot', 0, 0, 20, 24, 1),
('Опытный PvE', 'pve_bot', 100, 0, 50, 24, 5),
('Мастер PvE', 'pve_bot', 200, 0, 100, 24, 10),
('PvP Лицензия D', 'pvp', 50, 0, 10, 24, 3),
('PvP Лицензия C', 'pvp', 150, 0, 25, 24, 6),
('PvP Лицензия B', 'pvp', 300, 0, 50, 24, 10),
('PvP Лицензия A', 'pvp', 500, 0, 100, 24, 15);

-- Рейд боссы (3 штуки)
INSERT INTO raid_bosses (name, description, level, hp, max_hp, atk, exp_reward, gold_reward, participants_limit) VALUES
('КорольГоблин', 'Вождь гоблинов', 8, 800, 800, 25, 300, 250, 10),
('Призрак мага', 'Дух древнего мага', 12, 1500, 1500, 40, 500, 400, 10),
('Драконагма', 'Огненный дракон', 15, 2500, 2500, 60, 800, 600, 20);

-- Предметы для Hero (инвентарь)
INSERT INTO inventory (user_id, item_id, quantity, equipped) VALUES 
(1, 1, 1, 1), 
(1, 6, 1, 1), 
(1, 11, 5, 0), 
(1, 14, 3, 0);

-- Локации для Hero
INSERT INTO user_locations (user_id, location_id) VALUES 
(1, 1), (1, 2), (1, 8);

-- Лицензия для Hero
INSERT INTO user_battle_licenses (user_id, license_id, attacks_remaining, expires_at) VALUES 
(1, 1, 20, DATE_ADD(NOW(), INTERVAL 24 HOUR));

-- =====================
-- РЫНОК
-- =====================

CREATE TABLE IF NOT EXISTS market_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    item_id INT NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    item_type VARCHAR(20) NOT NULL,
    item_rarity ENUM('common','uncommon','rare','epic','legendary') DEFAULT 'common',
    price_gold INT NOT NULL,
    price_crystals INT DEFAULT 0,
    quantity INT DEFAULT 1,
    description VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Индексы для рынка
CREATE INDEX idx_market_active ON market_listings(is_active);
CREATE INDEX idx_market_seller ON market_listings(seller_id);
CREATE INDEX idx_market_type ON market_listings(item_type);
CREATE INDEX idx_market_rarity ON market_listings(item_rarity);

-- =====================
-- ВОЙНЫ ГИЛЬДИЙ
-- =====================

-- Альянсы гильдий
CREATE TABLE IF NOT EXISTS guild_alliances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    leader_guild_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_guild_id) REFERENCES guilds(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Войны между гильдиями
CREATE TABLE IF NOT EXISTS guild_wars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attacker_guild_id INT NOT NULL,
    defender_guild_id INT NOT NULL,
    attacker_score INT DEFAULT 0,
    defender_score INT DEFAULT 0,
    status ENUM('pending','active','completed','cancelled') DEFAULT 'pending',
    start_time DATETIME DEFAULT NULL,
    end_time DATETIME DEFAULT NULL,
    winner_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attacker_guild_id) REFERENCES guilds(id) ON DELETE CASCADE,
    FOREIGN KEY (defender_guild_id) REFERENCES guilds(id) ON DELETE CASCADE,
    FOREIGN KEY (winner_id) REFERENCES guilds(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Участники войны (члены гильдии, участвующие в бою)
CREATE TABLE IF NOT EXISTS war_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    war_id INT NOT NULL,
    user_id INT NOT NULL,
    kills INT DEFAULT 0,
    deaths INT DEFAULT 0,
    damage_dealt INT DEFAULT 0,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (war_id) REFERENCES guild_wars(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Территории гильдий
CREATE TABLE IF NOT EXISTS guild_territories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guild_id INT NOT NULL,
    territory_name VARCHAR(50) NOT NULL,
    resource_bonus INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guild_id) REFERENCES guilds(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- История войн
CREATE TABLE IF NOT EXISTS war_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    war_id INT NOT NULL,
    event_type VARCHAR(30) NOT NULL,
    description TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (war_id) REFERENCES guild_wars(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_wars_status ON guild_wars(status);
CREATE INDEX idx_wars_guilds ON guild_wars(attacker_guild_id, defender_guild_id);
CREATE INDEX idx_participants_war ON war_participants(war_id);
