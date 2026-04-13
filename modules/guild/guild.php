<?php
/**
 * Guild Module - Guilds, Wars, Members
 * Medieval Realm RPG
 */

if (!defined('GAME_MODULE')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Get all guilds
 */
function getGuilds(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT g.*, u.username as leader_name FROM guilds g JOIN users u ON g.leader_id = u.id ORDER BY g.level DESC, g.exp DESC");
    echo json_encode($stmt->fetchAll());
}

/**
 * Create new guild
 */
function createGuild(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($name)) {
        echo json_encode(['error' => 'Введите название гильдии']);
        return;
    }
    
    if (mb_strlen($name) > 50) {
        echo json_encode(['error' => 'Название слишком длинное (макс 50 символов)']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT id FROM guilds WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Гильдия с таким названием уже существует']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT guild_id FROM guild_members WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Вы уже состоите в гильдии']);
        return;
    }
    
    $cost = 1000;
    $stmt = $pdo->prepare("SELECT gold FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user['gold'] < $cost) {
        echo json_encode(['error' => 'Недостаточно золота (нужно ' . $cost . ')']);
        return;
    }
    
    $pdo->beginTransaction();
    
    $pdo->prepare("UPDATE users SET gold = gold - ? WHERE id = ?")->execute([$cost, $_SESSION['user_id']]);
    $pdo->prepare("INSERT INTO guilds (name, description, leader_id, level, exp, gold) VALUES (?, ?, ?, 1, 0, 0)")
        ->execute([$name, $description, $_SESSION['user_id']]);
    
    $guildId = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO guild_members (guild_id, user_id, role, joined_at) VALUES (?, ?, 'leader', NOW())")
        ->execute([$guildId, $_SESSION['user_id']]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'guild_id' => $guildId, 'message' => 'Гильдия создана']);
}

/**
 * Join guild
 */
