<?php
/**
 * API: Атака монстра / Начало боя
 * POST: monster_id
 */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не разрешен']);
    exit;
}

start_secure_session();

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Требуется авторизация']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$monsterId = (int)($input['monster_id'] ?? 0);

if ($monsterId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID монстра']);
    exit;
}

try {
    $db = Database::getInstance();
    $userId = get_current_user_id();
    
    // Получаем данные пользователя
    $user = $db->fetchOne("SELECT * FROM users WHERE id = :id", [':id' => $userId]);
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Пользователь не найден']);
        exit;
    }
    
    // Проверка энергии
    if ($user['energy'] < 5) {
        echo json_encode(['success' => false, 'error' => 'Недостаточно энергии (нужно 5)']);
        exit;
    }
    
    // Получаем монстра
    $monster = $db->fetchOne("SELECT * FROM monsters WHERE id = :id AND is_active = 1", [':id' => $monsterId]);
    if (!$monster) {
        echo json_encode(['success' => false, 'error' => 'Монстр не найден или не активен']);
        exit;
    }
    
    // Проверка уровня
    if (abs($user['level'] - $monster['min_level']) > 10) {
        echo json_encode(['success' => false, 'error' => 'Монстр слишком сильный или слабый для вас']);
        exit;
    }
    
    // Списание энергии
    $db->query("UPDATE users SET energy = energy - 5 WHERE id = :id", [':id' => $userId]);
    
    // Расчет боя (упрощенный)
    $userPower = $user['strength'] + $user['agility'] + $user['intelligence'] + ($user['level'] * 10);
    $monsterPower = $monster['health'] + $monster['attack'];
    
    $userRoll = rand(1, 100) + $userPower;
    $monsterRoll = rand(1, 100) + $monsterPower;
    
    $win = $userRoll >= $monsterRoll;
    
    // Логирование боя
    $db->query(
        "INSERT INTO action_logs (user_id, action_type, details, created_at) VALUES (:user_id, 'battle', :details, NOW())",
        [
            ':user_id' => $userId,
            ':details' => json_encode([
                'monster_id' => $monsterId,
                'monster_name' => $monster['name'],
                'result' => $win ? 'win' : 'lose',
                'user_roll' => $userRoll,
                'monster_roll' => $monsterRoll
            ])
        ]
    );
    
    if ($win) {
        // Награда
        $expReward = rand($monster['min_exp'], $monster['max_exp']);
        $goldReward = rand($monster['min_gold'], $monster['max_gold']);
        
        // Обновление статистики пользователя
        $db->query(
            "UPDATE users SET experience = experience + :exp, gold = gold + :gold WHERE id = :id",
            [':exp' => $expReward, ':gold' => $goldReward, ':id' => $userId]
        );
        
        // Запись убийства монстра
        $db->query(
            "INSERT INTO monster_kills (user_id, monster_id, killed_at) VALUES (:user_id, :monster_id, NOW())",
            [':user_id' => $userId, ':monster_id' => $monsterId]
        );
        
        // Проверка повышения уровня
        $newExp = $user['experience'] + $expReward;
        $levelUp = false;
        if ($newExp >= $user['experience_to_next']) {
            $levelUp = true;
            $db->query(
                "UPDATE users SET level = level + 1, experience = 0, experience_to_next = ROUND(experience_to_next * 1.5), max_health = max_health + 10, max_energy = max_energy + 5 WHERE id = :id",
                [':id' => $userId]
            );
        }
        
        echo json_encode([
            'success' => true,
            'battle' => [
                'win' => true,
                'monster' => $monster['name'],
                'experience' => $expReward,
                'gold' => $goldReward,
                'level_up' => $levelUp
            ]
        ]);
    } else {
        // Потеря здоровья
        $damage = rand(5, 15);
        $db->query(
            "UPDATE users SET health = GREATEST(0, health - :damage) WHERE id = :id",
            [':damage' => $damage, ':id' => $userId]
        );
        
        echo json_encode([
            'success' => true,
            'battle' => [
                'win' => false,
                'monster' => $monster['name'],
                'damage_taken' => $damage
            ]
        ]);
    }
    
} catch (Exception $e) {
    error_log("Battle error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера']);
}
