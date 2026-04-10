<?php
/**
 * Конфигурационный файл игры ФОРПОСТ
 */

// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'outpost_rpg');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Настройки игры
define('GAME_NAME', 'ФОРПОСТ');
define('GAME_VERSION', '1.0.0');
define('SITE_URL', 'http://localhost/outpost_rpg');
define('ASSETS_URL', SITE_URL . '/assets');

// Пути к файлам
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('API_PATH', ROOT_PATH . '/api');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('IMAGES_PATH', ASSETS_PATH . '/images');

// Настройки сессии
define('SESSION_LIFETIME', 3600); // 1 час
define('SESSION_NAME', 'outpost_session');

// Настройки безопасности
define('PASSWORD_MIN_LENGTH', 6);
define('USERNAME_MIN_LENGTH', 3);
define('USERNAME_MAX_LENGTH', 50);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 минут

// Настройки чата
define('MAX_CHAT_MESSAGE_LENGTH', 500);
define('CHAT_MESSAGE_COOLDOWN', 2); // секунды

// Настройки инвентаря
define('MAX_INVENTORY_SLOTS', 50);
define('MAX_STACK_SIZE', 999);

// Настройки энергии
define('MAX_ENERGY', 100);
define('ENERGY_REGEN_RATE', 1); // в минуту
define('ENERG_REGEN_TIME', 60); // секунд

// Настройки боя
define('PVP_ENABLED', true);
define('PVP_LEVEL_DIFFERENCE', 10);

// Включение ошибок (отключить на продакшене)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Время по умолчанию
date_default_timezone_set('Europe/Moscow');

// Запуск сессии только если это не API запрос (API управляет сессией самостоятельно)
if (!defined('API_REQUEST') && session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    
    // Настройки безопасности сессии
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    session_start();
}
