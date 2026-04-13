<?php
/**
 * Battle Module - PvE, PvP, Arena Combat
 * Medieval Realm RPG
 */

if (!defined('GAME_MODULE')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Get enemies list for player level
 */
function getEnemies(): void {
    $level = (int)($_POST['level'] ?? 1);
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM enemies WHERE level <= ? ORDER BY level");
    $stmt->execute([$level + 5]);
    echo json_encode($stmt->fetchAll());
}

/**
 * Start PvE battle
 */
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
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $player = $stmt->fetch();
    
    if ($player['hp'] <= 0) {
        echo json_encode(['error' => 'Вы мертвы. Воскресните у лекаря.']);
        return;
    }
    
    $_SESSION['battle'] = [
        'type' => 'pve',
        'enemy_id' => $enemyId,
        'enemy_hp' => $enemy['hp'],
        'enemy_max_hp' => $enemy['max_hp'],
        'started_at' => time()
    ];
    
    echo json_encode([
        'success' => true,
        'enemy' => $enemy,
        'player' => [
            'hp' => $player['hp'],
            'max_hp' => $player['max_hp'],
            'mp' => $player['mp'],
            'max_mp' => $player['max_mp'],
            'atk' => $player['atk'],
            'def' => $player['def']
        ]
    ]);
}

/**
 * Attack in battle (PvE)
 */
