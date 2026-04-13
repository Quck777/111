<?php
/**
 * Medieval Realm RPG - API
 * Версия: 3.0.0 (Глобальный релиз)
 * Дата: 2026-04-13
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';

try {
    Database::getConnection();
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Ошибка подключения к БД: ' . $e->getMessage()]);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (empty($action)) {
    echo json_encode(['error' => 'No action specified']);
    exit;
}

$allowedActions = [
    'register', 'login', 'logout', 'getUser',
    'getEnemies', 'startPveBattle', 'attack', 'usePotion',
    'getInventory', 'getItems', 'buyItem', 'equipItem', 'unequipItem', 'useItem',
    'getLeaderboard', 'getPvpLeaderboard', 'getOpponentsForArena',
    'getChat', 'sendChat', 'getOnlineUsers',
    'startPvpBattle', 'pvpAttack',
    'getLocations', 'getLicenses', 'buyLicense',
    'getSkills', 'getQuests', 'claimDailyReward',
    'getAchievements', 'getRaidBosses', 'getHistory',
    'getRecipes', 'getGuilds', 'createGuild', 'joinGuild', 'leaveGuild', 'donateToGuild',
    'getMarketListings', 'createMarketListing', 'buyFromMarket', 'cancelMarketListing', 'getMyMarketListings',
    'getGuildWars', 'getMyGuildWarStatus', 'declareGuildWar', 'joinGuildWar', 'attackGuildWarEnemy', 'getGuildWarLeaderboard',
    // Admin actions
    'adminLogin', 'adminGetUsers', 'adminUpdateUser', 'adminDeleteUser', 'adminResetPassword',
    'adminGetItems', 'adminSaveItem', 'adminDeleteItem',
    'adminGetEnemies', 'adminSaveEnemy', 'adminDeleteEnemy',
    'adminGetQuests', 'adminSaveQuest', 'adminDeleteQuest',
    'adminGetGuilds', 'adminDeleteGuild',
    'adminGetRaids', 'adminSaveRaid', 'adminDeleteRaid', 'adminGetStats',
    'adminSendAnnouncement', 'adminGiveItem', 'adminTeleportUser', 'adminBanUser'
];

if (!in_array($action, $allowedActions)) {
    echo json_encode(['error' => 'Unknown action: ' . $action]);
    exit;
}

try {
    switch ($action) {
        case 'register': register(); break;
        case 'login': login(); break;
        case 'logout': logout(); break;
        case 'getUser': getUser(); break;
        case 'getEnemies': getEnemies(); break;
        case 'startPveBattle': startPveBattle(); break;
        case 'attack': attack(); break;
        case 'usePotion': usePotion(); break;
        case 'getInventory': getInventory(); break;
        case 'getItems': getItems(); break;
        case 'buyItem': buyItem(); break;
        case 'equipItem': equipItem(); break;
        case 'unequipItem': unequipItem(); break;
        case 'useItem': useItem(); break;
        case 'getLeaderboard': getLeaderboard(); break;
        case 'getPvpLeaderboard': getPvpLeaderboard(); break;
        case 'getOpponentsForArena': getOpponentsForArena(); break;
        case 'getChat': getChat(); break;
        case 'sendChat': sendChat(); break;
        case 'getOnlineUsers': getOnlineUsers(); break;
        case 'startPvpBattle': startPvpBattleApi(); break;
        case 'pvpAttack': pvpAttack(); break;
        case 'getLocations': getLocations(); break;
        case 'getLicenses': getLicenses(); break;
        case 'buyLicense': buyLicense(); break;
        case 'getSkills': getSkills(); break;
        case 'getQuests': getQuests(); break;
        case 'claimDailyReward': claimDailyReward(); break;
        case 'getAchievements': getAchievements(); break;
        case 'getRaidBosses': getRaidBosses(); break;
        case 'getHistory': getHistory(); break;
        case 'getRecipes': getRecipes(); break;
        case 'getGuilds': getGuilds(); break;
        case 'createGuild': createGuild(); break;
        case 'joinGuild': joinGuild(); break;
        case 'leaveGuild': leaveGuild(); break;
        case 'donateToGuild': donateToGuild(); break;
        case 'getMarketListings': getMarketListings(); break;
        case 'createMarketListing': createMarketListing(); break;
        case 'buyFromMarket': buyFromMarket(); break;
        case 'cancelMarketListing': cancelMarketListing(); break;
        case 'getMyMarketListings': getMyMarketListings(); break;
        case 'getGuildWars': getGuildWars(); break;
        case 'getMyGuildWarStatus': getMyGuildWarStatus(); break;
        case 'declareGuildWar': declareGuildWar(); break;
        case 'joinGuildWar': joinGuildWar(); break;
        case 'attackGuildWarEnemy': attackGuildWarEnemy(); break;
        case 'getGuildWarLeaderboard': getGuildWarLeaderboard(); break;
        case 'adminLogin': adminLogin(); break;
        case 'adminGetUsers': adminGetUsers(); break;
        case 'adminUpdateUser': adminUpdateUser(); break;
        case 'adminDeleteUser': adminDeleteUser(); break;
        case 'adminResetPassword': adminResetPassword(); break;
        case 'adminGetItems': adminGetItems(); break;
        case 'adminSaveItem': adminSaveItem(); break;
        case 'adminDeleteItem': adminDeleteItem(); break;
        case 'adminGetEnemies': adminGetEnemies(); break;
        case 'adminSaveEnemy': adminSaveEnemy(); break;
        case 'adminDeleteEnemy': adminDeleteEnemy(); break;
        case 'adminGetQuests': adminGetQuests(); break;
        case 'adminSaveQuest': adminSaveQuest(); break;
        case 'adminDeleteQuest': adminDeleteQuest(); break;
        case 'adminGetGuilds': adminGetGuilds(); break;
        case 'adminDeleteGuild': adminDeleteGuild(); break;
        case 'adminGetRaids': adminGetRaids(); break;
        case 'adminSaveRaid': adminSaveRaid(); break;
        case 'adminDeleteRaid': adminDeleteRaid(); break;
        case 'adminGetStats': adminGetStats(); break;
        case 'adminSendAnnouncement': adminSendAnnouncement(); break;
        case 'adminGiveItem': adminGiveItem(); break;
        case 'adminTeleportUser': adminTeleportUser(); break;
        case 'adminBanUser': adminBanUser(); break;
        default: echo json_encode(['error' => 'Unknown action']);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

function register(): void {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $class = $_POST['class'] ?? 'warrior';
    $race = $_POST['race'] ?? 'human';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Заполните все поля']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Имя занято']);
        return;
    }
    
    $classStats = match($class) {
        'warrior' => ['hp' => 120, 'mp' => 40, 'atk' => 12, 'def' => 8],
        'mage' => ['hp' => 80, 'mp' => 100, 'atk' => 8, 'def' => 4],
        'archer' => ['hp' => 90, 'mp' => 60, 'atk' => 14, 'def' => 5],
        'rogue' => ['hp' => 85, 'mp' => 50, 'atk' => 15, 'def' => 4],
        'paladin' => ['hp' => 110, 'mp' => 70, 'atk' => 10, 'def' => 10],
        default => ['hp' => 100, 'mp' => 50, 'atk' => 10, 'def' => 5],
    };
    
    $hp = $classStats['hp'];
    $mp = $classStats['mp'];
    $atk = $classStats['atk'];
    $def = $classStats['def'];
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, race, class, level, hp, max_hp, mp, max_mp, atk, def) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $hash, $race, $class, 1, $hp, $hp, $mp, $mp, $atk, $def]);
    
    $userId = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO user_locations (user_id, location_id) VALUES (?, 1)")->execute([$userId]);
    $pdo->prepare("INSERT INTO user_battle_licenses (user_id, license_id, attacks_remaining, expires_at) VALUES (?, 1, 20, DATE_ADD(NOW(), INTERVAL 24 HOUR))")->execute([$userId]);
    
    echo json_encode(['success' => true]);
}

function login(): void {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'unknown';
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'error' => 'Неверный логин или пароль']);
        return;
    }
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['login_ip'] = $ip;
    $_SESSION['login_time'] = time();
    
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW(), last_ip = ? WHERE id = ?");
    $stmt->execute([$ip, $user['id']]);
    
    unset($user['password']);
    echo json_encode(['success' => true, 'user' => $user, 'ip' => $ip]);
}

function logout(): void {
    session_destroy();
    echo json_encode(['success' => true]);
}

function getUser(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    unset($user['password']);
    echo json_encode($user);
}

function getEnemies(): void {
    $level = (int)($_POST['level'] ?? 1);
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM enemies WHERE level <= ? ORDER BY level");
    $stmt->execute([$level + 5]);
    echo json_encode($stmt->fetchAll());
}

function startPveBattle(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $enemyId = (int)$_POST['enemy_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM enemies WHERE id = ?");
    $stmt->execute([$enemyId]);
    $enemy = $stmt->fetch();
    
    if (!$enemy) {
        echo json_encode(['error' => 'Enemy not found']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT hp, max_hp, mp, max_mp, atk, def FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    $battleId = rand(1000, 9999);
    echo json_encode([
        'battle_id' => $battleId,
        'enemy' => $enemy,
        'user_hp' => $user['hp'],
        'enemy_hp' => $enemy['hp']
    ]);
}

function attack(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $attackType = $_POST['attack_type'] ?? 'normal';
    $damage = match($attackType) {
        'heavy' => rand(15, 25),
        'quick' => rand(8, 15),
        'precise' => rand(10, 20),
        default => rand(5, 12)
    };
    
    $exp = rand(10, 30);
    $gold = rand(5, 20);
    
    $pdo = Database::getConnection();
    $pdo->prepare("UPDATE users SET exp = exp + ?, gold = gold + ? WHERE id = ?")->execute([$exp, $gold, $_SESSION['user_id']]);
    
    $win = rand(1, 100) > 30;
    if ($win) {
        $pdo->prepare("UPDATE users SET battles_won = battles_won + 1 WHERE id = ?")->execute([$_SESSION['user_id']]);
    }
    
    echo json_encode([
        'won' => $win,
        'exp' => $exp,
        'gold' => $gold,
        'damage' => $damage,
        'player_hp' => rand(50, 100),
        'enemy_hp' => rand(0, 30)
    ]);
}

function usePotion(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $itemId = (int)$_POST['item_id'];
    $battleId = (int)$_POST['battle_id'];
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();
    
    if (!$item || $item['type'] !== 'potion') {
        echo json_encode(['error' => 'Invalid item']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT hp, max_hp, mp, max_mp FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    $healed = min($item['hp_bonus'] ?? 0, $user['max_hp'] - $user['hp']);
    $pdo->prepare("UPDATE users SET hp = LEAST(max_hp, hp + ?) WHERE id = ?")->execute([$healed, $_SESSION['user_id']]);
    
    $pdo->prepare("DELETE FROM inventory WHERE user_id = ? AND item_id = ? AND quantity > 0 LIMIT 1")->execute([$_SESSION['user_id'], $itemId]);
    
    echo json_encode(['success' => true, 'healed' => $healed, 'new_hp' => $user['hp'] + $healed]);
}

function getInventory(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT i.id, i.item_id, i.quantity, i.equipped, i.durability, i.max_durability, i.expires_at,
        it.name, it.type, it.rarity, it.description, 
        it.atk_bonus, it.def_bonus, it.hp_bonus, it.mp_bonus
        FROM inventory i 
        JOIN items it ON i.item_id = it.id 
        WHERE i.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode($stmt->fetchAll());
}

function getItems(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM items ORDER BY required_level");
    echo json_encode($stmt->fetchAll());
}

function buyItem(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $itemId = (int)$_POST['item_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT gold FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $gold = $stmt->fetch()['gold'];
    
    if ($gold < $item['value']) {
        echo json_encode(['error' => 'Недостаточно золота']);
        return;
    }
    
    $pdo->prepare("UPDATE users SET gold = gold - ? WHERE id = ?")->execute([$item['value'], $_SESSION['user_id']]);
    $pdo->prepare("INSERT INTO inventory (user_id, item_id, quantity) VALUES (?, ?, 1)")->execute([$_SESSION['user_id'], $itemId]);
    
    echo json_encode(['success' => true]);
}

function equipItem(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $itemId = (int)$_POST['item_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT i.*, it.atk_bonus, it.def_bonus, it.hp_bonus, it.mp_bonus 
        FROM inventory i 
        JOIN items it ON i.item_id = it.id 
        WHERE i.id = ? AND i.user_id = ?");
    $stmt->execute([$itemId, $_SESSION['user_id']]);
    $invItem = $stmt->fetch();
    
    if (!$invItem) {
        echo json_encode(['error' => 'Item not found']);
        return;
    }
    
    $pdo->prepare("UPDATE inventory SET equipped = 1 WHERE user_id = ? AND id = ?")->execute([$_SESSION['user_id'], $itemId]);
    
    $bonusAtk = $invItem['atk_bonus'] ?? 0;
    $bonusDef = $invItem['def_bonus'] ?? 0;
    $bonusHp = $invItem['hp_bonus'] ?? 0;
    $bonusMp = $invItem['mp_bonus'] ?? 0;
    
    if ($bonusAtk > 0 || $bonusDef > 0 || $bonusHp > 0 || $bonusMp > 0) {
        $pdo->prepare("UPDATE users SET atk = atk + ?, def = def + ?, max_hp = max_hp + ?, max_mp = max_mp + ? WHERE id = ?")
            ->execute([$bonusAtk, $bonusDef, $bonusHp, $bonusMp, $_SESSION['user_id']]);
    }
    
    echo json_encode(['success' => true]);
}

function unequipItem(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $itemId = (int)$_POST['item_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT i.*, it.atk_bonus, it.def_bonus, it.hp_bonus, it.mp_bonus 
        FROM inventory i 
        JOIN items it ON i.item_id = it.id 
        WHERE i.id = ? AND i.user_id = ?");
    $stmt->execute([$itemId, $_SESSION['user_id']]);
    $invItem = $stmt->fetch();
    
    if (!$invItem) {
        echo json_encode(['error' => 'Item not found']);
        return;
    }
    
    $pdo->prepare("UPDATE inventory SET equipped = 0 WHERE user_id = ? AND id = ?")->execute([$_SESSION['user_id'], $itemId]);
    
    $bonusAtk = $invItem['atk_bonus'] ?? 0;
    $bonusDef = $invItem['def_bonus'] ?? 0;
    $bonusHp = $invItem['hp_bonus'] ?? 0;
    $bonusMp = $invItem['mp_bonus'] ?? 0;
    
    if ($bonusAtk > 0 || $bonusDef > 0 || $bonusHp > 0 || $bonusMp > 0) {
        $pdo->prepare("UPDATE users SET atk = GREATEST(1, atk - ?), def = GREATEST(1, def - ?), max_hp = GREATEST(10, max_hp - ?), max_mp = GREATEST(10, max_mp - ?) WHERE id = ?")
            ->execute([$bonusAtk, $bonusDef, $bonusHp, $bonusMp, $_SESSION['user_id']]);
    }
    
    echo json_encode(['success' => true]);
}

function useItem(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $itemId = (int)$_POST['item_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT i.*, it.* 
        FROM inventory i 
        JOIN items it ON i.item_id = it.id 
        WHERE i.id = ? AND i.user_id = ?");
    $stmt->execute([$itemId, $_SESSION['user_id']]);
    $invItem = $stmt->fetch();
    
    if (!$invItem) {
        echo json_encode(['error' => 'Item not found']);
        return;
    }
    
    $type = $invItem['type'];
    $message = 'Предмет использован';
    
    if ($type === 'potion') {
        $hpBonus = $invItem['hp_bonus'] ?? 0;
        $mpBonus = $invItem['mp_bonus'] ?? 0;
        
        if ($hpBonus > 0) {
            $pdo->prepare("UPDATE users SET hp = LEAST(max_hp, hp + ?) WHERE id = ?")->execute([$hpBonus, $_SESSION['user_id']]);
        }
        if ($mpBonus > 0) {
            $pdo->prepare("UPDATE users SET mp = LEAST(max_mp, mp + ?) WHERE id = ?")->execute([$mpBonus, $_SESSION['user_id']]);
        }
        $message = 'Восстановлено ' . ($hpBonus ? $hpBonus . ' HP ' : '') . ($mpBonus ? $mpBonus . ' MP' : '');
    } elseif ($type === 'food') {
        $hpBonus = $invItem['hp_bonus'] ?? 10;
        $pdo->prepare("UPDATE users SET hp = LEAST(max_hp, hp + ?) WHERE id = ?")->execute([$hpBonus, $_SESSION['user_id']]);
        $message = 'Восстановлено ' . $hpBonus . ' HP';
    } elseif ($type === 'scroll') {
        $message = 'Свиток использован';
    }
    
    // Decrease quantity or delete
    if ($invItem['quantity'] > 1) {
        $pdo->prepare("UPDATE inventory SET quantity = quantity - 1 WHERE user_id = ? AND id = ?")->execute([$_SESSION['user_id'], $itemId]);
    } else {
        $pdo->prepare("DELETE FROM inventory WHERE user_id = ? AND id = ?")->execute([$_SESSION['user_id'], $itemId]);
    }
    
    echo json_encode(['success' => true, 'message' => $message]);
}

function getLeaderboard(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT id, username, level, class, gold FROM users ORDER BY level DESC LIMIT 20");
    echo json_encode($stmt->fetchAll());
}

function getPvpLeaderboard(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT id, username, level, class, pvp_rating, pvp_wins, pvp_losses FROM users WHERE id != " . (int)($_SESSION['user_id'] ?? 0) . " ORDER BY pvp_rating DESC LIMIT 20");
    echo json_encode($stmt->fetchAll());
}

function getOpponentsForArena(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT id, username, level, class, pvp_rating, pvp_wins, pvp_losses FROM users WHERE id != " . (int)($_SESSION['user_id'] ?? 0) . " ORDER BY pvp_rating DESC LIMIT 15");
    echo json_encode($stmt->fetchAll());
}

function getChat(): void {
    $allowedChannels = ['global', 'trade', 'help'];
    $channel = $_GET['channel'] ?? 'global';
    if (!in_array($channel, $allowedChannels)) $channel = 'global';
    $since = (int)($_GET['since'] ?? 0);
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT c.id, c.message, c.timestamp, c.channel, c.message_type,
        u.id as user_id, u.username, u.level, u.class, u.race,
        (SELECT guild_id FROM guild_members WHERE user_id = u.id LIMIT 1) as guild_id
        FROM chat_messages c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.channel = ?
        " . ($since > 0 ? "AND c.id > ?" : "") . "
        ORDER BY c.timestamp DESC LIMIT 100");
    
    if ($since > 0) {
        $stmt->execute([$channel, $since]);
    } else {
        $stmt->execute([$channel]);
    }
    
    $messages = array_reverse($stmt->fetchAll());
    echo json_encode($messages);
}

function sendChat(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $message = trim($_POST['message'] ?? '');
    $allowedChannels = ['global', 'trade', 'help'];
    $channel = $_POST['channel'] ?? 'global';
    if (!in_array($channel, $allowedChannels)) $channel = 'global';
    
    if (empty($message)) {
        echo json_encode(['error' => 'Пустое сообщение']);
        return;
    }
    
    if (mb_strlen($message) > 500) {
        echo json_encode(['error' => 'Слишком длинное сообщение (макс 500 символов)']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT last_message_time, message_count FROM chat_spam WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $spam = $stmt->fetch();
    
    $now = time();
    if ($spam && ($now - $spam['last_message_time']) < 2 && $spam['message_count'] >= 3) {
        echo json_encode(['error' => 'Подождите, не спамьте!']);
        return;
    }
    
    $filtered = filterMessage($message);
    if (!$filtered) {
        echo json_encode(['error' => 'Запрещённое слово']);
        return;
    }
    
    $stmt = $pdo->prepare("INSERT INTO chat_messages (user_id, message, channel, message_type) VALUES (?, ?, ?, 'user')");
    $stmt->execute([$_SESSION['user_id'], $filtered, $channel]);
    
    if ($spam) {
        $pdo->prepare("UPDATE chat_spam SET last_message_time = ?, message_count = LEAST(message_count + 1, 10) WHERE user_id = ?")
            ->execute([$now, $_SESSION['user_id']]);
    } else {
        $pdo->prepare("INSERT INTO chat_spam (user_id, last_message_time, message_count) VALUES (?, ?, 1)")
            ->execute([$_SESSION['user_id'], $now]);
    }
    
    echo json_encode(['success' => true, 'message_id' => $pdo->lastInsertId()]);
}

function filterMessage($message) {
    $badWords = ['хуй', 'пизда', 'еба', 'бля', 'нахуй', 'ебану', 'мудак', 'идиот', '-Dame', 'vagina', 'penis', 'fuck', 'shit'];
    $filtered = mb_strtolower($message);
    foreach ($badWords as $word) {
        if (strpos($filtered, $word) !== false) {
            return false;
        }
    }
    return $message;
}

function getOnlineUsers(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT id, username, level, class FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    $users = $stmt->fetchAll();
    echo json_encode(['success' => true, 'count' => count($users), 'users' => $users]);
}

function sendSystemMessage($message, $channel = 'global'): void {
    $pdo = Database::getConnection();
    $pdo->prepare("INSERT INTO chat_messages (user_id, message, channel, message_type) VALUES (1, ?, ?, 'system')")
        ->execute([$message, $channel]);
}

function startPvpBattleApi(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $opponentId = (int)$_POST['opponent_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT id, username, level, class, hp, max_hp, mp, max_mp, atk, def FROM users WHERE id = ?");
    $stmt->execute([$opponentId]);
    $opponent = $stmt->fetch();
    
    if (!$opponent) {
        echo json_encode(['error' => 'Противник не найден']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT hp, max_hp, mp, max_mp, atk, def FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $player = $stmt->fetch();
    
    $stmt = $pdo->prepare("INSERT INTO pvp_battles (player1_id, player2_id, player1_hp, player2_hp, player1_mp, player2_mp, status, turn_player_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $opponentId, $player['hp'], $opponent['hp'], $player['mp'], $opponent['mp'], 'active', $_SESSION['user_id']]);
    
    echo json_encode([
        'battle_id' => $pdo->lastInsertId(),
        'opponent' => $opponent,
        'player_hp' => $player['hp'],
        'player_mp' => $player['mp'],
        'opponent_hp' => $opponent['hp'],
        'opponent_mp' => $opponent['mp']
    ]);
}

function pvpAttack(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $battleId = (int)$_POST['battle_id'];
    $attackType = $_POST['attack_type'] ?? 'normal';
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM pvp_battles WHERE id = ? AND (player1_id = ? OR player2_id = ?) AND status = 'active'");
    $stmt->execute([$battleId, $_SESSION['user_id'], $_SESSION['user_id']]);
    $battle = $stmt->fetch();
    
    if (!$battle) {
        echo json_encode(['error' => 'Бой не найден']);
        return;
    }
    
    $isPlayer1 = $battle['player1_id'] == $_SESSION['user_id'];
    $damage = match($attackType) {
        'heavy' => rand(15, 30),
        'quick' => rand(8, 18),
        'precise' => rand(12, 22),
        default => rand(5, 15)
    };
    
    if ($isPlayer1) {
        $newHp = $battle['player2_hp'] - $damage;
        $pdo->prepare("UPDATE pvp_battles SET player2_hp = ?, turn_player_id = ? WHERE id = ?")->execute([$newHp, $battle['player2_id'], $battleId]);
    } else {
        $newHp = $battle['player1_hp'] - $damage;
        $pdo->prepare("UPDATE pvp_battles SET player1_hp = ?, turn_player_id = ? WHERE id = ?")->execute([$newHp, $battle['player1_id'], $battleId]);
    }
    
    if ($newHp <= 0) {
        $winnerId = $_SESSION['user_id'];
        $loserId = $isPlayer1 ? $battle['player2_id'] : $battle['player1_id'];
        $ratingChange = 25;
        
        $pdo->prepare("UPDATE pvp_battles SET status = 'finished' WHERE id = ?")->execute([$battleId]);
        $pdo->prepare("UPDATE users SET pvp_wins = pvp_wins + 1, pvp_rating = pvp_rating + ? WHERE id = ?")->execute([$ratingChange, $winnerId]);
        $pdo->prepare("UPDATE users SET pvp_losses = pvp_losses + 1, pvp_rating = GREATEST(100, pvp_rating - ?) WHERE id = ?")->execute([$ratingChange, $loserId]);
        
        echo json_encode(['won' => true, 'rating_change' => $ratingChange]);
    } else {
        $enemyHp = $isPlayer1 ? $newHp : $battle['player2_hp'];
        $myHp = $isPlayer1 ? $battle['player1_hp'] : $newHp;
        echo json_encode(['player_dmg' => $damage, 'my_hp' => $myHp, 'opponent_hp' => $enemyHp]);
    }
}

function getLocations(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM locations ORDER BY min_level");
    echo json_encode($stmt->fetchAll());
}

function getLicenses(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM battle_licenses ORDER BY required_level");
    echo json_encode($stmt->fetchAll());
}

function buyLicense(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $licenseId = (int)$_POST['license_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM battle_licenses WHERE id = ?");
    $stmt->execute([$licenseId]);
    $license = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT gold, crystals FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    $cost = $license['price_gold'];
    if ($user['gold'] < $cost) {
        echo json_encode(['error' => 'Недостаточно золота']);
        return;
    }
    
    $pdo->prepare("UPDATE users SET gold = gold - ? WHERE id = ?")->execute([$cost, $_SESSION['user_id']]);
    $expires = date('Y-m-d H:i:s', time() + $license['duration_hours'] * 3600);
    $pdo->prepare("INSERT INTO user_battle_licenses (user_id, license_id, attacks_remaining, expires_at) VALUES (?, ?, ?, ?)")
        ->execute([$_SESSION['user_id'], $licenseId, $license['attacks_allowed'], $expires]);
    
    echo json_encode(['success' => true]);
}

function getSkills(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT u.class FROM users u WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT * FROM skills WHERE class = ?");
    $stmt->execute([$user['class']]);
    echo json_encode($stmt->fetchAll());
}

function getQuests(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM quests ORDER BY required_level");
    echo json_encode($stmt->fetchAll());
}

function claimDailyReward(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM daily_rewards WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $reward = $stmt->fetch();
    
    $now = new DateTime();
    $lastClaim = $reward ? new DateTime($reward['last_claim']) : null;
    $streak = $reward ? $reward['streak'] : 0;
    
    if ($lastClaim && $lastClaim->format('Y-m-d') === $now->format('Y-m-d')) {
        echo json_encode(['error' => 'Уже получено сегодня']);
        return;
    }
    
    $gold = 50 + $streak * 10;
    $exp = 25 + $streak * 5;
    
    if ($reward) {
        $pdo->prepare("UPDATE daily_rewards SET last_claim = NOW(), streak = streak + 1 WHERE user_id = ?")
            ->execute([$_SESSION['user_id']]);
    } else {
        $pdo->prepare("INSERT INTO daily_rewards (user_id, last_claim, streak) VALUES (?, NOW(), 1)")
            ->execute([$_SESSION['user_id']]);
    }
    
    $pdo->prepare("UPDATE users SET gold = gold + ?, exp = exp + ? WHERE id = ?")
        ->execute([$gold, $exp, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'gold' => $gold, 'exp' => $exp, 'streak' => $streak + 1]);
}

function getAchievements(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM achievements");
    echo json_encode($stmt->fetchAll());
}

function getRaidBosses(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM raid_bosses WHERE is_active = 1");
    echo json_encode($stmt->fetchAll());
}

function getHistory(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM battles_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode($stmt->fetchAll());
}

function getRecipes(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM recipes ORDER BY required_level");
    echo json_encode($stmt->fetchAll());
}

function getGuilds(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM guilds ORDER BY level DESC, exp DESC LIMIT 20");
    echo json_encode($stmt->fetchAll());
}

function createGuild(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (strlen($name) < 3 || strlen($name) > 30) {
        echo json_encode(['error' => 'Название 3-30 символов']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT id FROM guilds WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Имя занято']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT gold FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $gold = $stmt->fetch()['gold'];
    
    $cost = 500;
    if ($gold < $cost) {
        echo json_encode(['error' => 'Нужно ' . $cost . ' золота']);
        return;
    }
    
    $pdo->prepare("UPDATE users SET gold = gold - ? WHERE id = ?")->execute([$cost, $_SESSION['user_id']]);
    $pdo->prepare("INSERT INTO guilds (name, description, leader_id, gold) VALUES (?, ?, ?, ?)")
        ->execute([$name, $description, $_SESSION['user_id'], 0]);
    
    $guildId = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO guild_members (guild_id, user_id, role) VALUES (?, ?, 'leader')")
        ->execute([$guildId, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'guild_id' => $guildId]);
}

function joinGuild(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $guildId = (int)$_POST['guild_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT id FROM guild_members WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Вы уже в гильдии']);
        return;
    }
    
    $pdo->prepare("INSERT INTO guild_members (guild_id, user_id, role) VALUES (?, ?, 'member')")
        ->execute([$guildId, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true]);
}

function leaveGuild(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT gm.guild_id, g.leader_id FROM guild_members gm JOIN guilds g ON gm.guild_id = g.id WHERE gm.user_id = ? AND gm.role = 'leader'");
    $stmt->execute([$_SESSION['user_id']]);
    $guild = $stmt->fetch();
    
    if ($guild) {
        $pdo->prepare("DELETE FROM guilds WHERE id = ?")->execute([$guild['guild_id']]);
    } else {
        $pdo->prepare("DELETE FROM guild_members WHERE user_id = ?")->execute([$_SESSION['user_id']]);
    }
    
    echo json_encode(['success' => true]);
}

function donateToGuild(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $amount = (int)$_POST['amount'];
    if ($amount < 10 || $amount > 10000) {
        echo json_encode(['error' => 'Сумма 10-10000']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT gold FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $gold = $stmt->fetch()['gold'];
    
    if ($gold < $amount) {
        echo json_encode(['error' => 'Недостаточно золота']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT gm.guild_id FROM guild_members gm WHERE gm.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $member = $stmt->fetch();
    
    if (!$member) {
        echo json_encode(['error' => 'Вы не в гильдии']);
        return;
    }
    
    $pdo->prepare("UPDATE users SET gold = gold - ? WHERE id = ?")->execute([$amount, $_SESSION['user_id']]);
    $pdo->prepare("UPDATE guilds SET gold = gold + ?, exp = exp + ? WHERE id = ?")
        ->execute([$amount, $amount, $member['guild_id']]);
    
    echo json_encode(['success' => true, 'exp_gained' => $amount]);
}

// ================================
// РЫНОК
// ================================

function getMarketListings(): void {
    $type = $_POST['type'] ?? 'all';
    $rarity = $_POST['rarity'] ?? 'all';
    $search = $_POST['search'] ?? '';
    $page = (int)($_POST['page'] ?? 1);
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    $pdo = Database::getConnection();
    
    $sql = "SELECT m.*, u.username as seller_name 
            FROM market_listings m 
            JOIN users u ON m.seller_id = u.id 
            WHERE m.is_active = 1";
    $params = [];
    
    if ($type !== 'all') {
        $sql .= " AND m.item_type = ?";
        $params[] = $type;
    }
    if ($rarity !== 'all') {
        $sql .= " AND m.item_rarity = ?";
        $params[] = $rarity;
    }
    if ($search) {
        $sql .= " AND (m.item_name LIKE ? OR m.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $countSql = str_replace("SELECT m.*, u.username as seller_name", "SELECT COUNT(*)", $sql);
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();
    
    $sql .= " ORDER BY m.created_at DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $listings = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'listings' => $listings,
        'total' => $total,
        'pages' => ceil($total / $limit)
    ]);
}

function createMarketListing(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $itemId = (int)$_POST['item_id'];
    $priceGold = (int)$_POST['price_gold'];
    $priceCrystals = (int)($_POST['price_crystals'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $description = trim($_POST['description'] ?? '');
    
    if ($priceGold < 1 && $priceCrystals < 1) {
        echo json_encode(['error' => 'Укажите цену']);
        return;
    }
    if ($quantity < 1) $quantity = 1;
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT i.*, inv.quantity as inv_qty FROM items i 
                           JOIN inventory inv ON inv.item_id = i.id 
                           WHERE i.id = ? AND inv.user_id = ? AND inv.equipped = 0");
    $stmt->execute([$itemId, $_SESSION['user_id']]);
    $item = $stmt->fetch();
    
    if (!$item) {
        echo json_encode(['error' => 'Предмет не найден в инвентаре']);
        return;
    }
    
    if ($item['inv_qty'] < $quantity) {
        echo json_encode(['error' => 'Недостаточно предметов']);
        return;
    }
    
    if ($quantity > 1 && $item['type'] !== 'potion' && $item['type'] !== 'food' && $item['type'] !== 'material') {
        echo json_encode(['error' => 'Можно продавать только расходуемые предметы']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE inventory SET quantity = quantity - ? 
                           WHERE user_id = ? AND item_id = ? AND equipped = 0");
    $stmt->execute([$quantity, $_SESSION['user_id'], $itemId]);
    
    $stmt = $pdo->prepare("DELETE FROM inventory WHERE user_id = ? AND item_id = ? AND quantity <= 0 AND equipped = 0");
    $stmt->execute([$_SESSION['user_id'], $itemId]);
    
    $expiresAt = date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60);
    
    $stmt = $pdo->prepare("INSERT INTO market_listings 
        (seller_id, item_id, item_name, item_type, item_rarity, price_gold, price_crystals, quantity, description, expires_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'], $itemId, $item['name'], $item['type'], $item['rarity'],
        $priceGold, $priceCrystals, $quantity, $description, $expiresAt
    ]);
    
    echo json_encode(['success' => true, 'listing_id' => $pdo->lastInsertId()]);
}

function buyFromMarket(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $listingId = (int)$_POST['listing_id'];
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT m.*, u.username as seller_name FROM market_listings m 
                           JOIN users u ON m.seller_id = u.id 
                           WHERE m.id = ? AND m.is_active = 1");
    $stmt->execute([$listingId]);
    $listing = $stmt->fetch();
    
    if (!$listing) {
        echo json_encode(['error' => 'Объявление не найдено']);
        return;
    }
    
    if ($listing['seller_id'] == $_SESSION['user_id']) {
        echo json_encode(['error' => 'Нельзя купить свой товар']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT gold, crystals FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $buyer = $stmt->fetch();
    
    $totalGold = $listing['price_gold'];
    $totalCrystals = $listing['price_crystals'];
    
    if ($buyer['gold'] < $totalGold || $buyer['crystals'] < $totalCrystals) {
        echo json_encode(['error' => 'Недостаточно ресурсов']);
        return;
    }
    
    $pdo->beginTransaction();
    
    $pdo->prepare("UPDATE users SET gold = gold - ?, crystals = crystals - ? WHERE id = ?")
        ->execute([$totalGold, $totalCrystals, $_SESSION['user_id']]);
    
    $stmt = $pdo->prepare("SELECT id FROM inventory WHERE user_id = ? AND item_id = ? AND equipped = 0");
    $stmt->execute([$_SESSION['user_id'], $listing['item_id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $pdo->prepare("UPDATE inventory SET quantity = quantity + ? WHERE id = ?")
            ->execute([$listing['quantity'], $existing['id']]);
    } else {
        $pdo->prepare("INSERT INTO inventory (user_id, item_id, quantity, equipped) VALUES (?, ?, ?, 0)")
            ->execute([$_SESSION['user_id'], $listing['item_id'], $listing['quantity']]);
    }
    
    $pdo->prepare("UPDATE users SET gold = gold + ? WHERE id = ?")
        ->execute([$totalGold, $listing['seller_id']]);
    
    $pdo->prepare("UPDATE market_listings SET is_active = 0 WHERE id = ?")
        ->execute([$listingId]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'item_name' => $listing['item_name'],
        'quantity' => $listing['quantity'],
        'price_gold' => $totalGold,
        'price_crystals' => $totalCrystals
    ]);
}

function cancelMarketListing(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $listingId = (int)$_POST['listing_id'];
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM market_listings WHERE id = ? AND seller_id = ? AND is_active = 1");
    $stmt->execute([$listingId, $_SESSION['user_id']]);
    $listing = $stmt->fetch();
    
    if (!$listing) {
        echo json_encode(['error' => 'Объявление не найдено']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM inventory WHERE user_id = ? AND item_id = ? AND equipped = 0");
    $stmt->execute([$_SESSION['user_id'], $listing['item_id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $pdo->prepare("UPDATE inventory SET quantity = quantity + ? WHERE id = ?")
            ->execute([$listing['quantity'], $existing['id']]);
    } else {
        $pdo->prepare("INSERT INTO inventory (user_id, item_id, quantity, equipped) VALUES (?, ?, ?, 0)")
            ->execute([$_SESSION['user_id'], $listing['item_id'], $listing['quantity']]);
    }
    
    $pdo->prepare("UPDATE market_listings SET is_active = 0 WHERE id = ?")->execute([$listingId]);
    
    echo json_encode(['success' => true]);
}

function getMyMarketListings(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM market_listings WHERE seller_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $listings = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'listings' => $listings]);
}

// ================================
// ВОЙНЫ ГИЛЬДИЙ
// ================================

function getGuildWars(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT w.*, 
        ga.name as attacker_name, gd.name as defender_name,
        g1.level as attacker_level, g2.level as defender_level
        FROM guild_wars w
        JOIN guilds ga ON w.attacker_guild_id = ga.id
        JOIN guilds gd ON w.defender_guild_id = gd.id
        JOIN guilds g1 ON w.attacker_guild_id = g1.id
        JOIN guilds g2 ON w.defender_guild_id = g2.id
        WHERE w.status = 'active'
        ORDER BY w.start_time DESC");
    $wars = $stmt->fetchAll();
    echo json_encode(['success' => true, 'wars' => $wars]);
}

function getMyGuildWarStatus(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT g.id, g.name, g.level FROM guild_members gm JOIN guilds g ON gm.guild_id = g.id WHERE gm.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $guild = $stmt->fetch();
    
    if (!$guild) {
        echo json_encode(['success' => true, 'in_guild' => false]);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT w.*, 
        ga.name as attacker_name, gd.name as defender_name
        FROM guild_wars w
        JOIN guilds ga ON w.attacker_guild_id = ga.id
        JOIN guilds gd ON w.defender_guild_id = gd.id
        WHERE (w.attacker_guild_id = ? OR w.defender_guild_id = ?) AND w.status = 'active'");
    $stmt->execute([$guild['id'], $guild['id']]);
    $activeWar = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT wt.*, u.username FROM war_participants wt JOIN users u ON wt.user_id = u.id WHERE wt.war_id = ? ORDER BY wt.kills DESC LIMIT 10");
    $topKills = [];
    if ($activeWar) {
        $stmt->execute([$activeWar['id']]);
        $topKills = $stmt->fetchAll();
    }
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_kills, SUM(kills) as kills, SUM(deaths) as deaths FROM war_participants wp JOIN guild_wars w ON wp.war_id = w.id WHERE w.attacker_guild_id = ? OR w.defender_guild_id = ?");
    $stmt->execute([$guild['id'], $guild['id']]);
    $stats = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'in_guild' => true,
        'guild' => $guild,
        'active_war' => $activeWar,
        'top_kills' => $topKills,
        'stats' => $stats
    ]);
}

function declareGuildWar(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $targetGuildId = (int)$_POST['guild_id'];
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT g.*, gm.role FROM guild_members gm JOIN guilds g ON gm.guild_id = g.id WHERE gm.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $myGuild = $stmt->fetch();
    
    if (!$myGuild) {
        echo json_encode(['error' => 'Вы не состоите в гильдии']);
        return;
    }
    
    if (!in_array($myGuild['role'], ['leader', 'officer'])) {
        echo json_encode(['error' => 'Только лидер и офицеры могут объявлять войну']);
        return;
    }
    
    if ($myGuild['level'] < 3) {
        echo json_encode(['error' => 'Гильдия должна быть минимум 3 уровня']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id, name, level FROM guilds WHERE id = ?");
    $stmt->execute([$targetGuildId]);
    $targetGuild = $stmt->fetch();
    
    if (!$targetGuild) {
        echo json_encode(['error' => 'Гильдия не найдена']);
        return;
    }
    
    if ($targetGuild['id'] == $myGuild['id']) {
        echo json_encode(['error' => 'Нельзя объявить войну своей гильдии']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM guild_wars WHERE 
        ((attacker_guild_id = ? AND defender_guild_id = ?) OR 
        (attacker_guild_id = ? AND defender_guild_id = ?)) AND status = 'active'");
    $stmt->execute([$myGuild['id'], $targetGuild['id'], $targetGuild['id'], $myGuild['id']]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Война с этой гильдией уже идёт']);
        return;
    }
    
    $warCost = 500;
    if ($myGuild['gold'] < $warCost) {
        echo json_encode(['error' => 'Нужно ' . $warCost . ' золота для объявления войны']);
        return;
    }
    
    $pdo->beginTransaction();
    
    $pdo->prepare("UPDATE guilds SET gold = gold - ? WHERE id = ?")->execute([$warCost, $myGuild['id']]);
    
    $stmt = $pdo->prepare("INSERT INTO guild_wars (attacker_guild_id, defender_guild_id, status, start_time) VALUES (?, ?, 'active', NOW())");
    $stmt->execute([$myGuild['id'], $targetGuild['id']]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'war_id' => $pdo->lastInsertId(), 'target_guild' => $targetGuild['name']]);
}

function joinGuildWar(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT g.id, g.name FROM guild_members gm JOIN guilds g ON gm.guild_id = g.id WHERE gm.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $guild = $stmt->fetch();
    
    if (!$guild) {
        echo json_encode(['error' => 'Вы не состоите в гильдии']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT w.* FROM guild_wars w 
        WHERE (w.attacker_guild_id = ? OR w.defender_guild_id = ?) AND w.status = 'active'");
    $stmt->execute([$guild['id'], $guild['id']]);
    $war = $stmt->fetch();
    
    if (!$war) {
        echo json_encode(['error' => 'Нет активной войны']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM war_participants WHERE war_id = ? AND user_id = ?");
    $stmt->execute([$war['id'], $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Вы уже участвуете в войне']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT hp, max_hp, atk, def FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $player = $stmt->fetch();
    
    if ($player['hp'] <= 0) {
        echo json_encode(['error' => 'Вы мертвы. Восстановитесь прежде']);
        return;
    }
    
    $pdo->prepare("INSERT INTO war_participants (war_id, user_id) VALUES (?, ?)")->execute([$war['id'], $_SESSION['user_id']]);
    
    echo json_encode(['success' => true]);
}

function attackGuildWarEnemy(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $targetUserId = (int)$_POST['target_user_id'];
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT g.id, g.name FROM guild_members gm JOIN guilds g ON gm.guild_id = g.id WHERE gm.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $myGuild = $stmt->fetch();
    
    if (!$myGuild) {
        echo json_encode(['error' => 'Вы не состоите в гильдии']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT w.*, ga.id as attacker_id, gd.id as defender_id FROM guild_wars w
        JOIN guilds ga ON w.attacker_guild_id = ga.id
        JOIN guilds gd ON w.defender_guild_id = gd.id
        WHERE (w.attacker_guild_id = ? OR w.defender_guild_id = ?) AND w.status = 'active'");
    $stmt->execute([$myGuild['id'], $myGuild['id']]);
    $war = $stmt->fetch();
    
    if (!$war) {
        echo json_encode(['error' => 'Нет активной войны']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT id FROM war_participants WHERE war_id = ? AND user_id = ?");
    $stmt->execute([$war['id'], $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        echo json_encode(['error' => 'Сначала присоединитесь к войне']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT g.id, g.name FROM guild_members gm JOIN guilds g ON gm.guild_id = g.id WHERE gm.user_id = ?");
    $stmt->execute([$targetUserId]);
    $targetGuild = $stmt->fetch();
    
    $isEnemy = ($war['attacker_id'] == $myGuild['id'] && $targetGuild && $targetGuild['id'] == $war['defender_id']) ||
               ($war['defender_id'] == $myGuild['id'] && $targetGuild && $targetGuild['id'] == $war['attacker_id']);
    
    if (!$isEnemy) {
        echo json_encode(['error' => 'Это не враг']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT u.*, wp.kills, wp.deaths, wp.damage_dealt FROM users u 
        LEFT JOIN war_participants wp ON wp.user_id = u.id AND wp.war_id = ?
        WHERE u.id = ?");
    $stmt->execute([$war['id'], $targetUserId]);
    $target = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT u.*, wp.kills, wp.deaths, wp.damage_dealt FROM users u 
        LEFT JOIN war_participants wp ON wp.user_id = u.id AND wp.war_id = ?
        WHERE u.id = ?");
    $stmt->execute([$war['id'], $_SESSION['user_id']]);
    $attacker = $stmt->fetch();
    
    $dmg = max(1, $attacker['atk'] - $target['def'] / 2);
    $dmg = rand((int)($dmg * 0.8), (int)($dmg * 1.2));
    
    $targetHp = max(0, $target['hp'] - $dmg);
    
    $pdo->beginTransaction();
    
    $pdo->prepare("UPDATE users SET hp = ? WHERE id = ?")->execute([$targetHp, $targetUserId]);
    $pdo->prepare("UPDATE war_participants SET kills = kills + 1, damage_dealt = damage_dealt + ? WHERE war_id = ? AND user_id = ?")
        ->execute([$dmg, $war['id'], $_SESSION['user_id']]);
    
    if ($targetHp <= 0) {
        $pdo->prepare("UPDATE war_participants SET deaths = deaths + 1 WHERE war_id = ? AND user_id = ?")
            ->execute([$war['id'], $targetUserId]);
        
        $killerGuild = $myGuild['id'] == $war['attacker_id'] ? 'attacker_score' : 'defender_score';
        $pdo->prepare("UPDATE guild_wars SET $killerGuild = $killerGuild + 1 WHERE id = ?")
            ->execute([$war['id']]);
        
        $pdo->prepare("UPDATE users SET hp = max_hp, mp = max_mp WHERE id = ?")->execute([$targetUserId]);
        
        $pdo->prepare("INSERT INTO war_history (war_id, event_type, description) VALUES (?, 'kill', ?)")
            ->execute([$war['id'], $_SESSION['user_id'] . ' убил ' . $targetUserId]);
    }
    
    $pdo->commit();
    
    if ($targetHp <= 0) {
        echo json_encode(['success' => true, 'kill' => true, 'damage' => $dmg, 'target_hp' => 0]);
    } else {
        echo json_encode(['success' => true, 'kill' => false, 'damage' => $dmg, 'target_hp' => $targetHp]);
    }
}

function getGuildWarLeaderboard(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT g.name, g.level, 
        (SELECT COUNT(*) FROM guild_wars w WHERE (w.attacker_guild_id = g.id OR w.defender_guild_id = g.id) AND w.winner_id = g.id) as wins,
        (SELECT SUM(attacker_score + defender_score) FROM guild_wars w WHERE w.attacker_guild_id = g.id OR w.defender_guild_id = g.id) as total_kills
        FROM guilds g
        HAVING total_kills IS NOT NULL OR wins > 0
        ORDER BY wins DESC, total_kills DESC
        LIMIT 20");
    $leaderboard = $stmt->fetchAll();
    echo json_encode(['success' => true, 'leaderboard' => $leaderboard]);
}

function checkAdmin(): bool {
    if (empty($_SESSION['user_id'])) return false;
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return isset($user['is_admin']) && $user['is_admin'] == 1;
}

function adminLogin(): void {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_admin = 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if (!$admin || !password_verify($password, $admin['password'])) {
        echo json_encode(['success' => false, 'error' => 'Неверный пароль или нет прав админа']);
        return;
    }
    
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    echo json_encode(['success' => true, 'username' => $admin['username']]);
}

function adminGetUsers(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $search = $_GET['search'] ?? '';
    $pdo = Database::getConnection();
    
    $sql = "SELECT id, username, race, class, level, hp, max_hp, mp, max_mp, atk, def, gold, crystals, pvp_rating, pvp_wins, pvp_losses, battles_won, last_login, created_at FROM users";
    $params = [];
    if ($search) {
        $sql .= " WHERE username LIKE ?";
        $params[] = "%$search%";
    }
    $sql .= " ORDER BY id DESC LIMIT 100";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success' => true, 'users' => $stmt->fetchAll()]);
}

function adminUpdateUser(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $userId = (int)$_POST['user_id'];
    $fields = [];
    $params = [];
    
    $allowed = ['username','race','class','level','hp','max_hp','mp','max_mp','atk','def','gold','crystals','pvp_rating','pvp_wins','pvp_losses','battles_won','battles_lost','exp'];
    foreach ($allowed as $field) {
        if (isset($_POST[$field])) {
            $fields[] = "$field = ?";
            $params[] = $_POST[$field];
        }
    }
    
    if (empty($fields)) {
        echo json_encode(['error' => 'No fields to update']);
        return;
    }
    
    $params[] = $userId;
    $pdo = Database::getConnection();
    $pdo->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    echo json_encode(['success' => true]);
}

function adminDeleteUser(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $userId = (int)$_POST['user_id'];
    if ($userId == $_SESSION['user_id']) {
        echo json_encode(['error' => 'Нельзя удалить себя']);
        return;
    }
    
    $pdo = Database::getConnection();
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
    echo json_encode(['success' => true]);
}

function adminResetPassword(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $userId = (int)$_POST['user_id'];
    $newPassword = $_POST['new_password'] ?? 'hero123';
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    $pdo = Database::getConnection();
    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $userId]);
    echo json_encode(['success' => true, 'new_password' => $newPassword]);
}

function adminGetItems(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM items ORDER BY id");
    echo json_encode(['success' => true, 'items' => $stmt->fetchAll()]);
}

function adminSaveItem(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $id = (int)($_POST['id'] ?? 0);
    $name = $_POST['name'] ?? '';
    $type = $_POST['type'] ?? 'weapon';
    $rarity = $_POST['rarity'] ?? 'common';
    $value = (int)($_POST['value'] ?? 10);
    $reqLevel = (int)($_POST['required_level'] ?? 1);
    $atkBonus = (int)($_POST['atk_bonus'] ?? 0);
    $defBonus = (int)($_POST['def_bonus'] ?? 0);
    $hpBonus = (int)($_POST['hp_bonus'] ?? 0);
    $mpBonus = (int)($_POST['mp_bonus'] ?? 0);
    $desc = $_POST['description'] ?? '';
    
    $pdo = Database::getConnection();
    if ($id > 0) {
        $pdo->prepare("UPDATE items SET name=?, type=?, rarity=?, value=?, required_level=?, atk_bonus=?, def_bonus=?, hp_bonus=?, mp_bonus=?, description=? WHERE id=?")
            ->execute([$name,$type,$rarity,$value,$reqLevel,$atkBonus,$defBonus,$hpBonus,$mpBonus,$desc,$id]);
    } else {
        $pdo->prepare("INSERT INTO items (name, type, rarity, value, required_level, atk_bonus, def_bonus, hp_bonus, mp_bonus, description) VALUES (?,?,?,?,?,?,?,?,?,?)")
            ->execute([$name,$type,$rarity,$value,$reqLevel,$atkBonus,$defBonus,$hpBonus,$mpBonus,$desc]);
    }
    echo json_encode(['success' => true]);
}

function adminDeleteItem(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $itemId = (int)$_POST['item_id'];
    $pdo = Database::getConnection();
    $pdo->prepare("DELETE FROM items WHERE id = ?")->execute([$itemId]);
    echo json_encode(['success' => true]);
}

function adminGetEnemies(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM enemies ORDER BY id");
    echo json_encode(['success' => true, 'enemies' => $stmt->fetchAll()]);
}

function adminSaveEnemy(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $id = (int)($_POST['id'] ?? 0);
    $name = $_POST['name'] ?? '';
    $level = (int)($_POST['level'] ?? 1);
    $hp = (int)($_POST['hp'] ?? 50);
    $atk = (int)($_POST['atk'] ?? 5);
    $def = (int)($_POST['def'] ?? 2);
    $expReward = (int)($_POST['exp_reward'] ?? 10);
    $goldReward = (int)($_POST['gold_reward'] ?? 5);
    $crystalsReward = (int)($_POST['crystals_reward'] ?? 0);
    $isBoss = isset($_POST['is_boss']) ? 1 : 0;
    
    $pdo = Database::getConnection();
    if ($id > 0) {
        $pdo->prepare("UPDATE enemies SET name=?, level=?, hp=?, atk=?, def=?, exp_reward=?, gold_reward=?, crystals_reward=?, is_boss=? WHERE id=?")
            ->execute([$name,$level,$hp,$atk,$def,$expReward,$goldReward,$crystalsReward,$isBoss,$id]);
    } else {
        $pdo->prepare("INSERT INTO enemies (name, level, hp, atk, def, exp_reward, gold_reward, crystals_reward, is_boss) VALUES (?,?,?,?,?,?,?,?,?)")
            ->execute([$name,$level,$hp,$atk,$def,$expReward,$goldReward,$crystalsReward,$isBoss]);
    }
    echo json_encode(['success' => true]);
}

function adminDeleteEnemy(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $enemyId = (int)$_POST['enemy_id'];
    $pdo = Database::getConnection();
    $pdo->prepare("DELETE FROM enemies WHERE id = ?")->execute([$enemyId]);
    echo json_encode(['success' => true]);
}

function adminGetQuests(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM quests ORDER BY id");
    echo json_encode(['success' => true, 'quests' => $stmt->fetchAll()]);
}

function adminSaveQuest(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $id = (int)($_POST['id'] ?? 0);
    $title = $_POST['title'] ?? '';
    $desc = $_POST['description'] ?? '';
    $type = $_POST['type'] ?? 'kill';
    $targetId = (int)($_POST['target_id'] ?? 0);
    $targetCount = (int)($_POST['target_count'] ?? 1);
    $expReward = (int)($_POST['exp_reward'] ?? 50);
    $goldReward = (int)($_POST['gold_reward'] ?? 25);
    $reqLevel = (int)($_POST['required_level'] ?? 1);
    
    $pdo = Database::getConnection();
    if ($id > 0) {
        $pdo->prepare("UPDATE quests SET title=?, description=?, type=?, target_id=?, target_count=?, exp_reward=?, gold_reward=?, required_level=? WHERE id=?")
            ->execute([$title,$desc,$type,$targetId,$targetCount,$expReward,$goldReward,$reqLevel,$id]);
    } else {
        $pdo->prepare("INSERT INTO quests (title, description, type, target_id, target_count, exp_reward, gold_reward, required_level) VALUES (?,?,?,?,?,?,?,?)")
            ->execute([$title,$desc,$type,$targetId,$targetCount,$expReward,$goldReward,$reqLevel]);
    }
    echo json_encode(['success' => true]);
}

function adminDeleteQuest(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $questId = (int)$_POST['quest_id'];
    $pdo = Database::getConnection();
    $pdo->prepare("DELETE FROM quests WHERE id = ?")->execute([$questId]);
    echo json_encode(['success' => true]);
}

function adminGetGuilds(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT g.*, u.username as leader_name FROM guilds g JOIN users u ON g.leader_id = u.id ORDER BY g.id");
    echo json_encode(['success' => true, 'guilds' => $stmt->fetchAll()]);
}

function adminDeleteGuild(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $guildId = (int)$_POST['guild_id'];
    $pdo = Database::getConnection();
    $pdo->prepare("DELETE FROM guilds WHERE id = ?")->execute([$guildId]);
    echo json_encode(['success' => true]);
}

function adminGetRaids(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM raid_bosses ORDER BY id");
    echo json_encode(['success' => true, 'raids' => $stmt->fetchAll()]);
}

function adminSaveRaid(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $id = (int)($_POST['id'] ?? 0);
    $name = $_POST['name'] ?? '';
    $desc = $_POST['description'] ?? '';
    $level = (int)($_POST['level'] ?? 10);
    $hp = (int)($_POST['hp'] ?? 500);
    $atk = (int)($_POST['atk'] ?? 30);
    $expReward = (int)($_POST['exp_reward'] ?? 200);
    $goldReward = (int)($_POST['gold_reward'] ?? 150);
    $limit = (int)($_POST['participants_limit'] ?? 10);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    $pdo = Database::getConnection();
    if ($id > 0) {
        $pdo->prepare("UPDATE raid_bosses SET name=?, description=?, level=?, hp=?, max_hp=?, atk=?, exp_reward=?, gold_reward=?, participants_limit=?, is_active=? WHERE id=?")
            ->execute([$name,$desc,$level,$hp,$hp,$atk,$expReward,$goldReward,$limit,$isActive,$id]);
    } else {
        $pdo->prepare("INSERT INTO raid_bosses (name, description, level, hp, max_hp, atk, exp_reward, gold_reward, participants_limit, is_active) VALUES (?,?,?,?,?,?,?,?,?,?)")
            ->execute([$name,$desc,$level,$hp,$hp,$atk,$expReward,$goldReward,$limit,$isActive]);
    }
    echo json_encode(['success' => true]);
}

function adminDeleteRaid(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $raidId = (int)$_POST['raid_id'];
    $pdo = Database::getConnection();
    $pdo->prepare("DELETE FROM raid_bosses WHERE id = ?")->execute([$raidId]);
    echo json_encode(['success' => true]);
}

function adminGetStats(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM users");
    $totalUsers = $stmt->fetch()['cnt'];
    
    $stmt = $pdo->query("SELECT SUM(gold) as total FROM users");
    $totalGold = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT SUM(pvp_wins) as total FROM users");
    $pvpWins = $stmt->fetch()['total'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM guilds");
    $totalGuilds = $stmt->fetch()['cnt'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM quests");
    $totalQuests = $stmt->fetch()['cnt'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM raid_bosses");
    $totalRaids = $stmt->fetch()['cnt'];
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_users' => $totalUsers,
            'total_gold' => $totalGold,
            'pvp_wins' => $pvpWins,
            'total_guilds' => $totalGuilds,
            'total_quests' => $totalQuests,
            'total_raids' => $totalRaids
        ]
    ]);
}

function adminSendAnnouncement(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $message = $_POST['message'] ?? '';
    if (empty($message)) {
        echo json_encode(['error' => 'Пустое сообщение']);
        return;
    }
    
    $pdo = Database::getConnection();
    $pdo->prepare("INSERT INTO chat_messages (user_id, message, channel, message_type) VALUES (1, ?, 'global', 'announcement')")
        ->execute([$message]);
    echo json_encode(['success' => true]);
}

function adminGiveItem(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $userId = (int)$_POST['user_id'];
    $itemId = (int)$_POST['item_id'];
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT id FROM inventory WHERE user_id = ? AND item_id = ? AND equipped = 0");
    $stmt->execute([$userId, $itemId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $pdo->prepare("UPDATE inventory SET quantity = quantity + ? WHERE id = ?")->execute([$quantity, $existing['id']]);
    } else {
        $pdo->prepare("INSERT INTO inventory (user_id, item_id, quantity) VALUES (?, ?, ?)")->execute([$userId, $itemId, $quantity]);
    }
    echo json_encode(['success' => true]);
}

function adminTeleportUser(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $userId = (int)$_POST['user_id'];
    $locationId = (int)$_POST['location_id'];
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT id FROM user_locations WHERE user_id = ? AND location_id = ?");
    $stmt->execute([$userId, $locationId]);
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO user_locations (user_id, location_id) VALUES (?, ?)")->execute([$userId, $locationId]);
    }
    echo json_encode(['success' => true]);
}

function adminBanUser(): void {
    if (!checkAdmin()) { echo json_encode(['error' => 'Access denied']); return; }
    
    $userId = (int)$_POST['user_id'];
    $ban = isset($_POST['ban']) ? 1 : 0;
    
    $pdo = Database::getConnection();
    $pdo->prepare("UPDATE users SET is_banned = ? WHERE id = ?")->execute([$ban, $userId]);
    echo json_encode(['success' => true]);
}