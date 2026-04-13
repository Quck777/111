<?php
/**
 * Admin Module - Admin Panel Functions
 * Medieval Realm RPG
 */

if (!defined('GAME_MODULE')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Check if user is admin
 */
function checkAdmin(): bool {
    if (empty($_SESSION['user_id'])) {
        return false;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    return $user && in_array($user['role'], ['admin', 'moderator']);
}

/**
 * Admin login
 */
function adminLogin(): void {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role IN ('admin', 'moderator')");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'error' => 'Неверный логин или пароль']);
        return;
    }
    
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_role'] = $user['role'];
    
    unset($user['password']);
    echo json_encode(['success' => true, 'user' => $user]);
}

/**
 * Get all users (admin)
 */
function adminGetUsers(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT id, username, email, class, race, level, hp, max_hp, mp, max_mp, atk, def, gold, pvp_rating, pvp_wins, pvp_losses, created_at, last_login, role FROM users ORDER BY level DESC LIMIT 100");
    echo json_encode($stmt->fetchAll());
}

/**
 * Update user (admin)
 */
function adminUpdateUser(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $userId = (int)$_POST['user_id'];
    $level = (int)$_POST['level'];
    $gold = (int)$_POST['gold'];
    $atk = (int)$_POST['atk'];
    $def = (int)$_POST['def'];
    
    $pdo = Database::getConnection();
    $pdo->prepare("UPDATE users SET level = ?, gold = ?, atk = ?, def = ? WHERE id = ?")
        ->execute([$level, $gold, $atk, $def, $userId]);
    
    echo json_encode(['success' => true, 'message' => 'Пользователь обновлен']);
}

/**
 * Delete user (admin)
 */
function adminDeleteUser(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $userId = (int)$_POST['user_id'];
    $pdo = Database::getConnection();
    
    $pdo->prepare("DELETE FROM inventory WHERE user_id = ?")->execute([$userId]);
    $pdo->prepare("DELETE FROM user_equipment WHERE user_id = ?")->execute([$userId]);
    $pdo->prepare("DELETE FROM guild_members WHERE user_id = ?")->execute([$userId]);
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    
    echo json_encode(['success' => true, 'message' => 'Пользователь удален']);
}

/**
 * Reset user password (admin)
 */
function adminResetPassword(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $userId = (int)$_POST['user_id'];
    $newPassword = $_POST['new_password'] ?? '';
    
    if (empty($newPassword)) {
        echo json_encode(['error' => 'Введите новый пароль']);
        return;
    }
    
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $pdo = Database::getConnection();
    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $userId]);
    
    echo json_encode(['success' => true, 'message' => 'Пароль сброшен']);
}

/**
 * Get all items (admin)
 */
function adminGetItems(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM items ORDER BY id");
    echo json_encode($stmt->fetchAll());
}

/**
 * Save item (admin)
 */
