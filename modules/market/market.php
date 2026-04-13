<?php
/**
 * Market Module - Player Marketplace, Trading
 * Medieval Realm RPG
 */

if (!defined('GAME_MODULE')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Get all market listings
 */
function getMarketListings(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT ml.*, it.name as item_name, it.type, it.rarity, u.username as seller_name 
        FROM market_listings ml 
        JOIN items it ON ml.item_id = it.id 
        JOIN users u ON ml.seller_id = u.id 
        WHERE ml.status = 'active' 
        ORDER BY ml.created_at DESC");
    echo json_encode($stmt->fetchAll());
}

/**
 * Create market listing
 */
function createMarketListing(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $inventoryId = (int)$_POST['inventory_id'];
    $price = (int)$_POST['price'];
    
    if ($price <= 0) {
        echo json_encode(['error' => 'Некорректная цена']);
        return;
    }
    
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT i.*, it.type FROM inventory i JOIN items it ON i.item_id = it.id WHERE i.user_id = ? AND i.id = ?");
    $stmt->execute([$_SESSION['user_id'], $inventoryId]);
    $item = $stmt->fetch();
    
    if (!$item) {
        echo json_encode(['error' => 'Предмет не найден']);
        return;
    }
    
    if ($item['type'] === 'potion') {
        echo json_encode(['error' => 'Нельзя выставить зелья на продажу']);
        return;
    }
    
    $pdo->beginTransaction();
    
    if ($item['quantity'] > 1) {
        $pdo->prepare("UPDATE inventory SET quantity = quantity - 1 WHERE user_id = ? AND id = ?")->execute([$_SESSION['user_id'], $inventoryId]);
    } else {
        $pdo->prepare("DELETE FROM inventory WHERE user_id = ? AND id = ?")->execute([$_SESSION['user_id'], $inventoryId]);
    }
    
    $pdo->prepare("INSERT INTO market_listings (seller_id, item_id, quantity, price, status, created_at) VALUES (?, ?, 1, ?, 'active', NOW())")
        ->execute([$_SESSION['user_id'], $item['item_id'], $price]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Лот выставлен на продажу']);
}

/**
 * Buy from market
 */
function buyFromMarket(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $listingId = (int)$_POST['listing_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM market_listings WHERE id = ? AND status = 'active' FOR UPDATE");
    $stmt->execute([$listingId]);
    $listing = $stmt->fetch();
    
    if (!$listing) {
        echo json_encode(['error' => 'Лот не найден или уже продан']);
        return;
    }
    
    if ($listing['seller_id'] == $_SESSION['user_id']) {
        echo json_encode(['error' => 'Нельзя купить свой собственный лот']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT gold FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $buyer = $stmt->fetch();
    
    if ($buyer['gold'] < $listing['price']) {
        echo json_encode(['error' => 'Недостаточно золота']);
        return;
    }
    
    $pdo->beginTransaction();
    
    $pdo->prepare("UPDATE users SET gold = gold - ? WHERE id = ?")->execute([$listing['price'], $_SESSION['user_id']]);
    $pdo->prepare("UPDATE users SET gold = gold + ? WHERE id = ?")->execute([$listing['price'], $listing['seller_id']]);
    
    $stmt = $pdo->prepare("SELECT id FROM inventory WHERE user_id = ? AND item_id = ?");
    $stmt->execute([$_SESSION['user_id'], $listing['item_id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $pdo->prepare("UPDATE inventory SET quantity = quantity + ? WHERE user_id = ? AND item_id = ?")
            ->execute([$listing['quantity'], $_SESSION['user_id'], $listing['item_id']]);
    } else {
        $pdo->prepare("INSERT INTO inventory (user_id, item_id, quantity) VALUES (?, ?, ?)")
            ->execute([$_SESSION['user_id'], $listing['item_id'], $listing['quantity']]);
    }
    
    $pdo->prepare("UPDATE market_listings SET status = 'sold', buyer_id = ?, sold_at = NOW() WHERE id = ?")
        ->execute([$_SESSION['user_id'], $listingId]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Покупка совершена']);
}

/**
 * Cancel market listing
 */
function cancelMarketListing(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $listingId = (int)$_POST['listing_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM market_listings WHERE id = ? AND seller_id = ? AND status = 'active'");
    $stmt->execute([$listingId, $_SESSION['user_id']]);
    $listing = $stmt->fetch();
    
    if (!$listing) {
        echo json_encode(['error' => 'Лот не найден']);
        return;
    }
    
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("SELECT id FROM inventory WHERE user_id = ? AND item_id = ?");
    $stmt->execute([$_SESSION['user_id'], $listing['item_id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $pdo->prepare("UPDATE inventory SET quantity = quantity + ? WHERE user_id = ? AND item_id = ?")
            ->execute([$listing['quantity'], $_SESSION['user_id'], $listing['item_id']]);
    } else {
        $pdo->prepare("INSERT INTO inventory (user_id, item_id, quantity) VALUES (?, ?, ?)")
            ->execute([$_SESSION['user_id'], $listing['item_id'], $listing['quantity']]);
    }
    
    $pdo->prepare("UPDATE market_listings SET status = 'cancelled' WHERE id = ?")->execute([$listingId]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Лот снят с продажи']);
}

/**
 * Get my market listings
 */
function getMyMarketListings(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT ml.*, it.name as item_name, it.type, it.rarity, 
        CASE WHEN ml.status = 'sold' THEN (SELECT username FROM users WHERE id = ml.buyer_id) ELSE NULL END as buyer_name
        FROM market_listings ml 
        JOIN items it ON ml.item_id = it.id 
        WHERE ml.seller_id = ? 
        ORDER BY ml.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode($stmt->fetchAll());
}
