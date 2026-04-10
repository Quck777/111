<?php
/**
 * API: Получение сообщений чата
 * GET: channel, limit
 */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

start_secure_session();

$channel = $_GET['channel'] ?? 'global';
$limit = min((int)($_GET['limit'] ?? 50), 100);

$allowedChannels = ['global', 'local', 'guild', 'trade'];
if (!in_array($channel, $allowedChannels)) {
    echo json_encode(['success' => false, 'error' => 'Недопустимый канал']);
    exit;
}

try {
    $db = Database::getInstance();
    
    $messages = $db->fetchAll(
        "SELECT 
            cm.id,
            cm.message,
            cm.channel,
            cm.created_at,
            u.username,
            u.avatar_path as avatar,
            r.name as rank_name,
            r.color as rank_color
         FROM chat_messages cm
         LEFT JOIN users u ON cm.user_id = u.id
         LEFT JOIN ranks r ON u.rank_id = r.id
         WHERE cm.channel = :channel
         ORDER BY cm.created_at DESC
         LIMIT :limit",
        [':channel' => $channel, ':limit' => $limit]
    );
    
    // Переворачиваем массив (новые сообщения внизу)
    $messages = array_reverse($messages);
    
    echo json_encode([
        'success' => true,
        'messages' => $messages
    ]);
    
} catch (Exception $e) {
    error_log("Chat get error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера']);
}