function joinGuild(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $guildId = (int)$_POST['guild_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM guilds WHERE id = ?");
    $stmt->execute([$guildId]);
    $guild = $stmt->fetch();
    
    if (!$guild) {
        echo json_encode(['error' => 'Гильдия не найдена']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM guild_members WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Вы уже состоите в гильдии']);
        return;
    }
    
    $pdo->prepare("INSERT INTO guild_members (guild_id, user_id, role, joined_at) VALUES (?, ?, 'member', NOW())")
        ->execute([$guildId, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Вы вступили в гильдию']);
}

/**
 * Leave guild
 */
function leaveGuild(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM guild_members WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $membership = $stmt->fetch();
    
    if (!$membership) {
        echo json_encode(['error' => 'Вы не состоите в гильдии']);
        return;
    }
    
    if ($membership['role'] === 'leader') {
        $stmt = $pdo->prepare("SELECT user_id FROM guild_members WHERE guild_id = ? AND user_id != ? LIMIT 1");
        $stmt->execute([$membership['guild_id'], $_SESSION['user_id']]);
        $newLeader = $stmt->fetch();
        
        if ($newLeader) {
            $pdo->prepare("UPDATE guilds SET leader_id = ? WHERE id = ?")->execute([$newLeader['user_id'], $membership['guild_id']]);
            $pdo->prepare("UPDATE guild_members SET role = 'leader' WHERE guild_id = ? AND user_id = ?")
                ->execute([$membership['guild_id'], $newLeader['user_id']]);
        } else {
            $pdo->prepare("DELETE FROM guilds WHERE id = ?")->execute([$membership['guild_id']]);
        }
    }
    
    $pdo->prepare("DELETE FROM guild_members WHERE user_id = ?")->execute([$_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Вы покинули гильдию']);
}

/**
 * Donate to guild
 */
function donateToGuild(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $amount = (int)$_POST['amount'];
    if ($amount <= 0) {
        echo json_encode(['error' => 'Некорректная сумма']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT gm.guild_id FROM guild_members gm WHERE gm.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $membership = $stmt->fetch();
    
    if (!$membership) {
        echo json_encode(['error' => 'Вы не состоите в гильдии']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT gold FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user['gold'] < $amount) {
        echo json_encode(['error' => 'Недостаточно золота']);
        return;
    }
    
    $pdo->beginTransaction();
    
    $pdo->prepare("UPDATE users SET gold = gold - ? WHERE id = ?")->execute([$amount, $_SESSION['user_id']]);
    $pdo->prepare("UPDATE guilds SET gold = gold + ? WHERE id = ?")->execute([$amount, $membership['guild_id']]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Пожертвование внесено']);
}

/**
 * Get guild wars
 */
function getGuildWars(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT gw.*, g1.name as attacker_name, g2.name as defender_name FROM guild_wars gw JOIN guilds g1 ON gw.attacker_guild_id = g1.id JOIN guilds g2 ON gw.defender_guild_id = g2.id WHERE gw.status = 'active' ORDER BY gw.created_at DESC");
    echo json_encode($stmt->fetchAll());
}

/**
 * Get my guild war status
 */
function getMyGuildWarStatus(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT guild_id FROM guild_members WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $membership = $stmt->fetch();
    
    if (!$membership) {
        echo json_encode(['error' => 'Вы не в гильдии']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT gw.*, g1.name as attacker_name, g2.name as defender_name FROM guild_wars gw JOIN guilds g1 ON gw.attacker_guild_id = g1.id JOIN guilds g2 ON gw.defender_guild_id = g2.id WHERE (gw.attacker_guild_id = ? OR gw.defender_guild_id = ?) AND gw.status = 'active'");
    $stmt->execute([$membership['guild_id'], $membership['guild_id']]);
    echo json_encode($stmt->fetchAll());
}

/**
 * Declare guild war
 */
function declareGuildWar(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $targetGuildId = (int)$_POST['target_guild_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT guild_id FROM guild_members WHERE user_id = ? AND role = 'leader'");
    $stmt->execute([$_SESSION['user_id']]);
    $myGuild = $stmt->fetch();
    
    if (!$myGuild) {
        echo json_encode(['error' => 'Только лидер гильдии может объявлять войну']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM guilds WHERE id = ?");
    $stmt->execute([$targetGuildId]);
    $targetGuild = $stmt->fetch();
    
    if (!$targetGuild) {
        echo json_encode(['error' => 'Гильдия не найдена']);
        return;
    }
    
    if ($targetGuildId == $myGuild['guild_id']) {
        echo json_encode(['error' => 'Нельзя объявить войну своей гильдии']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM guild_wars WHERE ((attacker_guild_id = ? AND defender_guild_id = ?) OR (attacker_guild_id = ? AND defender_guild_id = ?)) AND status IN ('pending', 'active')");
    $stmt->execute([$myGuild['guild_id'], $targetGuildId, $targetGuildId, $myGuild['guild_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Война уже идет']);
        return;
    }
    
    $pdo->prepare("INSERT INTO guild_wars (attacker_guild_id, defender_guild_id, created_at, status) VALUES (?, ?, NOW(), 'pending')")
        ->execute([$myGuild['guild_id'], $targetGuildId]);
    
    echo json_encode(['success' => true, 'message' => 'Война объявлена']);
}

/**
 * Join guild war
 */
function joinGuildWar(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $warId = (int)$_POST['war_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT guild_id FROM guild_members WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $membership = $stmt->fetch();
    
    if (!$membership) {
        echo json_encode(['error' => 'Вы не в гильдии']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM guild_wars WHERE id = ? AND status = 'active' AND (attacker_guild_id = ? OR defender_guild_id = ?)");
    $stmt->execute([$warId, $membership['guild_id'], $membership['guild_id']]);
    $war = $stmt->fetch();
    
    if (!$war) {
        echo json_encode(['error' => 'Война не найдена']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM war_participants WHERE war_id = ? AND user_id = ?");
    $stmt->execute([$warId, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Вы уже участвуете']);
        return;
    }
    
    $pdo->prepare("INSERT INTO war_participants (war_id, user_id, joined_at) VALUES (?, ?, NOW())")
        ->execute([$warId, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Вы присоединились к войне']);
}

/**
 * Attack guild war enemy
 */
function attackGuildWarEnemy(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $enemyId = (int)$_POST['enemy_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT guild_id FROM guild_members WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $myMembership = $stmt->fetch();
    
    if (!$myMembership) {
        echo json_encode(['error' => 'Вы не в гильдии']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT guild_id FROM guild_members WHERE user_id = ?");
    $stmt->execute([$enemyId]);
    $enemyGuild = $stmt->fetch();
    
    if (!$enemyGuild) {
        echo json_encode(['error' => 'Противник не найден']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM guild_wars WHERE status = 'active' AND ((attacker_guild_id = ? AND defender_guild_id = ?) OR (attacker_guild_id = ? AND defender_guild_id = ?))");
    $stmt->execute([$myMembership['guild_id'], $enemyGuild['guild_id'], $enemyGuild['guild_id'], $myMembership['guild_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['error' => 'Нет активной войны между гильдиями']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$enemyId]);
    $enemy = $stmt->fetch();
    
    if (!$enemy) {
        echo json_encode(['error' => 'Противник не найден']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $player = $stmt->fetch();
    
    $damage = max(1, $player['atk'] - $enemy['def'] * 0.5);
    $score = floor($damage / 10);
    
    // Insert into war_history instead of non-existent guild_war_attacks
    $stmt = $pdo->prepare("SELECT id FROM guild_wars WHERE status = 'active' AND ((attacker_guild_id = ? AND defender_guild_id = ?) OR (attacker_guild_id = ? AND defender_guild_id = ?))");
    $stmt->execute([$myMembership['guild_id'], $enemyGuild['guild_id'], $enemyGuild['guild_id'], $myMembership['guild_id']]);
    $war = $stmt->fetch();
    
    if ($war) {
        $pdo->prepare("INSERT INTO war_history (war_id, event_type, description, timestamp) VALUES (?, 'attack', CONCAT(?, ' нанес ', ?, ' урона'), NOW())")
            ->execute([$war['id'], $_SESSION['user_id'], $damage]);
        
        // Update participant stats
        $pdo->prepare("UPDATE war_participants SET damage_dealt = damage_dealt + ? WHERE war_id = ? AND user_id = ?")
            ->execute([$damage, $war['id'], $_SESSION['user_id']]);
    }
    
    echo json_encode(['success' => true, 'damage' => $damage, 'score' => $score]);
}

/**
 * Get guild war leaderboard
 */
function getGuildWarLeaderboard(): void {
    $warId = (int)($_GET['war_id'] ?? 0);
    if (!$warId) {
        echo json_encode(['error' => 'War ID required']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT u.username, COALESCE(SUM(wp.damage_dealt), 0) as total_score, COUNT(wp.id) as attacks FROM war_participants wp JOIN users u ON wp.user_id = u.id WHERE wp.war_id = ? GROUP BY u.id ORDER BY total_score DESC LIMIT 20");
    $stmt->execute([$warId]);
    echo json_encode($stmt->fetchAll());
}
