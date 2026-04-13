/**
 * Medieval Realm RPG - Admin Module
 */

var currentAdminTab = 'users';

// Admin functions
function adminTab(tab) {
    currentAdminTab = tab;
    document.querySelectorAll('.admin-tab').forEach(function(b) { b.classList.remove('active'); });
    event.target.classList.add('active');
    document.querySelectorAll('.admin-content').forEach(function(c) { c.style.display = 'none'; });
    document.getElementById('admin-' + tab).style.display = 'block';
    
    var loads = { 
        users: loadAdminUsers, 
        items: loadAdminItems, 
        enemies: loadAdminEnemies, 
        quests: loadAdminQuests, 
        guilds: loadAdminGuilds, 
        raids: loadAdminRaids, 
        stats: loadAdminStats 
    };
    if (loads[tab]) loads[tab]();
}

function adminApi(action, data, callback) {
    var formData = new FormData();
    formData.append('action', action);
    for (var key in data) formData.append(key, data[key]);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api.php', true);
    xhr.onload = function() {
        try { callback(JSON.parse(xhr.responseText)); } 
        catch(e) { alert('Error'); }
    };
    xhr.send(formData);
}

// Users
function loadAdminUsers() { adminApi('adminGetUsers', {}, function(r) { renderAdminUsers(r.users || []); }); }
function renderAdminUsers(list) {
    var tbody = document.getElementById('admin-users-body');
    if (!tbody) return;
    tbody.innerHTML = list.length ? list.map(function(u) { 
        return '<tr><td>'+u.id+'</td><td>'+u.username+'</td><td>'+u.class+'</td><td>'+u.level+'</td><td>'+u.hp+'/'+u.max_hp+'</td><td>'+u.gold+'</td><td>'+u.pvp_rating+'</td><td><button onclick="editUser('+u.id+')">✏</button><button onclick="deleteUser('+u.id+')">🗑</button></td></tr>'; 
    }).join('') : '<tr><td colspan="8">Нет игроков</td></tr>';
}
function editUser(id) {
    var f = prompt('Поле (level, gold, crystals, hp, atk, def):'), v = prompt('Значение:');
    if (f && v) { 
        var d = { user_id: id }; 
        d[f] = isNaN(v) ? v : parseInt(v); 
        adminApi('adminUpdateUser', d, function(r) { 
            alert(r.success ? 'Сохранено!' : r.error); 
            loadAdminUsers(); 
        }); 
    }
}
function deleteUser(id) { if (confirm('Удалить игрока?')) adminApi('adminDeleteUser', { user_id: id }, function(r) { loadAdminUsers(); }); }

// Items
function loadAdminItems() { adminApi('adminGetItems', {}, function(r) { renderAdminItems(r.items || []); }); }
function renderAdminItems(list) {
    var tbody = document.getElementById('admin-items-body');
    if (!tbody) return;
    tbody.innerHTML = list.map(function(i) { 
        return '<tr><td>'+i.id+'</td><td>'+i.name+'</td><td>'+i.type+'</td><td>'+i.rarity+'</td><td>'+i.value+'</td><td>'+i.atk_bonus+'</td><td>'+i.def_bonus+'</td><td><button onclick="deleteItem('+i.id+')">🗑</button></td></tr>'; 
    }).join('');
}
function deleteItem(id) { if (confirm('Удалить предмет?')) adminApi('adminDeleteItem', { item_id: id }, function(r) { loadAdminItems(); }); }

// Enemies
function loadAdminEnemies() { adminApi('adminGetEnemies', {}, function(r) { renderAdminEnemies(r.enemies || []); }); }
function renderAdminEnemies(list) {
    var tbody = document.getElementById('admin-enemies-body');
    if (!tbody) return;
    tbody.innerHTML = list.map(function(e) { 
        return '<tr><td>'+e.id+'</td><td>'+(e.is_boss?'👑 ':'')+e.name+'</td><td>'+e.level+'</td><td>'+e.hp+'</td><td>'+e.atk+'</td><td>'+e.def+'</td><td>'+e.exp_reward+'</td><td>'+e.gold_reward+'</td><td>'+(e.is_boss?'Да':'Нет')+'</td><td><button onclick="deleteEnemy('+e.id+')">🗑</button></td></tr>'; 
    }).join('');
}
function deleteEnemy(id) { if (confirm('Удалить монстра?')) adminApi('adminDeleteEnemy', { enemy_id: id }, function(r) { loadAdminEnemies(); }); }

// Quests
function loadAdminQuests() { adminApi('adminGetQuests', {}, function(r) { renderAdminQuests(r.quests || []); }); }
function renderAdminQuests(list) {
    var tbody = document.getElementById('admin-quests-body');
    if (!tbody) return;
    tbody.innerHTML = list.map(function(q) { 
        return '<tr><td>'+q.id+'</td><td>'+q.title+'</td><td>'+q.type+'</td><td>'+q.target_id+'</td><td>'+q.exp_reward+'</td><td>'+q.gold_reward+'</td><td>'+q.required_level+'</td><td><button onclick="deleteQuest('+q.id+')">🗑</button></td></tr>'; 
    }).join('');
}
function deleteQuest(id) { if (confirm('Удалить квест?')) adminApi('adminDeleteQuest', { quest_id: id }, function(r) { loadAdminQuests(); }); }

