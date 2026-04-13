/**
 * Medieval Realm RPG - SHOP & INVENTORY
 * Shop and inventory management
 */

// ================================
// SHOP & INVENTORY
// ================================

function loadShop() {
    api('getItems', {}, function(items) {
        var shop = document.getElementById('shop-items');
        if (!shop) return;
        
        if (!items || !items.length) {
            shop.innerHTML = '<p>Нет предметов</p>';
            return;
        }
        
        var html = '';
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            var icon = item.type === 'weapon' ? '⚔️' : (item.type === 'armor' ? '🛡️' : '🧪');
            html += '<div class="item-card">' +
                '<div class="item-icon">' + icon + '</div>' +
                '<div class="item-name">' + item.name + '</div>' +
                '<div class="item-price">💰 ' + item.value + '</div>' +
                '<button class="btn-buy" onclick="buyItem(' + item.id + ')">Купить</button></div>';
        }
        shop.innerHTML = html;
    });
}

function buyItem(itemId) {
    api('buyItem', { item_id: itemId }, function(result) {
        if (result.success) {
            showToast('Предмет куплен!', 'success');
            refreshUser();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function loadInventory() {
    api('getInventory', {}, function(items) {
        currentInventory = items || [];
        renderInventory();
    });
}

function renderInventory() {
    var container = document.getElementById('inventory-list');
    if (!container) {
        container = document.getElementById('inventory-grid');
    }
    
    if (!container) return;
    
    if (!currentInventory || !currentInventory.length) {
        container.innerHTML = '<div class="empty-state">Инвентарь пуст</div>';
        return;
    }
    
    var html = '';
    for (var i = 0; i < currentInventory.length; i++) {
        var inv = currentInventory[i];
        var item = inv.item;
        if (!item) continue;
        
        var icon = item.type === 'weapon' ? '⚔️' : 
                 item.type === 'armor' ? '🛡️' : 
                 item.type === 'potion' ? '🧪' : 
                 item.type === 'food' ? '🍖' : '📦';
        
        var equipped = inv.equipped ? ' (экипировано)' : '';
        var quantity = inv.quantity > 1 ? ' x' + inv.quantity : '';
        
        html += '<div class="inv-item"' + (inv.equipped ? ' equipped' : '') + '>' +
            '<div class="inv-icon">' + icon + '</div>' +
            '<div class="inv-info">' +
                '<div class="inv-name">' + item.name + equipped + quantity + '</div>' +
                '<div class="inv-stats">' + getItemStats(item) + '</div>' +
                '<div class="inv-actions">' +
                    (!inv.equipped ? '<button onclick="equipItem(' + inv.id + ')">Экипировать</button>' : '<button onclick="unequipItem(' + inv.id + ')">Снять</button>') +
                    (item.type === 'potion' || item.type === 'food' ? '<button onclick="useItem(' + inv.id + ')">Использовать</button>' : '') +
                '</div>' +
            '</div>' +
        '</div>';
    }
    
    container.innerHTML = html;
}

function getItemStats(item) {
    var stats = [];
    if (item.atk_bonus > 0) stats.push('⚔️ +' + item.atk_bonus);
    if (item.def_bonus > 0) stats.push('🛡️ +' + item.def_bonus);
    if (item.hp_bonus > 0) stats.push('❤️ +' + item.hp_bonus);
    if (item.mp_bonus > 0) stats.push('💧 +' + item.mp_bonus);
    return stats.join(' ') || item.type;
}

function equipItem(inventoryId) {
    api('equipItem', { inventory_id: inventoryId }, function(result) {
        if (result.success) {
            showToast('Предмет экипирован!', 'success');
            loadInventory();
            loadCharacter();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function unequipItem(inventoryId) {
    api('unequipItem', { inventory_id: inventoryId }, function(result) {
        if (result.success) {
            showToast('Предмет снят!', 'success');
            loadInventory();
            loadCharacter();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function useItem(inventoryId) {
    api('useItem', { inventory_id: inventoryId }, function(result) {
        if (result.success) {
            showToast('Предмет использован!', 'success');
            refreshUser();
            loadInventory();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

// Expose to window
window.loadShop = loadShop;
window.buyItem = buyItem;
window.loadInventory = loadInventory;
window.renderInventory = renderInventory;
window.getItemStats = getItemStats;
window.equipItem = equipItem;
window.unequipItem = unequipItem;
window.useItem = useItem;