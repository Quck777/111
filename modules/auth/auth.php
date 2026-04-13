<?php
/**
 * Auth Module - Registration, Login, Logout, User Management
 * Medieval Realm RPG
 */

if (!defined('GAME_MODULE')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Register new user
 */
function register(): void {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $class = $_POST['class'] ?? 'warrior';
    $race = $_POST['race'] ?? 'human';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Заполните все поля']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Имя занято']);
        return;
    }
    
    $classStats = match($class) {
        'warrior' => ['hp' => 120, 'mp' => 40, 'atk' => 12, 'def' => 8],
        'mage' => ['hp' => 80, 'mp' => 100, 'atk' => 8, 'def' => 4],
        'archer' => ['hp' => 90, 'mp' => 60, 'atk' => 14, 'def' => 5],
        'rogue' => ['hp' => 85, 'mp' => 50, 'atk' => 15, 'def' => 4],
        'paladin' => ['hp' => 110, 'mp' => 70, 'atk' => 10, 'def' => 10],
        default => ['hp' => 100, 'mp' => 50, 'atk' => 10, 'def' => 5],
    };
    
    $hp = $classStats['hp'];
    $mp = $classStats['mp'];
    $atk = $classStats['atk'];
    $def = $classStats['def'];
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, race, class, level, hp, max_hp, mp, max_mp, atk, def) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $hash, $race, $class, 1, $hp, $hp, $mp, $mp, $atk, $def]);
    
    $userId = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO user_locations (user_id, location_id) VALUES (?, 1)")->execute([$userId]);
    $pdo->prepare("INSERT INTO user_battle_licenses (user_id, license_id, attacks_remaining, expires_at) VALUES (?, 1, 20, DATE_ADD(NOW(), INTERVAL 24 HOUR))")->execute([$userId]);
    
    echo json_encode(['success' => true]);
}

/**
 * Login user
 */
function login(): void {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'unknown';
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'error' => 'Неверный логин или пароль']);
        return;
    }
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['login_ip'] = $ip;
    $_SESSION['login_time'] = time();
    
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW(), last_ip = ? WHERE id = ?");
    $stmt->execute([$ip, $user['id']]);
    
    unset($user['password']);
    echo json_encode(['success' => true, 'user' => $user, 'ip' => $ip]);
}

/**
 * Logout user
 */
function logout(): void {
    session_destroy();
    echo json_encode(['success' => true]);
}

/**
 * Get current user data
 */
function getUser(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    unset($user['password']);
    echo json_encode($user);
}

/**
 * Get online users
 */
function getOnlineUsers(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT id, username, level, class FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    $users = $stmt->fetchAll();
    echo json_encode(['success' => true, 'count' => count($users), 'users' => $users]);
}
