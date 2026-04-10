<?php
/**
 * API: Выставление предмета на продажу
 * POST: item_id, price, quantity
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
$itemId = (int)($input['item_id'] ?? 0);
$price = (int)($input['price'] ?? 0);
$quantity = max(1, (int)($input['quantity'] ?? 1));

if ($itemId <= 0 || $price <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверные данные']);
    exit;
}

try {
    $db = Database::getInstance();
    $userId = get_current_user_id();
    
    // Проверка наличия предмета в инвентаре
    $inventoryItem = $db->fetchOne(
        "SELECT * FROM inventory WHERE user_id = :user_id AND item_id = :item_id AND quantity >= :quantity",
        [':user_id' => $userId, ':item_id' => $itemId, ':quantity' => $quantity]
    );
    
    if (!$inventoryItem) {
        echo json_encode(['success' => false, 'error' => 'Предмет не найден в инвентаре']);
        exit;
    }
    
    // Проверка предмета
    $item = $db->fetchOne("SELECT * FROM items WHERE id = :id", [':id' => $itemId]);
    if (!$item || !$item['tradable']) {
        echo json_encode(['success' => false, 'error' => 'Этот предмет нельзя продать']);
        exit;
    }
    
    // Начало транзакции
    $pdo = $db->getConnection();
    $pdo->beginTransaction();
    
    try {
        // Создание лота на рынке
        $db->query(
            "INSERT INTO market_listings (seller_id, item_id, price, quantity, created_at, expires_at) 
             VALUES (:seller_id, :item_id, :price, :quantity, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY))",
            [
                ':seller_id' => $userId,
                ':item_id' => $itemId,
                ':price' => $price,
                ':quantity' => $quantity
            ]
        );
        
        $listingId = $pdo->lastInsertId();
        
        // Удаление предмета из инвентаря (резервирование)
        $db->query(
            "UPDATE inventory SET quantity = quantity - :quantity WHERE id = :id",
            [':quantity' => $quantity, ':id' => $inventoryItem['id']]
        );
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Предмет выставлен на продажу',
            'listing_id' => $listingId
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Market sell error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера']);
}
