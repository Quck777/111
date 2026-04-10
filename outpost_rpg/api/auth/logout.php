<?php
/**
 * Выход пользователя
 */

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

session_start();
session_destroy();

echo json_encode([
    'success' => true,
    'message' => 'Выход выполнен успешно'
]);
