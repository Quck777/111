<?php
/**
 * Chat Module - Chat System, Messages, Spam Filter
 * Medieval Realm RPG
 */

if (!defined('GAME_MODULE')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Get chat messages
 */
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

/**
 * Send chat message
 */
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

/**
 * Filter profanity from message
 */
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

/**
 * Send system message
 */
function sendSystemMessage($message, $channel = 'global'): void {
    $pdo = Database::getConnection();
    $pdo->prepare("INSERT INTO chat_messages (user_id, message, channel, message_type) VALUES (1, ?, ?, 'system')")
        ->execute([$message, $channel]);
}
