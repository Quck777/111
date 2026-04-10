<?php
/**
 * Управление безопасными сессиями
 */

function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        // Настройки безопасности сессии
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 0); // Установить 1 если используется HTTPS
        ini_set('session.cookie_samesite', 'Strict');
        
        // Название сессии
        ini_set('session.name', 'OUTPOST_SID');
        
        // Время жизни сессии (24 часа)
        ini_set('session.gc_maxlifetime', 86400);
        session_set_cookie_params([
            'lifetime' => 86400,
            'path' => '/',
            'domain' => '', // Оставить пустым для текущего домена
            'secure' => false, // Установить true для HTTPS
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        session_start();
        
        // Регенерация ID сессии для безопасности
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 3600) {
            // Регенерировать каждый час
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

function destroy_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        session_destroy();
    }
}

function require_login() {
    start_secure_session();
    
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: /register.php');
        exit;
    }
    
    return $_SESSION['user_id'] ?? null;
}

function get_current_user_id() {
    start_secure_session();
    return $_SESSION['user_id'] ?? null;
}

function is_logged_in() {
    start_secure_session();
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}
