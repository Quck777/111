<?php
/**
 * Регистрация нового пользователя
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

// Принудительно стартуем сессию если еще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Метод не разрешен']);
    exit;
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Получаем данные
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Неверный формат данных');
    }
    
    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';
    
    // Валидация
    if (strlen($username) < 3 || strlen($username) > 20) {
        throw new Exception('Имя должно быть от 3 до 20 символов');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Неверный формат email');
    }
    
    if (strlen($password) < 6) {
        throw new Exception('Пароль должен быть не менее 6 символов');
    }
    
    if ($password !== $confirmPassword) {
        throw new Exception('Пароли не совпадают');
    }
    
    // Проверка на существование
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $checkStmt->execute([$username, $email]);
    
    if ($checkStmt->fetch()) {
        throw new Exception('Пользователь с таким именем или email уже существует');
    }
    
    // Хеширование пароля
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Создание пользователя
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, avatar_path, level, experience, gold, energy, max_energy, health, max_health, strength, agility, intelligence, rank_id, created_at)
        VALUES (?, ?, ?, 'assets/images/characters/default.png', 1, 0, 100, 100, 100, 100, 100, 5, 5, 5, 1, NOW())
    ");
    
    $stmt->execute([$username, $email, $passwordHash]);
    $userId = $pdo->lastInsertId();
    
    // Старт сессии
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    
    echo json_encode([
        'success' => true,
        'message' => 'Регистрация успешна',
        'user_id' => $userId,
        'username' => $username
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
