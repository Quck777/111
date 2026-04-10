<?php
/**
 * API: Получение списка монстров в локации
 * GET: location_id
 */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

start_secure_session();

$locationId = (int)($_GET['location_id'] ?? 1);

try {
    $db = Database::getInstance();
    
    $monsters = $db->fetchAll(
        "SELECT 
            m.id,
            m.name,
            m.level as monster_level,
            m.health,
            m.attack,
            m.min_exp,
            m.max_exp,
            m.min_gold,
            m.max_gold,
            m.image,
            m.description,
            l.name as location_name
         FROM monsters m
         LEFT JOIN locations l ON m.location_id = l.id
         WHERE m.location_id = :location_id AND m.is_active = 1
         ORDER BY m.level ASC",
        [':location_id' => $locationId]
    );
    
    echo json_encode([
        'success' => true,
        'monsters' => $monsters
    ]);
    
} catch (Exception $e) {
    error_log("Get monsters error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера']);
}
