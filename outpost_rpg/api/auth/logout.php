<?php
/**
 * Выход пользователя
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

// Принудительно стартуем сессию если еще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if (isset($_SESSION['user_id'])) {
        $userId = (int)$_SESSION['user_id'];
        
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        // Обновление статуса offline
        $updateStmt = $pdo->prepare("UPDATE users SET is_online = 0 WHERE id = ?");
        $updateStmt->execute([$userId]);
    }
    
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Выход выполнен успешно'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка сервера: ' . $e->getMessage()
    ]);
}
