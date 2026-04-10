<?php
/**
 * API: Покупка предмета с рынка
 * POST: listing_id
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
$listingId = (int)($input['listing_id'] ?? 0);

if ($listingId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID лота']);
    exit;
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $buyerId = get_current_user_id();
    
    // Начало транзакции
    $pdo->beginTransaction();
    
    try {
        // Получаем лот с блокировкой
        $listing = $db->fetchOne(
            "SELECT * FROM market_listings WHERE id = :id FOR UPDATE",
            [':id' => $listingId]
        );
        
        if (!$listing) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Лот не найден']);
            exit;
        }
        
        if ($listing['seller_id'] == $buyerId) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Нельзя купить свой собственный предмет']);
            exit;
        }
        
        if ($listing['quantity'] <= 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Товар закончился']);
            exit;
        }
        
        if ($listing['expires_at'] <= date('Y-m-d H:i:s')) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Срок действия лота истек']);
            exit;
        }
        
        // Проверяем баланс покупателя
        $buyer = $db->fetchOne("SELECT gold FROM users WHERE id = :id FOR UPDATE", [':id' => $buyerId]);
        if ($buyer['gold'] < $listing['price']) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Недостаточно золота']);
            exit;
        }
        
        // Получаем продавца
        $sellerId = $listing['seller_id'];
        
        // Перевод золота
        $db->query(
            "UPDATE users SET gold = gold - :price WHERE id = :id",
            [':price' => $listing['price'], ':id' => $buyerId]
        );
        
        $db->query(
            "UPDATE users SET gold = gold + :price WHERE id = :id",
            [':price' => $listing['price'], ':id' => $sellerId]
        );
        
        // Добавление предмета покупателю
        $db->query(
            "INSERT INTO inventory (user_id, item_id, quantity, slot) 
             VALUES (:user_id, :item_id, :quantity, (SELECT COALESCE(MAX(slot) + 1, 1) FROM inventory WHERE user_id = :user_id))
             ON DUPLICATE KEY UPDATE quantity = quantity + :quantity",
            [':user_id' => $buyerId, ':item_id' => $listing['item_id'], ':quantity' => 1]
        );
        
        // Уменьшение количества в лоте или удаление
        if ($listing['quantity'] <= 1) {
            $db->query("DELETE FROM market_listings WHERE id = :id", [':id' => $listingId]);
        } else {
            $db->query(
                "UPDATE market_listings SET quantity = quantity - 1 WHERE id = :id",
                [':id' => $listingId]
            );
        }
        
        // Логирование транзакции
        $db->query(
            "INSERT INTO transactions (buyer_id, seller_id, item_id, price, type, created_at) 
             VALUES (:buyer_id, :seller_id, :item_id, :price, 'market', NOW())",
            [
                ':buyer_id' => $buyerId,
                ':seller_id' => $sellerId,
                ':item_id' => $listing['item_id'],
                ':price' => $listing['price']
            ]
        );
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Покупка совершена успешно',
            'item_id' => $listing['item_id'],
            'price' => $listing['price']
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Market buy error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера: ' . $e->getMessage()]);
}
