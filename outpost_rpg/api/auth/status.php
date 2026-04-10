<?php
/**
 * Проверка статуса авторизации пользователя
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

// Принудительно стартуем сессию если еще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    if (isset($_SESSION['user_id'])) {
        $userId = (int)$_SESSION['user_id'];
        
        $stmt = $pdo->prepare("
            SELECT 
                u.id,
                u.username,
                u.email,
                u.avatar_path as avatar,
                u.level,
                u.experience,
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
                r.color as rank_color
            FROM users u
            LEFT JOIN ranks r ON u.rank_id = r.id
            WHERE u.id = ? AND u.is_banned = 0
        ");
        
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Обновляем last_online
            $updateStmt = $pdo->prepare("UPDATE users SET last_online = NOW() WHERE id = ?");
            $updateStmt->execute([$userId]);
            
            echo json_encode([
                'success' => true,
                'authenticated' => true,
                'user' => $user
            ]);
            exit;
        }
    }
    
    echo json_encode([
        'success' => true,
        'authenticated' => false,
        'user' => null
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка сервера: ' . $e->getMessage()
    ]);
}