function adminSaveItem(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $itemId = (int)($_POST['item_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $type = $_POST['type'] ?? 'weapon';
    $rarity = $_POST['rarity'] ?? 'common';
    $value = (int)$_POST['value'];
    $atkBonus = (int)$_POST['atk_bonus'];
    $defBonus = (int)$_POST['def_bonus'];
    $requiredLevel = (int)$_POST['required_level'];
    $isShopItem = isset($_POST['is_shop_item']) ? 1 : 0;
    
    if (empty($name)) {
        echo json_encode(['error' => 'Введите название']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    if ($itemId > 0) {
        $pdo->prepare("UPDATE items SET name = ?, type = ?, rarity = ?, value = ?, atk_bonus = ?, def_bonus = ?, required_level = ?, is_shop_item = ? WHERE id = ?")
            ->execute([$name, $type, $rarity, $value, $atkBonus, $defBonus, $requiredLevel, $isShopItem, $itemId]);
        echo json_encode(['success' => true, 'message' => 'Предмет обновлен']);
    } else {
        $pdo->prepare("INSERT INTO items (name, type, rarity, value, atk_bonus, def_bonus, required_level, is_shop_item) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$name, $type, $rarity, $value, $atkBonus, $defBonus, $requiredLevel, $isShopItem]);
        echo json_encode(['success' => true, 'message' => 'Предмет создан']);
    }
}

/**
 * Delete item (admin)
 */
function adminDeleteItem(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $itemId = (int)$_POST['item_id'];
    $pdo = Database::getConnection();
    $pdo->prepare("DELETE FROM items WHERE id = ?")->execute([$itemId]);
    echo json_encode(['success' => true, 'message' => 'Предмет удален']);
}

/**
 * Get all enemies (admin)
 */
function adminGetEnemies(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM enemies ORDER BY level");
    echo json_encode($stmt->fetchAll());
}

/**
 * Save enemy (admin)
 */
function adminSaveEnemy(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $enemyId = (int)($_POST['enemy_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $level = (int)$_POST['level'];
    $hp = (int)$_POST['hp'];
    $atk = (int)$_POST['atk'];
    $def = (int)$_POST['def'];
    $exp = (int)$_POST['exp'];
    $gold = (int)$_POST['gold'];
    $isBoss = isset($_POST['is_boss']) ? 1 : 0;
    
    $pdo = Database::getConnection();
    
    if ($enemyId > 0) {
        $pdo->prepare("UPDATE enemies SET name = ?, level = ?, hp = ?, max_hp = ?, atk = ?, def = ?, exp = ?, gold = ?, is_boss = ? WHERE id = ?")
            ->execute([$name, $level, $hp, $hp, $atk, $def, $exp, $gold, $isBoss, $enemyId]);
        echo json_encode(['success' => true, 'message' => 'Монстр обновлен']);
    } else {
        $pdo->prepare("INSERT INTO enemies (name, level, hp, max_hp, atk, def, exp, gold, is_boss) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$name, $level, $hp, $hp, $atk, $def, $exp, $gold, $isBoss]);
        echo json_encode(['success' => true, 'message' => 'Монстр создан']);
    }
}

/**
 * Delete enemy (admin)
 */
function adminDeleteEnemy(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $enemyId = (int)$_POST['enemy_id'];
    $pdo = Database::getConnection();
    $pdo->prepare("DELETE FROM enemies WHERE id = ?")->execute([$enemyId]);
    echo json_encode(['success' => true, 'message' => 'Монстр удален']);
}

/**
 * Get all quests (admin)
 */
function adminGetQuests(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM quests ORDER BY id");
    echo json_encode($stmt->fetchAll());
}

/**
 * Save quest (admin)
 */
function adminSaveQuest(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $questId = (int)($_POST['quest_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? 'kill';
    $targetId = (int)$_POST['target_id'];
    $targetCount = (int)$_POST['target_count'];
    $expReward = (int)$_POST['exp_reward'];
    $goldReward = (int)$_POST['gold_reward'];
    $minLevel = (int)$_POST['min_level'];
    
    $pdo = Database::getConnection();
    
    if ($questId > 0) {
        $pdo->prepare("UPDATE quests SET name = ?, description = ?, type = ?, target_id = ?, target_count = ?, exp_reward = ?, gold_reward = ?, min_level = ? WHERE id = ?")
            ->execute([$name, $description, $type, $targetId, $targetCount, $expReward, $goldReward, $minLevel, $questId]);
        echo json_encode(['success' => true, 'message' => 'Квест обновлен']);
    } else {
        $pdo->prepare("INSERT INTO quests (name, description, type, target_id, target_count, exp_reward, gold_reward, min_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$name, $description, $type, $targetId, $targetCount, $expReward, $goldReward, $minLevel]);
        echo json_encode(['success' => true, 'message' => 'Квест создан']);
    }
}

/**
 * Delete quest (admin)
 */
function adminDeleteQuest(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $questId = (int)$_POST['quest_id'];
    $pdo = Database::getConnection();
    $pdo->prepare("DELETE FROM quests WHERE id = ?")->execute([$questId]);
    echo json_encode(['success' => true, 'message' => 'Квест удален']);
}

/**
 * Get all guilds (admin)
 */
function adminGetGuilds(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT g.*, u.username as leader_name FROM guilds g JOIN users u ON g.leader_id = u.id ORDER BY g.level DESC");
    echo json_encode($stmt->fetchAll());
}

/**
 * Delete guild (admin)
 */
function adminDeleteGuild(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $guildId = (int)$_POST['guild_id'];
    $pdo = Database::getConnection();
    $pdo->prepare("DELETE FROM guild_members WHERE guild_id = ?")->execute([$guildId]);
    $pdo->prepare("DELETE FROM guilds WHERE id = ?")->execute([$guildId]);
    echo json_encode(['success' => true, 'message' => 'Гильдия удалена']);
}

/**
 * Get all raids (admin)
 */
function adminGetRaids(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM raid_bosses ORDER BY id");
    echo json_encode($stmt->fetchAll());
}

/**
 * Save raid boss (admin)
 */
function adminSaveRaid(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $raidId = (int)($_POST['raid_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $level = (int)$_POST['level'];
    $hp = (int)$_POST['hp'];
    $atk = (int)$_POST['atk'];
    $exp = (int)$_POST['exp'];
    $gold = (int)$_POST['gold'];
    $playerLimit = (int)$_POST['player_limit'];
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    $pdo = Database::getConnection();
    
    if ($raidId > 0) {
        $pdo->prepare("UPDATE raid_bosses SET name = ?, level = ?, hp = ?, max_hp = ?, atk = ?, exp = ?, gold = ?, player_limit = ?, is_active = ? WHERE id = ?")
            ->execute([$name, $level, $hp, $hp, $atk, $exp, $gold, $playerLimit, $isActive, $raidId]);
        echo json_encode(['success' => true, 'message' => 'Рейд-босс обновлен']);
    } else {
        $pdo->prepare("INSERT INTO raid_bosses (name, level, hp, max_hp, atk, exp, gold, player_limit, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$name, $level, $hp, $hp, $atk, $exp, $gold, $playerLimit, $isActive]);
        echo json_encode(['success' => true, 'message' => 'Рейд-босс создан']);
    }
}

/**
 * Delete raid (admin)
 */
function adminDeleteRaid(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $raidId = (int)$_POST['raid_id'];
    $pdo = Database::getConnection();
    $pdo->prepare("DELETE FROM raid_bosses WHERE id = ?")->execute([$raidId]);
    echo json_encode(['success' => true, 'message' => 'Рейд-босс удален']);
}

/**
 * Get server stats (admin)
 */
function adminGetStats(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalGuilds = $pdo->query("SELECT COUNT(*) FROM guilds")->fetchColumn();
    $totalGold = $pdo->query("SELECT SUM(gold) FROM users")->fetchColumn() ?: 0;
    $totalPvpWins = $pdo->query("SELECT SUM(pvp_wins) FROM users")->fetchColumn() ?: 0;
    $totalQuests = $pdo->query("SELECT COUNT(*) FROM quests")->fetchColumn();
    $totalRaids = $pdo->query("SELECT COUNT(*) FROM raid_bosses")->fetchColumn();
    
    echo json_encode([
        'total_users' => $totalUsers,
        'total_guilds' => $totalGuilds,
        'total_gold' => $totalGold,
        'total_pvp_wins' => $totalPvpWins,
        'total_quests' => $totalQuests,
        'total_raids' => $totalRaids
    ]);
}

/**
 * Send announcement (admin)
 */
function adminSendAnnouncement(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $message = trim($_POST['message'] ?? '');
    if (empty($message)) {
        echo json_encode(['error' => 'Введите сообщение']);
        return;
    }
    
    sendSystemMessage($message, 'global');
    echo json_encode(['success' => true, 'message' => 'Объявление отправлено']);
}

/**
 * Give item to user (admin)
 */
function adminGiveItem(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $userId = (int)$_POST['user_id'];
    $itemId = (int)$_POST['item_id'];
    $quantity = (int)$_POST['quantity'];
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT id FROM inventory WHERE user_id = ? AND item_id = ?");
    $stmt->execute([$userId, $itemId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $pdo->prepare("UPDATE inventory SET quantity = quantity + ? WHERE user_id = ? AND item_id = ?")
            ->execute([$quantity, $userId, $itemId]);
    } else {
        $pdo->prepare("INSERT INTO inventory (user_id, item_id, quantity) VALUES (?, ?, ?)")
            ->execute([$userId, $itemId, $quantity]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Предмет выдан']);
}

/**
 * Teleport user (admin)
 */
function adminTeleportUser(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $userId = (int)$_POST['user_id'];
    $locationId = (int)$_POST['location_id'];
    
    $pdo = Database::getConnection();
    $pdo->prepare("UPDATE user_locations SET location_id = ? WHERE user_id = ?")
        ->execute([$locationId, $userId]);
    
    echo json_encode(['success' => true, 'message' => 'Пользователь телепортирован']);
}

/**
 * Ban user (admin)
 */
function adminBanUser(): void {
    if (!checkAdmin()) {
        echo json_encode(['error' => 'Access denied']);
        return;
    }
    
    $userId = (int)$_POST['user_id'];
    $pdo = Database::getConnection();
    $pdo->prepare("UPDATE users SET role = 'banned' WHERE id = ?")->execute([$userId]);
    echo json_encode(['success' => true, 'message' => 'Пользователь заблокирован']);
}
