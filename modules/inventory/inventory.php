<?php
/**
 * Inventory Module - Items, Equipment, Shop, Skills
 * Medieval Realm RPG
 */

if (!defined('GAME_MODULE')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Get player inventory
 */
function getInventory(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT i.*, it.name, it.type, it.rarity, it.value, it.atk_bonus, it.def_bonus, it.mp_bonus, it.hp_bonus, it.required_level 
        FROM inventory i 
        JOIN items it ON i.item_id = it.id 
        WHERE i.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode($stmt->fetchAll());
}

/**
 * Get shop items
 */
function getItems(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM items WHERE is_shop_item = 1 ORDER BY value");
    echo json_encode($stmt->fetchAll());
}

/**
 * Buy item from shop
 */
function buyItem(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $itemId = (int)$_POST['item_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND is_shop_item = 1");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();
    
    if (!$item) {
        echo json_encode(['error' => 'Предмет не найден']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT gold, level FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user['gold'] < $item['value']) {
        echo json_encode(['error' => 'Недостаточно золота']);
        return;
    }
    
    if ($user['level'] < $item['required_level']) {
        echo json_encode(['error' => 'Недостаточный уровень']);
        return;
    }
    
    $pdo->beginTransaction();
    
    $pdo->prepare("UPDATE users SET gold = gold - ? WHERE id = ?")->execute([$item['value'], $_SESSION['user_id']]);
    
    $stmt = $pdo->prepare("SELECT id FROM inventory WHERE user_id = ? AND item_id = ?");
    $stmt->execute([$_SESSION['user_id'], $itemId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $pdo->prepare("UPDATE inventory SET quantity = quantity + 1 WHERE user_id = ? AND item_id = ?")->execute([$_SESSION['user_id'], $itemId]);
    } else {
        $pdo->prepare("INSERT INTO inventory (user_id, item_id, quantity) VALUES (?, ?, 1)")->execute([$_SESSION['user_id'], $itemId]);
    }
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Предмет куплен']);
}

/**
 * Equip item
 */
function equipItem(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $itemId = (int)$_POST['item_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT i.*, it.type, it.atk_bonus, it.def_bonus, it.mp_bonus, it.hp_bonus FROM inventory i JOIN items it ON i.item_id = it.id WHERE i.user_id = ? AND i.id = ?");
    $stmt->execute([$_SESSION['user_id'], $itemId]);
    $invItem = $stmt->fetch();
    
    if (!$invItem) {
        echo json_encode(['error' => 'Предмет не найден']);
        return;
    }
    
    $slotMap = [
        'weapon' => 'weapon',
        'armor' => 'armor',
        'helmet' => 'helmet',
        'boots' => 'boots',
        'ring' => 'accessory',
        'amulet' => 'accessory'
    ];
    
    $slot = $slotMap[$invItem['type']] ?? null;
    if (!$slot) {
        echo json_encode(['error' => 'Нельзя экипировать этот предмет']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM user_equipment WHERE user_id = ? AND slot = ?");
    $stmt->execute([$_SESSION['user_id'], $slot]);
    $equipped = $stmt->fetch();
    
    $pdo->beginTransaction();
    
    if ($equipped) {
        $stmt = $pdo->prepare("SELECT it.* FROM user_equipment ue JOIN items it ON ue.item_id = it.id WHERE ue.user_id = ? AND ue.slot = ?");
        $stmt->execute([$_SESSION['user_id'], $slot]);
        $oldItem = $stmt->fetch();
        
        $atkDiff = $invItem['atk_bonus'] - ($oldItem['atk_bonus'] ?? 0);
        $defDiff = $invItem['def_bonus'] - ($oldItem['def_bonus'] ?? 0);
        $mpDiff = $invItem['mp_bonus'] - ($oldItem['mp_bonus'] ?? 0);
        $hpDiff = $invItem['hp_bonus'] - ($oldItem['hp_bonus'] ?? 0);
        
        $pdo->prepare("UPDATE users SET atk = atk + ?, def = def + ?, mp = mp + ?, max_mp = max_mp + ?, hp = hp + ?, max_hp = max_hp + ? WHERE id = ?")
            ->execute([$atkDiff, $defDiff, $mpDiff, $mpDiff, $hpDiff, $hpDiff, $_SESSION['user_id']]);
        
        $stmt = $pdo->prepare("SELECT id FROM inventory WHERE user_id = ? AND item_id = ?");
        $stmt->execute([$_SESSION['user_id'], $equipped['item_id']]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $pdo->prepare("UPDATE inventory SET quantity = quantity + 1 WHERE user_id = ? AND item_id = ?")->execute([$_SESSION['user_id'], $equipped['item_id']]);
        } else {
            $pdo->prepare("INSERT INTO inventory (user_id, item_id, quantity) VALUES (?, ?, 1)")->execute([$_SESSION['user_id'], $equipped['item_id']]);
        }
        
        $pdo->prepare("UPDATE user_equipment SET item_id = ? WHERE user_id = ? AND slot = ?")->execute([$itemId, $_SESSION['user_id'], $slot]);
    } else {
        $pdo->prepare("UPDATE users SET atk = atk + ?, def = def + ?, mp = mp + ?, max_mp = max_mp + ?, hp = hp + ?, max_hp = max_hp + ? WHERE id = ?")
            ->execute([$invItem['atk_bonus'], $invItem['def_bonus'], $invItem['mp_bonus'], $invItem['mp_bonus'], $invItem['hp_bonus'], $invItem['hp_bonus'], $_SESSION['user_id']]);
        
        $pdo->prepare("INSERT INTO user_equipment (user_id, slot, item_id) VALUES (?, ?, ?)")->execute([$_SESSION['user_id'], $slot, $itemId]);
    }
    
    $pdo->prepare("DELETE FROM inventory WHERE user_id = ? AND id = ?")->execute([$_SESSION['user_id'], $itemId]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Предмет экипирован']);
}

/**
 * Unequip item
 */
function unequipItem(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $slot = $_POST['slot'] ?? '';
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM user_equipment WHERE user_id = ? AND slot = ?");
    $stmt->execute([$_SESSION['user_id'], $slot]);
    $equipped = $stmt->fetch();
    
    if (!$equipped) {
        echo json_encode(['error' => 'Слот пуст']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$equipped['item_id']]);
    $item = $stmt->fetch();
    
    $pdo->beginTransaction();
    
    $pdo->prepare("UPDATE users SET atk = atk - ?, def = def - ?, mp = mp - ?, max_mp = max_mp - ?, hp = hp - ?, max_hp = max_hp - ? WHERE id = ?")
        ->execute([$item['atk_bonus'], $item['def_bonus'], $item['mp_bonus'], $item['mp_bonus'], $item['hp_bonus'], $item['hp_bonus'], $_SESSION['user_id']]);
    
    $stmt = $pdo->prepare("SELECT id FROM inventory WHERE user_id = ? AND item_id = ?");
    $stmt->execute([$_SESSION['user_id'], $equipped['item_id']]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $pdo->prepare("UPDATE inventory SET quantity = quantity + 1 WHERE user_id = ? AND item_id = ?")->execute([$_SESSION['user_id'], $equipped['item_id']]);
    } else {
        $pdo->prepare("INSERT INTO inventory (user_id, item_id, quantity) VALUES (?, ?, 1)")->execute([$_SESSION['user_id'], $equipped['item_id']]);
    }
    
    $pdo->prepare("DELETE FROM user_equipment WHERE user_id = ? AND slot = ?")->execute([$_SESSION['user_id'], $slot]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Предмет снят']);
}

/**
 * Use item (consumable)
 */
function useItem(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $itemId = (int)$_POST['item_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT i.*, it.type, it.hp_bonus, it.mp_bonus FROM inventory i JOIN items it ON i.item_id = it.id WHERE i.user_id = ? AND i.id = ?");
    $stmt->execute([$_SESSION['user_id'], $itemId]);
    $invItem = $stmt->fetch();
    
    if (!$invItem || $invItem['type'] !== 'potion') {
        echo json_encode(['error' => 'Нельзя использовать']);
        return;
    }
    
    $healAmount = $invItem['hp_bonus'] ?: 50;
    $manaAmount = $invItem['mp_bonus'] ?: 0;
    
    $message = [];
    if ($healAmount > 0) {
        $pdo->prepare("UPDATE users SET hp = LEAST(max_hp, hp + ?) WHERE id = ?")->execute([$healAmount, $_SESSION['user_id']]);
        $message[] = "Восстановлено {$healAmount} HP";
    }
    if ($manaAmount > 0) {
        $pdo->prepare("UPDATE users SET mp = LEAST(max_mp, mp + ?) WHERE id = ?")->execute([$manaAmount, $_SESSION['user_id']]);
        $message[] = "Восстановлено {$manaAmount} MP";
    }
    
    if ($invItem['quantity'] > 1) {
        $pdo->prepare("UPDATE inventory SET quantity = quantity - 1 WHERE user_id = ? AND id = ?")->execute([$_SESSION['user_id'], $itemId]);
    } else {
        $pdo->prepare("DELETE FROM inventory WHERE user_id = ? AND id = ?")->execute([$_SESSION['user_id'], $itemId]);
    }
    
    echo json_encode(['success' => true, 'message' => implode(', ', $message)]);
}

/**
 * Get skills list
 */
function getSkills(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    $skills = [
        ['id' => 1, 'name' => 'Сильный удар', 'description' => 'Наносит 150% урона', 'cooldown' => 3, 'mp_cost' => 10],
        ['id' => 2, 'name' => 'Лечение', 'description' => 'Восстанавливает 50 HP', 'cooldown' => 5, 'mp_cost' => 15],
        ['id' => 3, 'name' => 'Щит', 'description' => '+50% защиты на 2 хода', 'cooldown' => 6, 'mp_cost' => 20]
    ];
    
    echo json_encode($skills);
}

/**
 * Get locations
 */
function getLocations(): void {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM locations ORDER BY id");
    echo json_encode($stmt->fetchAll());
}

/**
 * Get battle licenses
 */
function getLicenses(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT ul.*, l.name, l.description, l.price FROM user_battle_licenses ul JOIN battle_licenses l ON ul.license_id = l.id WHERE ul.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode($stmt->fetchAll());
}

/**
 * Buy license
 */
function buyLicense(): void {
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        return;
    }
    
    $licenseId = (int)$_POST['license_id'];
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM battle_licenses WHERE id = ?");
    $stmt->execute([$licenseId]);
    $license = $stmt->fetch();
    
    if (!$license) {
        echo json_encode(['error' => 'Лицензия не найдена']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT gold FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user['gold'] < $license['price']) {
        echo json_encode(['error' => 'Недостаточно золота']);
        return;
    }
    
    $pdo->prepare("UPDATE users SET gold = gold - ? WHERE id = ?")->execute([$license['price'], $_SESSION['user_id']]);
    $pdo->prepare("INSERT INTO user_battle_licenses (user_id, license_id, attacks_remaining, expires_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))")
        ->execute([$_SESSION['user_id'], $licenseId, $license['attacks_per_day']]);
    
    echo json_encode(['success' => true, 'message' => 'Лицензия куплена']);
}
