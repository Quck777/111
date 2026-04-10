<?php
/**
 * API: Отправка сообщения в чат
 * POST: message, channel (global/local/guild)
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
$message = trim($input['message'] ?? '');
$channel = $input['channel'] ?? 'global';

if (empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Сообщение не может быть пустым']);
    exit;
}

if (strlen($message) > 500) {
    echo json_encode(['success' => false, 'error' => 'Сообщение слишком длинное (макс. 500 символов)']);
    exit;
}

$allowedChannels = ['global', 'local', 'guild', 'trade'];
if (!in_array($channel, $allowedChannels)) {
    echo json_encode(['success' => false, 'error' => 'Недопустимый канал']);
    exit;
}

try {
    $db = Database::getInstance();
    $userId = get_current_user_id();
    
    // Проверка cooldown
    $lastMsg = $db->fetchOne(
        "SELECT created_at FROM chat_messages WHERE user_id = :user_id AND channel = :channel ORDER BY created_at DESC LIMIT 1",
        [':user_id' => $userId, ':channel' => $channel]
    );
    
    if ($lastMsg) {
        $lastTime = strtotime($lastMsg['created_at']);
        if (time() - $lastTime < 2) {
            echo json_encode(['success' => false, 'error' => 'Подождите перед следующим сообщением']);
            exit;
        }
    }
    
    // Вставка сообщения
    $db->query(
        "INSERT INTO chat_messages (user_id, channel, message, created_at) VALUES (:user_id, :channel, :message, NOW())",
        [
            ':user_id' => $userId,
            ':channel' => $channel,
            ':message' => $message
        ]
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Сообщение отправлено',
        'data' => [
            'channel' => $channel,
            'message' => $message
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Chat error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера']);
}
