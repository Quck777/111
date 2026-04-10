<?php
/**
 * Получение профиля пользователя
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Пользователь не авторизован'
        ]);
        exit;
    }
    
    $userId = (int)$_SESSION['user_id'];
    
    // Основная информация
    $stmt = $pdo->prepare("
        SELECT 
            u.id,
            u.username,
            u.email,
            u.avatar_path as avatar,
            u.level,
            u.experience,
            u.experience_to_next,
            u.gold,
            u.energy,
            u.max_energy,
            u.health,
            u.max_health,
            u.strength,
            u.agility,
            u.intelligence,
            u.rank_id,
            u.created_at,
            u.last_online,
            r.name as rank_name,
            r.color as rank_color,
            r.icon_path as rank_icon
        FROM users u
        LEFT JOIN ranks r ON u.rank_id = r.id
        WHERE u.id = ?
    ");
    
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Пользователь не найден'
        ]);
        exit;
    }
    
    // Статистика
    $statsStmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT m.id) as monsters_killed,
            COUNT(DISTINCT q.id) as quests_completed,
            COUNT(DISTINCT a.id) as achievements_unlocked
        FROM users u
        LEFT JOIN monster_kills m ON u.id = m.user_id
        LEFT JOIN user_quests uq ON u.id = uq.user_id AND uq.is_completed = 1
        LEFT JOIN quests q ON uq.quest_id = q.id
        LEFT JOIN user_achievements ua ON u.id = ua.user_id
        LEFT JOIN achievements a ON ua.achievement_id = a.id
        WHERE u.id = ?
        GROUP BY u.id
    ");
    $statsStmt->execute([$userId]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?? ['monsters_killed' => 0, 'quests_completed' => 0, 'achievements_unlocked' => 0];
    
    // Инвентарь (количество предметов)
    $invStmt = $pdo->prepare("SELECT COUNT(*) as item_count FROM inventory WHERE user_id = ?");
    $invStmt->execute([$userId]);
    $inventoryCount = $invStmt->fetch(PDO::FETCH_ASSOC)['item_count'] ?? 0;
    
    // Экипировка
    $equipStmt = $pdo->prepare("
        SELECT 
            e.slot,
            i.id as item_id,
            i.name as item_name,
            i.type as item_type,
            i.rarity as item_rarity,
            i.image as item_image
        FROM equipment e
        LEFT JOIN items i ON e.item_id = i.id
        WHERE e.user_id = ?
    ");
    $equipStmt->execute([$userId]);
    $equipment = [];
    while ($row = $equipStmt->fetch(PDO::FETCH_ASSOC)) {
        $equipment[$row['slot']] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'profile' => array_merge($user, [
            'stats' => $stats,
            'inventory_count' => $inventoryCount,
            'equipment' => $equipment
        ])
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка сервера: ' . $e->getMessage()
    ]);
}
