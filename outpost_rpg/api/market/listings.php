<?php
/**
 * API: Получение списка товаров на рынке
 * GET: page, limit, sort (price_asc/price_desc/date)
 */
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/database.php';

header('Content-Type: application/json');

start_secure_session();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;
$sort = $_GET['sort'] ?? 'date_desc';

$orderBy = 'created_at DESC';
if ($sort === 'price_asc') {
    $orderBy = 'price ASC';
} elseif ($sort === 'price_desc') {
    $orderBy = 'price DESC';
} elseif ($sort === 'date_asc') {
    $orderBy = 'created_at ASC';
}

try {
    $db = Database::getInstance();
    
    $listings = $db->fetchAll(
        "SELECT 
            ml.id,
            ml.item_id,
            ml.price,
            ml.quantity,
            ml.created_at,
            ml.expires_at,
            i.name as item_name,
            i.type as item_type,
            i.rarity as item_rarity,
            i.image as item_image,
            u.username as seller_name,
            r.name as seller_rank
         FROM market_listings ml
         LEFT JOIN items i ON ml.item_id = i.id
         LEFT JOIN users u ON ml.seller_id = u.id
         LEFT JOIN ranks r ON u.rank_id = r.id
         WHERE ml.quantity > 0 AND ml.expires_at > NOW()
         ORDER BY {$orderBy}
         LIMIT :offset, :limit",
        [':offset' => $offset, ':limit' => $limit]
    );
    
    // Общее количество
    $totalStmt = $db->getConnection()->query("SELECT COUNT(*) FROM market_listings WHERE quantity > 0 AND expires_at > NOW()");
    $total = (int)$totalStmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'listings' => $listings,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_items' => $total
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Market listings error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера']);
}