// Guilds
function loadAdminGuilds() { adminApi('adminGetGuilds', {}, function(r) { renderAdminGuilds(r.guilds || []); }); }
function renderAdminGuilds(list) {
    var tbody = document.getElementById('admin-guilds-body');
    if (!tbody) return;
    tbody.innerHTML = list.map(function(g) { 
        return '<tr><td>'+g.id+'</td><td>'+g.name+'</td><td>'+(g.leader_name||'')+'</td><td>'+g.level+'</td><td>'+g.exp+'</td><td>'+g.gold+'</td><td><button onclick="deleteGuild('+g.id+')">🗑</button></td></tr>'; 
    }).join('');
}
function deleteGuild(id) { if (confirm('Удалить гильдию?')) adminApi('adminDeleteGuild', { guild_id: id }, function(r) { loadAdminGuilds(); }); }

// Raids
function loadAdminRaids() { adminApi('adminGetRaids', {}, function(r) { renderAdminRaids(r.raids || []); }); }
function renderAdminRaids(list) {
    var tbody = document.getElementById('admin-raids-body');
    if (!tbody) return;
    tbody.innerHTML = list.map(function(r) { 
        return '<tr><td>'+r.id+'</td><td>🔥 '+r.name+'</td><td>'+r.level+'</td><td>'+r.hp+'</td><td>'+r.atk+'</td><td>'+r.exp_reward+'</td><td>'+r.gold_reward+'</td><td>'+r.participants_limit+'</td><td>'+(r.is_active?'Да':'Нет')+'</td><td><button onclick="deleteRaid('+r.id+')">🗑</button></td></tr>'; 
    }).join('');
}
function deleteRaid(id) { if (confirm('Удалить рейд?')) adminApi('adminDeleteRaid', { raid_id: id }, function(r) { loadAdminRaids(); }); }

// Stats
function loadAdminStats() { adminApi('adminGetStats', {}, function(r) { 
    if (r.success && r.stats) { 
        var s = r.stats; 
        document.getElementById('stat-total-users').textContent = s.total_users; 
        document.getElementById('stat-pvp-wins').textContent = s.pvp_wins; 
        document.getElementById('stat-total-gold').textContent = s.total_gold; 
        document.getElementById('stat-total-guilds').textContent = s.total_guilds; 
        document.getElementById('stat-total-quests').textContent = s.total_quests; 
        document.getElementById('stat-total-raids').textContent = s.total_raids; 
    } 
}); }

// Alias
var loadStats = loadAdminStats;

// Item form functions
function showItemForm() { document.getElementById('admin-item-form').classList.add('show'); }
function hideItemForm() { document.getElementById('admin-item-form').classList.remove('show'); }
function saveItem() {
    var d = { 
        name: document.getElementById('item-name').value, 
        type: document.getElementById('item-type').value, 
        rarity: document.getElementById('item-rarity').value, 
        value: parseInt(document.getElementById('item-value').value) || 10, 
        required_level: parseInt(document.getElementById('item-required-level').value) || 1, 
        atk_bonus: parseInt(document.getElementById('item-atk-bonus').value) || 0, 
        def_bonus: 0, 
        hp_bonus: 0, 
        mp_bonus: 0, 
        description: '' 
    };
    adminApi('adminSaveItem', d, function(r) { 
        alert(r.success ? 'Сохранено!' : r.error); 
        hideItemForm(); 
        loadAdminItems(); 
    });
}

// Raid form functions
function showRaidForm() { showToast('Форма рейда скоро!', 'info'); }

// Show admin panel
function showAdmin() {
    var u = prompt('Логин:'), p = prompt('Пароль:');
    if (!u || !p) return;
    var fd = new FormData(); 
    fd.append('action', 'adminLogin'); 
    fd.append('username', u); 
    fd.append('password', p);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api.php', true);
    xhr.onload = function() {
        try {
            var r = JSON.parse(xhr.responseText);
            if (r.success) {
                router('admin');
                loadAdminUsers();
            } else { alert(r.error || 'Error'); }
        } catch(e) { alert('Error: ' + e.message); }
    };
    xhr.send(fd);
}

// Expose to window
window.adminTab = adminTab;
window.loadAdminUsers = loadAdminUsers;
window.loadUsers = loadAdminUsers; // alias
window.loadAdminItems = loadAdminItems;
window.loadItems = loadAdminItems; // alias
window.loadAdminEnemies = loadAdminEnemies;
window.loadEnemies = loadAdminEnemies; // alias
window.loadAdminQuests = loadAdminQuests;
window.loadAdminGuilds = loadAdminGuilds;
window.loadAdminRaids = loadAdminRaids;
window.loadRaids = loadAdminRaids; // alias
window.loadAdminStats = loadAdminStats;
window.loadStats = loadStats;
window.showAdmin = showAdmin;
window.showItemForm = showItemForm;
window.hideItemForm = hideItemForm;
window.saveItem = saveItem;
window.showRaidForm = showRaidForm;
window.editUser = editUser;
window.deleteUser = deleteUser;
window.deleteItem = deleteItem;
window.deleteEnemy = deleteEnemy;
window.deleteQuest = deleteQuest;
window.deleteGuild = deleteGuild;
window.deleteRaid = deleteRaid;

// Also expose adminTab for admin-section.php
window.adminTab = adminTab;