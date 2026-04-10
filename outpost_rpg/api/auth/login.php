<?php
/**
 * Вход пользователя
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не разрешен']);
    exit;
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Неверный формат данных');
    }
    
    $login = trim($data['login'] ?? '');
    $password = $data['password'] ?? '';
    
    if (empty($login) || empty($password)) {
        throw new Exception('Введите логин и пароль');
    }
    
    // Поиск пользователя по email или username
    $stmt = $pdo->prepare("
        SELECT id, username, password, is_banned 
        FROM users 
        WHERE email = ? OR username = ?
    ");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('Пользователь не найден');
    }
    
    if ($user['is_banned']) {
        throw new Exception('Аккаунт заблокирован');
    }
    
    if (!password_verify($password, $user['password'])) {
        throw new Exception('Неверный пароль');
    }
    
    // Старт сессии
    session_start();
    $_SESSION['user_id'] = $user['id'];
    
    // Обновление last_online
    $updateStmt = $pdo->prepare("UPDATE users SET last_online = NOW() WHERE id = ?");
    $updateStmt->execute([$user['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Вход выполнен успешно',
        'user_id' => $user['id']
    ]);
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