function attack(): void {
    if (empty($_SESSION['user_id']) || empty($_SESSION['battle'])) {
        echo json_encode(['error' => 'No active battle']);
        return;
    }
    
    $attackType = $_POST['attack_type'] ?? 'normal';
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $player = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT * FROM enemies WHERE id = ?");
    $stmt->execute([$_SESSION['battle']['enemy_id']]);
    $enemy = $stmt->fetch();
    
    $critChance = 0.1;
    $isCrit = rand(0, 100) < ($critChance * 100);
    $dmgMultiplier = $isCrit ? 2.0 : 1.0;
    
    $baseDmg = match($attackType) {
        'strong' => $player['atk'] * 1.5,
        'precise' => $player['atk'] * 0.8,
        default => $player['atk']
    };
    
    $damage = max(1, floor($baseDmg * $dmgMultiplier - $enemy['def'] * 0.5));
    $newEnemyHp = max(0, $_SESSION['battle']['enemy_hp'] - $damage);
    $_SESSION['battle']['enemy_hp'] = $newEnemyHp;
    
    $log = ["Вы нанесли {$damage} урона" . ($isCrit ? " (КРИТИЧЕСКИЙ!)" : "")];
    
    if ($newEnemyHp <= 0) {
        $expGain = $enemy['exp'];
        $goldGain = $enemy['gold'];
        
        $stmt = $pdo->prepare("UPDATE users SET gold = gold + ?, exp = exp + ? WHERE id = ?");
        $stmt->execute([$goldGain, $expGain, $_SESSION['user_id']]);
        
        $log[] = "Победа! Получено: {$expGain} опыта, {$goldGain} золота";
        
        unset($_SESSION['battle']);
        
        echo json_encode([
            'success' => true,
            'won' => true,
            'log' => $log,
            'rewards' => ['exp' => $expGain, 'gold' => $goldGain]
        ]);
        return;
    }
    
    $enemyDmg = max(1, floor($enemy['atk'] - $player['def'] * 0.5));
    $newPlayerHp = max(0, $player['hp'] - $enemyDmg);
    
    $stmt = $pdo->prepare("UPDATE users SET hp = ? WHERE id = ?");
    $stmt->execute([$newPlayerHp, $_SESSION['user_id']]);
    
    $log[] = "Враг нанес вам {$enemyDmg} урона";
    
    if ($newPlayerHp <= 0) {
        $stmt = $pdo->prepare("UPDATE users SET deaths = deaths + 1 WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        unset($_SESSION['battle']);
        
        echo json_encode([
            'success' => true,
            'won' => false,
            'log' => $log,
            'defeated' => true
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'won' => false,
        'log' => $log,
        'enemy_hp' => $newEnemyHp,
        'player_hp' => $newPlayerHp
    ]);
}

/**
 * Use potion in battle
 */
function usePotion(): void {
    if (empty($_SESSION['user_id']) || empty($_SESSION['battle'])) {
        echo json_encode(['error' => 'No active battle']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM inventory i JOIN items it ON i.item_id = it.id WHERE i.user_id = ? AND it.type = 'potion' LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $potion = $stmt->fetch();
    
    if (!$potion) {
        echo json_encode(['error' => 'Нет зелий']);
        return;
    }
    
    $healAmount = 50;
    $stmt = $pdo->prepare("UPDATE users SET hp = LEAST(max_hp, hp + ?) WHERE id = ?");
    $stmt->execute([$healAmount, $_SESSION['user_id']]);
    
    if ($potion['quantity'] > 1) {
        $pdo->prepare("UPDATE inventory SET quantity = quantity - 1 WHERE user_id = ? AND id = ?")->execute([$_SESSION['user_id'], $potion['id']]);
    } else {
        $pdo->prepare("DELETE FROM inventory WHERE user_id = ? AND id = ?")->execute([$_SESSION['user_id'], $potion['id']]);
    }
    
    $stmt = $pdo->prepare("SELECT hp, max_hp FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $player = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'healed' => $healAmount,
        'hp' => $player['hp'],
        'max_hp' => $player['max_hp']
    ]);
}

/**
 * Start PvP battle
 */
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
    
    if ($opponent['id'] == $_SESSION['user_id']) {
        echo json_encode(['error' => 'Нельзя сражаться с самим собой']);
        return;
    }
    
    $_SESSION['pvp_battle'] = [
        'opponent_id' => $opponentId,
        'opponent_hp' => $opponent['max_hp'],
        'opponent_max_hp' => $opponent['max_hp'],
        'started_at' => time()
    ];
    
    $stmt = $pdo->prepare("SELECT hp, max_hp, mp, max_mp, atk, def FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $player = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'opponent' => $opponent,
        'player' => $player
    ]);
}

/**
 * PvP Attack
 */
function pvpAttack(): void {
    if (empty($_SESSION['user_id']) || empty($_SESSION['pvp_battle'])) {
        echo json_encode(['error' => 'No active PvP battle']);
        return;
    }
    
    $attackType = $_POST['attack_type'] ?? 'normal';
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $player = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['pvp_battle']['opponent_id']]);
    $opponent = $stmt->fetch();
    
    $isCrit = rand(0, 100) < 10;
    $dmgMultiplier = $isCrit ? 2.0 : 1.0;
    
    $baseDmg = match($attackType) {
        'strong' => $player['atk'] * 1.5,
        default => $player['atk']
    };
    
    $damage = max(1, floor($baseDmg * $dmgMultiplier - $opponent['def'] * 0.5));
    $newOpponentHp = max(0, $_SESSION['pvp_battle']['opponent_hp'] - $damage);
    $_SESSION['pvp_battle']['opponent_hp'] = $newOpponentHp;
    
    $log = ["Вы нанесли {$damage} урона игроку {$opponent['username']}" . ($isCrit ? " (КРИТ!)" : "")];
    
    if ($newOpponentHp <= 0) {
        $ratingChange = 15;
        $stmt = $pdo->prepare("UPDATE users SET pvp_wins = pvp_wins + 1, pvp_rating = pvp_rating + ? WHERE id = ?");
        $stmt->execute([$ratingChange, $_SESSION['user_id']]);
        
        $stmt = $pdo->prepare("UPDATE users SET pvp_losses = pvp_losses + 1, pvp_rating = GREATEST(0, pvp_rating - ?) WHERE id = ?");
        $stmt->execute([$ratingChange, $_SESSION['pvp_battle']['opponent_id']]);
        
        unset($_SESSION['pvp_battle']);
        
        echo json_encode([
            'success' => true,
            'won' => true,
            'log' => $log,
            'rating_change' => $ratingChange
        ]);
        return;
    }
    
    $enemyDmg = max(1, floor($opponent['atk'] - $player['def'] * 0.5));
    $newPlayerHp = max(0, $player['hp'] - $enemyDmg);
    
    $stmt = $pdo->prepare("UPDATE users SET hp = ? WHERE id = ?");
    $stmt->execute([$newPlayerHp, $_SESSION['user_id']]);
    
    $log[] = "{$opponent['username']} нанес вам {$enemyDmg} урона";
    
    if ($newPlayerHp <= 0) {
        $stmt = $pdo->prepare("UPDATE users SET pvp_losses = pvp_losses + 1, pvp_rating = GREATEST(0, pvp_rating - 15) WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        $stmt = $pdo->prepare("UPDATE users SET pvp_wins = pvp_wins + 1, pvp_rating = pvp_rating + 15 WHERE id = ?");
        $stmt->execute([$_SESSION['pvp_battle']['opponent_id']]);
        
        unset($_SESSION['pvp_battle']);
        
        echo json_encode([
            'success' => true,
            'won' => false,
            'log' => $log,
            'defeated' => true
        ]);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'won' => false,
        'log' => $log,
        'opponent_hp' => $newOpponentHp,
        'player_hp' => $newPlayerHp
    ]);
}

/**
 * Get leaderboard
 */
function getLeaderboard(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT id, username, level, class, gold FROM users ORDER BY level DESC LIMIT 20");
    echo json_encode($stmt->fetchAll());
}

/**
 * Get PvP leaderboard
 */
function getPvpLeaderboard(): void {
    $pdo = Database::getConnection();
    $currentUserId = (int)($_SESSION['user_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT id, username, level, class, pvp_rating, pvp_wins, pvp_losses FROM users WHERE id != ? ORDER BY pvp_rating DESC LIMIT 20");
    $stmt->execute([$currentUserId]);
    echo json_encode($stmt->fetchAll());
}

/**
 * Get opponents for arena
 */
function getOpponentsForArena(): void {
    $pdo = Database::getConnection();
    $currentUserId = (int)($_SESSION['user_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT id, username, level, class, pvp_rating, pvp_wins, pvp_losses FROM users WHERE id != ? ORDER BY pvp_rating DESC LIMIT 15");
    $stmt->execute([$currentUserId]);
    echo json_encode($stmt->fetchAll());
}

/**
 * Get battle history
 */
function getHistory(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM battles_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode($stmt->fetchAll());
}
