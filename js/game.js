/**
 * Medieval Realm RPG - JavaScript Client
 * MAIN CORE FILE
 * Версия: 3.0.0
 */
var GAME_VERSION = '3.0.0';
console.log('Medieval Realm v' + GAME_VERSION + ' loaded');

// ================================
// СИСТЕМА ОТЛАДКИ И ОШИБОК
// ================================

var DEBUG_MODE = true;
var errors = [];

function logError(context, message, details) {
    var error = {
        time: new Date().toLocaleTimeString(),
        context: context,
        message: message,
        details: details || null
    };
    errors.push(error);
    console.error('[ERROR] ' + context + ': ' + message, details);
    
    if (DEBUG_MODE && typeof showToast === 'function') {
        showToast('⚠️ ' + context + ': ' + message, 'error');
    }
    
    updateDebugPanel();
}

function updateDebugPanel() {
    var panel = document.getElementById('debug-panel');
    if (!panel) return;
    
    if (errors.length === 0) {
        panel.innerHTML = '<div class="debug-empty">✅ Нет ошибок</div>';
        return;
    }
    
    var html = '<div class="debug-header">🔧 Отладка (' + errors.length + ' ошибок)</div>';
    errors.slice(-10).forEach(function(e) {
        html += '<div class="debug-error">';
        html += '<span class="debug-time">' + e.time + '</span>';
        html += '<span class="debug-context">' + e.context + '</span>';
        html += '<span class="debug-msg">' + e.message + '</span>';
        html += '</div>';
    });
    
    panel.innerHTML = html;
}

window.onerror = function(msg, url, line, col, error) {
    logError('JS Error', msg, 'Line: ' + line);
    return false;
};

window.onunhandledrejection = function(event) {
    logError('Promise', 'Unhandled rejection: ' + event.reason);
};

console.log = (function(orig) {
    return function() {
        if (arguments[0] && typeof arguments[0] === 'string' && arguments[0].indexOf('[DEBUG]') === 0) {
            if (DEBUG_MODE) orig.apply(console, arguments);
        } else {
            orig.apply(console, arguments);
        }
    };
})(console.log);

var API_URL = '';
var currentUser = null;
var currentBattle = null;
var currentAttackType = 'normal';
var currentInventory = [];
var selectedItem = null;
var temporaryItemsTimer = null;

// ================================
// ИНИЦИАЛИЗАЦИЯ
// ================================

function init() {
    try {
        hideLoader();
        createToastContainer();
        createDebugPanel();
        console.log('[DEBUG] Инициализация завершена');
    } catch(e) {
        logError('Init', e.message);
    }
}

function createDebugPanel() {
    if (document.getElementById('debug-panel')) return;
    
    var panel = document.createElement('div');
    panel.id = 'debug-panel';
    panel.className = 'debug-panel';
    panel.innerHTML = '<div class="debug-empty">✅ Загрузка...</div>';
    document.body.appendChild(panel);
}

function hideLoader() {
    var loader = document.getElementById('page-loader');
    if (loader) {
        loader.classList.add('hidden');
        setTimeout(function() { loader.remove(); }, 500);
    }
}

// ================================
// TOAST
// ================================

function createToastContainer() {
    if (document.getElementById('toast-container')) return;
    var container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
}

function showToast(message, type) {
    type = type || 'info';
    var container = document.getElementById('toast-container');
    if (!container) {
        createToastContainer();
        return showToast(message, type);
    }
    var toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.innerHTML = message;
    container.appendChild(toast);
    setTimeout(function() {
        toast.style.animation = 'fadeOut 0.3s ease forwards';
        setTimeout(function() { toast.remove(); }, 300);
    }, 3500);
}

// ================================
// API
// ================================

function api(action, data, callback) {
    var formData = new FormData();
    formData.append('action', action);
    for (var key in data) {
        formData.append(key, data[key]);
    }
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api.php', true);
    
    xhr.onerror = function() {
        logError('API', 'Network error: ' + action);
        showToast('Ошибка сети', 'error');
    };
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 0) {
                logError('API', 'Connection failed: ' + action);
                return;
            }
            if (xhr.status !== 200) {
                logError('API', 'HTTP ' + xhr.status + ': ' + action);
                showToast('Ошибка сервера: ' + xhr.status, 'error');
                return;
            }
            try {
                var result = JSON.parse(xhr.responseText);
                if (result.error) {
                    logError('API', result.error, { action: action });
                    showToast(result.error, 'error');
                }
                if (callback) callback(result);
            } catch(e) {
                logError('API', 'JSON parse error: ' + e.message, { response: xhr.responseText.substring(0, 100) });
                showToast('Ошибка: ' + e.message, 'error');
            }
        }
    };
    xhr.send(formData);
}

// ================================
// AUTH
// ================================

function showAuth(type) {
    document.querySelectorAll('.auth-tab').forEach(function(t) { t.classList.remove('active'); });
    var tabEl = document.querySelector('.auth-tab:nth-child(' + (type === 'login' ? 1 : 2) + ')');
    if (tabEl) tabEl.classList.add('active');
    
    document.getElementById('login-form').style.display = type === 'login' ? 'block' : 'none';
    document.getElementById('register-form').style.display = type === 'register' ? 'block' : 'none';
}

function register() {
    var regUser = document.getElementById('reg-username');
    var username = regUser ? regUser.value.trim() : '';
    var regPass = document.getElementById('reg-password');
    var password = regPass ? regPass.value : '';
    var regCls = document.getElementById('reg-class');
    var cls = regCls ? regCls.value : 'warrior';
    var regRace = document.getElementById('reg-race');
    var race = regRace ? regRace.value : 'human';
    var errEl = document.getElementById('auth-error');
    
    if (!username || username.length < 3) {
        if (errEl) errEl.textContent = 'Имя минимум 3 символа';
        return;
    }
    if (!password || password.length < 4) {
        if (errEl) errEl.textContent = 'Пароль минимум 4 символа';
        return;
    }
    
    if (errEl) errEl.textContent = '';
    
    api('register', { username: username, password: password, class: cls, race: race }, function(result) {
        if (result.success) {
            if (errEl) errEl.style.color = '#10b981';
            if (errEl) errEl.textContent = 'Персонаж создан! Теперь войдите.';
            setTimeout(function() { showAuth('login'); }, 1500);
        } else {
            if (errEl) errEl.style.color = '#ef4444';
            if (errEl) errEl.textContent = result.error || 'Ошибка регистрации';
        }
    });
}

function updateClassPreview() {
    var cls = document.getElementById('reg-class');
    var clsName = cls ? cls.value : 'warrior';
    
    var stats = {
        warrior: { hp: 120, mp: 40, atk: 12, def: 8, desc: 'Сильный воин с высоким HP' },
        mage: { hp: 80, mp: 100, atk: 8, def: 4, desc: 'Мастер магии с большим MP' },
        archer: { hp: 90, mp: 60, atk: 14, def: 5, desc: 'Ловкий лучник с высокой атакой' },
        rogue: { hp: 85, mp: 50, atk: 15, def: 4, desc: 'Быстрый плут с критическим уроном' },
        paladin: { hp: 110, mp: 70, atk: 10, def: 10, desc: 'Святой воин с балансом' }
    };
    
    var s = stats[clsName] || stats.warrior;
    var grid = document.querySelector('.stats-grid');
    if (grid) {
        grid.innerHTML = '<div class="stat-item"><span>❤️ HP</span><strong>' + s.hp + '</strong></div>' +
            '<div class="stat-item"><span>💧 MP</span><strong>' + s.mp + '</strong></div>' +
            '<div class="stat-item"><span>⚔️ АТК</span><strong>' + s.atk + '</strong></div>' +
            '<div class="stat-item"><span>🛡️ ЗАЩ</span><strong>' + s.def + '</strong></div>';
    }
    var desc = document.querySelector('.class-desc');
    if (desc) desc.textContent = s.desc;
}

function login() {
    var loginUser = document.getElementById('login-username');
    var username = loginUser ? loginUser.value.trim() : '';
    var loginPass = document.getElementById('login-password');
    var password = loginPass ? loginPass.value : '';
    
    if (!username || !password) {
        showToast('Введите логин и пароль', 'error');
        return;
    }
    
    showToast('Вход...', 'info');
    
    api('login', { username: username, password: password }, function(result) {
        if (result.success) {
            currentUser = result.user;
            showGame();
        } else {
            showToast(result.error || 'Ошибка входа', 'error');
        }
    });
}

function logout() {
    api('logout', {}, function() {
        currentUser = null;
        document.getElementById('auth-section').style.display = 'flex';
        document.getElementById('game-layout').style.display = 'none';
    });
}

function showGame() {
    document.getElementById('auth-section').style.display = 'none';
    document.getElementById('game-layout').style.display = 'flex';
    loadCharacter();
    updateUserStats();
    showSection('character');
    setInterval(refreshUser, 10000);
    
    loadChat();
}

function refreshUser() {
    api('getUser', {}, function(user) {
        if (user && !user.error) {
            currentUser = user;
            updateUserStats();
        }
    });
}

function updateUserStats() {
    if (!currentUser) return;
    var goldEl = document.getElementById('header-gold');
    if (goldEl) goldEl.textContent = currentUser.gold;
    var crysEl = document.getElementById('header-crystals');
    if (crysEl) crysEl.textContent = currentUser.crystals;
    var ratEl = document.getElementById('header-rating');
    if (ratEl) ratEl.textContent = currentUser.pvp_rating;
    var lvlEl = document.getElementById('header-level');
    if (lvlEl) lvlEl.textContent = currentUser.level;
    var nameEl = document.getElementById('header-name');
    if (nameEl) nameEl.textContent = currentUser.username;
}

function loadCharacter() {
    if (!currentUser) return;
    
    var elements = {
        'char-name': currentUser.username,
        'char-class': currentUser.class,
        'char-race': currentUser.race,
        'char-level': currentUser.level,
        'char-hp': currentUser.hp,
        'char-max-hp': currentUser.max_hp,
        'char-mp': currentUser.mp,
        'char-max-mp': currentUser.max_mp,
        'char-atk': currentUser.atk,
        'char-def': currentUser.def,
        'char-gold': currentUser.gold,
        'char-crystals': currentUser.crystals,
        'char-pvp-rating': currentUser.pvp_rating,
        'char-pvp-wins': currentUser.pvp_wins,
        'char-str': currentUser.str,
        'char-dex': currentUser.dex,
        'char-int': currentUser.intel,
        'char-vit': currentUser.vit,
        'char-luck': currentUser.luck
    };
    
    for (var id in elements) {
        var el = document.getElementById(id);
        if (el) el.textContent = elements[id];
    }
    
    var hpBar = document.getElementById('hp-bar');
    var mpBar = document.getElementById('mp-bar');
    var expBar = document.getElementById('exp-bar');
    if (currentUser.max_hp > 0 && hpBar) hpBar.style.width = (currentUser.hp / currentUser.max_hp * 100) + '%';
    if (currentUser.max_mp > 0 && mpBar) mpBar.style.width = (currentUser.mp / currentUser.max_mp * 100) + '%';
    if (expBar) expBar.style.width = Math.min(100, (currentUser.exp / 100) * 100) + '%';
    
    // Update avatar based on class
    var avatars = { 
        warrior: '⚔️', 
        mage: '🔮', 
        archer: '🏹', 
        rogue: '🗡️', 
        paladin: '🛡️',
        berserker: '⚔️',
        necromancer: '💀',
        assassin: '🗡️'
    };
    var avatarEl = document.getElementById('char-avatar');
    if (avatarEl) avatarEl.textContent = avatars[currentUser.class] || '⚔️';
    
    var classNames = {
        warrior: 'ВОИН',
        mage: 'МАГ',
        archer: 'ЛУЧНИК',
        rogue: 'ПЛУТ',
        paladin: 'ПАЛАДИН'
    };
    var classEl = document.getElementById('char-class');
    if (classEl) classEl.textContent = classNames[currentUser.class] || currentUser.class.toUpperCase();
    
    // Update slot displays
    updateEquipmentSlots();
}

function updateEquipmentSlots() {
    var slots = ['weapon', 'armor', 'helmet', 'boots', 'ring', 'amulet'];
    for (var i = 0; i < slots.length; i++) {
        var slotEl = document.getElementById('slot-' + slots[i]);
        if (slotEl) slotEl.textContent = '-';
    }
}

function showSlotInfo(slotType) {
    var tooltip = document.getElementById('item-tooltip');
    if (!tooltip) return;
    
    // Hide any existing tooltip first
    tooltip.style.display = 'none';
    
    var slotNames = {
        weapon: 'Оружие',
        armor: 'Броня',
        helmet: 'Шлем',
        boots: 'Ботинки',
        ring: 'Кольцо',
        amulet: 'Амулет'
    };
    
    var slotDescriptions = {
        weapon: ' увеличивает атаку',
        armor: ' увеличивает защиту',
        helmet: ' увеличивает HP',
        boots: ' увеличивает ловкость',
        ring: ' увеличивает удачу',
        amulet: ' увеличивает MP'
    };
    
    tooltip.querySelector('.tooltip-name').textContent = slotNames[slotType] || slotType;
    tooltip.querySelector('.tooltip-type').textContent = 'Слот: ' + slotNames[slotType];
    tooltip.querySelector('.tooltip-stats').textContent = 'Наденьте предмет для получения бонуса';
    tooltip.querySelector('.tooltip-desc').textContent = 'Доступно для всех классов';
    tooltip.style.display = 'block';
    
    // Position tooltip near click
    var clickEl = document.querySelector('[data-slot="' + slotType + '"]');
    if (clickEl) {
        var rect = clickEl.getBoundingClientRect();
        tooltip.style.left = rect.left + 'px';
        tooltip.style.top = (rect.bottom + 10) + 'px';
    }
    
    // Hide on click outside
    setTimeout(function() {
        document.addEventListener('click', hideTooltip);
    }, 100);
}

function hideTooltip() {
    var tooltip = document.getElementById('item-tooltip');
    if (tooltip) tooltip.style.display = 'none';
    document.removeEventListener('click', hideTooltip);
}

// ================================
// NAVIGATION
// ================================

function showSection(section) {
    router(section);
}

function toggleSidebar() {
    var sidebar = document.querySelector('.sidebar');
    if (sidebar) sidebar.classList.toggle('open');
}

// ================================
// BATTLE
// ================================

function loadEnemies() {
    var level = currentUser ? currentUser.level : 1;
    api('getEnemies', { level: level }, function(enemies) {
        var list = document.getElementById('enemies-list');
        if (!list) return;
        
        if (!enemies || !enemies.length) {
            list.innerHTML = '<p style="color:var(--text-muted)">Нет монстров</p>';
            return;
        }
        
        var html = '';
        for (var i = 0; i < enemies.length; i++) {
            var e = enemies[i];
            var icon = e.is_boss ? '👑' : '👹';
            html += '<div class="enemy-card" onclick="startBattle(' + e.id + ')">' +
                '<span class="level-badge">🏔️ Ур.' + e.level + '</span>' +
                '<h3>' + icon + ' ' + e.name + '</h3>' +
                '<p>❤️ ' + e.hp + ' ⚔️' + e.atk + ' 🛡️' + e.def + '</p>' +
                '<div class="rewards"><span>⭐ +' + e.exp_reward + '</span><span>💰 +' + e.gold_reward + '</span></div></div>';
        }
        list.innerHTML = html;
    });
}

function startBattle(enemyId) {
    api('startPveBattle', { enemy_id: enemyId }, function(result) {
        if (result.battle_id) {
            currentBattle = {
                id: result.battle_id,
                enemy: result.enemy,
                userHp: result.user_hp,
                enemyHp: result.enemy_hp,
                enemyMaxHp: result.enemy.hp
            };
            showBattleUI();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function showBattleUI() {
    var listView = document.getElementById('enemies-list-view');
    var battleView = document.getElementById('battle-view');
    if (listView) listView.style.display = 'none';
    if (battleView) battleView.style.display = 'block';
    
    var enemyName = document.getElementById('battle-enemy-name');
    var enemyLevel = document.getElementById('battle-enemy-level');
    var enemyAvatar = document.getElementById('battle-enemy-avatar');
    if (currentBattle && currentBattle.enemy) {
        if (enemyName) enemyName.textContent = currentBattle.enemy.name;
        if (enemyLevel) enemyLevel.textContent = currentBattle.enemy.level;
        if (enemyAvatar) enemyAvatar.textContent = currentBattle.enemy.is_boss ? '👑' : '👹';
    }
    updateBattleDisplay();
}

function updateBattleDisplay() {
    if (!currentBattle) return;
    
    var enemy = currentBattle.enemy;
    var enemyEl = document.getElementById('enemy-name');
    if (enemyEl) enemyEl.textContent = enemy.name;
    
    var hpEl = document.getElementById('enemy-hp');
    if (hpEl) hpEl.textContent = currentBattle.enemyHp;
    
    var hpBar = document.getElementById('enemy-hp-bar');
    if (hpBar && enemy.hp > 0) hpBar.style.width = (currentBattle.enemyHp / enemy.hp * 100) + '%';
    
    var playerHpEl = document.getElementById('player-hp');
    if (playerHpEl) playerHpEl.textContent = currentBattle.userHp;
    
    var playerHpBar = document.getElementById('player-hp-bar');
    if (playerHpBar && currentUser) playerHpBar.style.width = (currentBattle.userHp / currentUser.max_hp * 100) + '%';
}

function attack(type) {
    type = type || 'normal';
    api('attack', { battle_id: currentBattle.id, attack_type: type }, function(result) {
        if (result.won) {
            showToast('Победа! +' + result.exp + ' опыта, +' + result.gold + ' золота', 'success');
            cancelBattle();
        } else {
            currentBattle.userHp = result.player_hp;
            currentBattle.enemyHp = result.enemy_hp;
            updateBattleDisplay();
        }
    });
}

function cancelBattle() {
    currentBattle = null;
    var listView = document.getElementById('enemies-list-view');
    var battleView = document.getElementById('battle-view');
    if (listView) listView.style.display = 'block';
    if (battleView) battleView.style.display = 'none';
    loadEnemies();
    refreshUser();
}

function useBattlePotion() {
    var potions = currentInventory.filter(function(i) { return i.item && i.item.type === 'potion' && !i.equipped; });
    if (!potions.length) {
        showToast('Нет зелий!', 'error');
        return;
    }
    api('usePotion', { item_id: potions[0].item.id }, function(result) {
        if (result.success) {
            currentBattle.userHp = Math.min(currentBattle.userHp + result.hp_restored, currentUser.max_hp);
            updateBattleDisplay();
            loadInventory();
        }
    });
}

function fleeBattle() {
    if (!confirm('Вы уверены, что хотите сбежать?')) return;
    currentBattle = null;
    var listView = document.getElementById('enemies-list-view');
    var battleView = document.getElementById('battle-view');
    if (listView) listView.style.display = 'block';
    if (battleView) battleView.style.display = 'none';
    showToast('Вы сбежали!', 'info');
}

function updateBattleDisplay() {
    var enemyHp = document.getElementById('battle-enemy-hp');
    var enemyMaxHp = document.getElementById('battle-enemy-max-hp');
    var enemyHpBar = document.getElementById('battle-enemy-hp-bar');
    var playerHp = document.getElementById('battle-player-hp');
    var playerMaxHp = document.getElementById('battle-player-max-hp');
    var playerHpBar = document.getElementById('battle-player-hp-bar');
    var playerMp = document.getElementById('battle-player-mp');
    var playerMaxMp = document.getElementById('battle-player-max-mp');
    
    if (currentBattle) {
        if (enemyHp) enemyHp.textContent = currentBattle.enemyHp;
        if (enemyMaxHp) enemyMaxHp.textContent = currentBattle.enemyMaxHp;
        if (enemyHpBar) enemyHpBar.style.width = (currentBattle.enemyHp / currentBattle.enemyMaxHp * 100) + '%';
        if (playerHp) playerHp.textContent = currentBattle.userHp;
        if (playerMaxHp) playerMaxHp.textContent = currentUser.max_hp;
        if (playerHpBar) playerHpBar.style.width = (currentBattle.userHp / currentUser.max_hp * 100) + '%';
        if (playerMp) playerMp.textContent = currentUser.mp;
        if (playerMaxMp) playerMaxMp.textContent = currentUser.max_mp;
    }
}

function selectAttackType(type, btn) {
    currentAttackType = type;
    var btns = document.querySelectorAll('.attack-type-btn');
    btns.forEach(function(b) { b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
}

function showEnemyType(type, btn) {
    var tabs = document.querySelectorAll('.selection-tab');
    tabs.forEach(function(b) { b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
    loadEnemies();
}

function showBattleSkills() {
    showToast('Навыки скоро!', 'info');
}

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

// ================================
// LOCATIONS & LICENSES
// ================================

function loadLocations() {
    api('getLocations', {}, function(locations) {
        var container = document.getElementById('locations-grid');
        if (!container) return;
        
        if (!locations || !locations.length) {
            container.innerHTML = '<p>Нет локаций</p>';
            return;
        }
        
        var html = '';
        for (var i = 0; i < locations.length; i++) {
            var l = locations[i];
            html += '<div class="location-card" onclick="travelTo(' + l.id + ')">' +
                '<div class="location-name">' + l.name + '</div>' +
                '<div class="location-level">Уровень ' + l.min_level + '+</div></div>';
        }
        container.innerHTML = html;
    });
}

function travelTo(locationId) {
    showToast('Переход...', 'info');
}

function loadLicenses() {
    api('getLicenses', {}, function(licenses) {
        var shop = document.getElementById('licenses-shop');
        if (!shop) return;
        
        if (!licenses || !licenses.length) {
            shop.innerHTML = '<p>Нет лицензий</p>';
            return;
        }
        
        var html = '';
        for (var i = 0; i < licenses.length; i++) {
            var l = licenses[i];
            html += '<div class="license-card">' +
                '<div class="license-name">' + l.name + '</div>' +
                '<div class="license-price">💰 ' + l.price_gold + '</div>' +
                '<button onclick="buyLicense(' + l.id + ')">Купить</button></div>';
        }
        shop.innerHTML = html;
    });
}

function buyLicense(licenseId) {
    api('buyLicense', { license_id: licenseId }, function(result) {
        if (result.success) {
            showToast('Лицензия куплена!', 'success');
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

// ================================
// SKILLS & QUESTS
// ================================

function loadSkills() {
    api('getSkills', {}, function(skills) {
        var container = document.getElementById('skills-list');
        if (!container) return;
        
        if (!skills || !skills.length) {
            container.innerHTML = '<p>Нет навыков</p>';
            return;
        }
        
        var html = '';
        for (var i = 0; i < skills.length; i++) {
            var s = skills[i];
            html += '<div class="skill-card">' +
                '<div class="skill-name">' + s.name + '</div>' +
                '<div class="skill-desc">' + s.description + '</div></div>';
        }
        container.innerHTML = html;
    });
}

function loadQuests() {
    api('getQuests', {}, function(quests) {
        var container = document.getElementById('quests-list');
        if (!container) return;
        
        if (!quests || !quests.length) {
            container.innerHTML = '<p>Нет квестов</p>';
            return;
        }
        
        var html = '';
        for (var i = 0; i < quests.length; i++) {
            var q = quests[i];
            html += '<div class="quest-card">' +
                '<div class="quest-title">' + q.title + '</div>' +
                '<div class="quest-desc">' + q.description + '</div></div>';
        }
        container.innerHTML = html;
    });
}

function claimDailyReward() {
    api('claimDailyReward', {}, function(result) {
        if (result.success) {
            showToast('+' + result.gold + ' золота, +' + result.exp + ' опыта', 'success');
            refreshUser();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function loadAchievements() {
    api('getAchievements', {}, function(achievements) {
        var container = document.getElementById('achievements-list');
        if (!container) return;
        
        if (!achievements || !achievements.length) {
            container.innerHTML = '<p>Нет достижений</p>';
            return;
        }
        
        var html = '';
        for (var i = 0; i < achievements.length; i++) {
            var a = achievements[i];
            html += '<div class="achievement-card">' +
                '<div class="achievement-name">' + a.name + '</div>' +
                '<div class="achievement-desc">' + a.description + '</div></div>';
        }
        container.innerHTML = html;
    });
}

// ================================
// RAIDS & HISTORY
// ================================

function loadRaidBosses() {
    api('getRaidBosses', {}, function(bosses) {
        var container = document.getElementById('raids-list');
        if (!container) return;
        
        if (!bosses || !bosses.length) {
            container.innerHTML = '<div class="empty-state">Нет активных рейдов</div>';
            return;
        }
        
        var html = '';
        bosses.forEach(function(b) {
            html += '<div class="enemy-card raid-card" onclick="joinRaid(' + b.id + ')">';
            html += '<h3>🔥 ' + b.name + '</h3>';
            html += '<div class="level">Уровень ' + b.level + '</div>';
            html += '<div class="stats">';
            html += '<span>❤️ HP: ' + b.hp + '</span>';
            html += '<span>⚔️ ATK: ' + b.atk + '</span>';
            html += '</div>';
            html += '<div class="boss-badge">БОСС</div>';
            html += '<div class="stats" style="margin-top:10px">';
            html += '<span>💰 ' + b.gold_reward + ' золота</span>';
            html += '<span>⭐ ' + b.exp_reward + ' опыта</span>';
            html += '</div>';
            html += '</div>';
        });
        
        container.innerHTML = html;
    });
}

function joinRaid(raidId) {
    if (!confirm('Присоединиться к рейду?')) return;
    showToast('Присоединение к рейду...', 'info');
}

function loadHistory() {
    api('getHistory', {}, function(history) {
        var container = document.getElementById('history-list');
        if (!container) return;
        
        if (!history || !history.length) {
            container.innerHTML = '<p>Нет истории</p>';
            return;
        }
        
        var html = '';
        for (var i = 0; i < history.length; i++) {
            var h = history[i];
            var resultClass = h.result === 'win' ? 'win' : 'lose';
            html += '<div class="history-card ' + resultClass + '">' +
                '<div>' + h.battle_type + ' vs ' + h.opponent_name + '</div>' +
                '<div>' + h.result + '</div></div>';
        }
        container.innerHTML = html;
    });
}

function loadRecipes() {
    api('getRecipes', {}, function(recipes) {
        var container = document.getElementById('recipes-list');
        if (!container) return;
        
        if (!recipes || !recipes.length) {
            container.innerHTML = '<p>Нет рецептов</p>';
            return;
        }
        
        var html = '';
        for (var i = 0; i < recipes.length; i++) {
            var r = recipes[i];
            html += '<div class="recipe-card">' +
                '<div>' + r.name + '</div>' +
                '<button onclick="craftItem(' + r.id + ')">Создать</button></div>';
        }
        container.innerHTML = html;
    });
}

function craftItem(recipeId) {
    showToast('Создание...', 'info');
}

// ================================
// LEADERBOARD & ARENA
// ================================

function loadLeaderboard() {
    api('getLeaderboard', {}, function(users) {
        var container = document.getElementById('leaderboard-body');
        if (!container) return;
        
        if (!users || !users.length) {
            container.innerHTML = '<div class="lb-row"><span>Нет игроков</span></div>';
            return;
        }
        
        var html = '';
        var classIcons = { warrior: '⚔️', mage: '🔮', archer: '🏹', rogue: '🗡️', paladin: '🛡️' };
        
        for (var i = 0; i < users.length; i++) {
            var u = users[i];
            var rankClass = '';
            if (i === 0) rankClass = 'gold';
            else if (i === 1) rankClass = 'silver';
            else if (i === 2) rankClass = 'bronze';
            
            html += '<div class="lb-row">' +
                '<span class="lb-rank ' + rankClass + '">' + (i + 1) + '</span>' +
                '<span>' + u.username + '</span>' +
                '<span>' + (classIcons[u.class] || '⚔️') + ' ' + u.class + '</span>' +
                '<span>' + u.level + '</span>' +
                '</div>';
        }
        container.innerHTML = html;
    });
}

function loadGuildsForRating() {
    api('getGuilds', {}, function(guilds) {
        var container = document.getElementById('guilds-list-body');
        if (!container) return;
        
        if (!guilds || !guilds.length) {
            container.innerHTML = '<div class="rating-row"><span colspan="4">Нет кланов</span></div>';
            return;
        }
        
        var html = '';
        for (var i = 0; i < guilds.length; i++) {
            var g = guilds[i];
            var rankClass = '';
            if (i === 0) rankClass = 'gold';
            else if (i === 1) rankClass = 'silver';
            else if (i === 2) rankClass = 'bronze';
            
            html += '<div class="rating-row">' +
                '<span class="rank ' + rankClass + '">' + (i + 1) + '</span>' +
                '<span class="name">' + g.name + '</span>' +
                '<span class="level">' + g.level + '</span>' +
                '<span class="level">' + (g.members || 0) + '</span>' +
                '</div>';
        }
        container.innerHTML = html;
    });
}

function showLeaderboardTab(tab, btn) {
    document.querySelectorAll('.rating-tab').forEach(function(b) { b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
    
    if (tab === 'guilds') {
        document.getElementById('players-rating').style.display = 'none';
        document.getElementById('guilds-rating').style.display = 'block';
        loadGuildsForRating();
    } else {
        document.getElementById('players-rating').style.display = 'block';
        document.getElementById('guilds-rating').style.display = 'none';
        loadLeaderboard();
    }
}

function loadArena() {
    if (!currentUser) return;
    var rating = document.getElementById('my-arena-rating');
    if (rating) rating.textContent = currentUser.pvp_rating || 1000;
    var wins = document.getElementById('my-pvp-wins');
    if (wins) wins.textContent = currentUser.pvp_wins || 0;
    var losses = document.getElementById('my-pvp-losses');
    if (losses) losses.textContent = currentUser.pvp_losses || 0;
    
    api('getOpponentsForArena', {}, function(users) {
        var container = document.getElementById('arena-opponents');
        if (!container) return;
        
        if (!users || !users.length) {
            container.innerHTML = '<div class="empty-state">Нет соперников</div>';
            return;
        }
        
        var html = '';
        var classIcons = { warrior: '⚔️', mage: '🔮', archer: '🏹', rogue: '🗡️', paladin: '🛡️' };
        
        users.forEach(function(u) {
            html += '<div class="enemy-card" onclick="startPvpBattle(' + u.id + ')">';
            html += '<h3>' + (classIcons[u.class] || '⚔️') + ' ' + u.username + '</h3>';
            html += '<div class="level">Уровень ' + u.level + '</div>';
            html += '<div class="stats">';
            html += '<span>🏆 Рейтинг: ' + u.pvp_rating + '</span>';
            html += '<span>⚔️ ' + u.pvp_wins + ' побед</span>';
            html += '</div>';
            html += '</div>';
        });
        
        container.innerHTML = html;
    });
}

var currentPvpBattle = null;

function startPvpBattle(opponentId) {
    api('startPvpBattle', { opponent_id: opponentId }, function(result) {
        if (result.battle_id) {
            currentPvpBattle = result;
            showPvpBattleUI(result);
        } else if (result.error) {
            showToast(result.error, 'error');
        }
    });
}

function showPvpBattleUI(battle) {
    var listView = document.getElementById('arena-list-view');
    var battleView = document.getElementById('pvp-battle-view');
    if (listView) listView.style.display = 'none';
    if (battleView) battleView.style.display = 'block';
    
    var enemyName = document.getElementById('pvp-enemy-name');
    var enemyLevel = document.getElementById('pvp-enemy-level');
    var enemyHp = document.getElementById('pvp-enemy-hp');
    var enemyMaxHp = document.getElementById('pvp-enemy-max-hp');
    var enemyHpBar = document.getElementById('pvp-enemy-hp-bar');
    var playerHp = document.getElementById('pvp-player-hp');
    var playerMaxHp = document.getElementById('pvp-player-max-hp');
    var playerHpBar = document.getElementById('pvp-player-hp-bar');
    var playerMp = document.getElementById('pvp-player-mp');
    var playerMaxMp = document.getElementById('pvp-player-max-mp');
    
    if (battle.opponent) {
        if (enemyName) enemyName.textContent = battle.opponent.username;
        if (enemyLevel) enemyLevel.textContent = battle.opponent.level;
        if (enemyHp) enemyHp.textContent = battle.opponent_hp;
        if (enemyMaxHp) enemyMaxHp.textContent = battle.opponent.max_hp;
        if (enemyHpBar) enemyHpBar.style.width = (battle.opponent_hp / battle.opponent.max_hp * 100) + '%';
    }
    
    if (playerHp) playerHp.textContent = battle.player_hp;
    if (playerMaxHp) playerMaxHp.textContent = currentUser.max_hp;
    if (playerHpBar) playerHpBar.style.width = (battle.player_hp / currentUser.max_hp * 100) + '%';
    if (playerMp) playerMp.textContent = battle.player_mp || currentUser.max_mp;
    if (playerMaxMp) playerMaxMp.textContent = currentUser.max_mp;
}

function pvpAttack(type) {
    type = type || 'normal';
    if (!currentPvpBattle) return;
    
    api('pvpAttack', { battle_id: currentPvpBattle.battle_id, attack_type: type }, function(result) {
        if (result.won) {
            showToast('Победа в PvP! +' + result.rating_change + ' рейтинга', 'success');
            cancelPvpBattle();
        } else if (result.lost) {
            showToast('Поражение! ' + result.rating_change + ' рейтинга', 'error');
            cancelPvpBattle();
        } else {
            currentPvpBattle.player_hp = result.player_hp;
            currentPvpBattle.opponent_hp = result.opponent_hp;
            updatePvpBattleDisplay();
        }
    });
}

function updatePvpBattleDisplay() {
    var enemyHp = document.getElementById('pvp-enemy-hp');
    var enemyMaxHp = document.getElementById('pvp-enemy-max-hp');
    var enemyHpBar = document.getElementById('pvp-enemy-hp-bar');
    var playerHp = document.getElementById('pvp-player-hp');
    var playerMaxHp = document.getElementById('pvp-player-max-hp');
    var playerHpBar = document.getElementById('pvp-player-hp-bar');
    var playerMp = document.getElementById('pvp-player-mp');
    var playerMaxMp = document.getElementById('pvp-player-max-mp');
    
    if (currentPvpBattle) {
        if (enemyHp) enemyHp.textContent = currentPvpBattle.opponent_hp;
        if (enemyMaxHp) enemyMaxHp.textContent = currentPvpBattle.opponent.max_hp;
        if (enemyHpBar) enemyHpBar.style.width = (currentPvpBattle.opponent_hp / currentPvpBattle.opponent.max_hp * 100) + '%';
        if (playerHp) playerHp.textContent = currentPvpBattle.player_hp;
        if (playerMaxHp) playerMaxHp.textContent = currentUser.max_hp;
        if (playerHpBar) playerHpBar.style.width = (currentPvpBattle.player_hp / currentUser.max_hp * 100) + '%';
        if (playerMp) playerMp.textContent = currentPvpBattle.player_mp || currentUser.mp;
        if (playerMaxMp) playerMaxMp.textContent = currentUser.max_mp;
    }
}

function usePvpPotion() {
    var potions = currentInventory.filter(function(i) { return i.item && i.item.type === 'potion' && !i.equipped; });
    if (!potions.length) {
        showToast('Нет зелий!', 'error');
        return;
    }
    api('usePotion', { item_id: potions[0].item.id }, function(result) {
        if (result.success) {
            if (currentPvpBattle) {
                currentPvpBattle.player_hp = Math.min(currentPvpBattle.player_hp + result.hp_restored, currentUser.max_hp);
                updatePvpBattleDisplay();
            }
            loadInventory();
        }
    });
}

function fleePvpBattle() {
    if (!confirm('Сдаться и покинуть бой?')) return;
    currentPvpBattle = null;
    var listView = document.getElementById('arena-list-view');
    var battleView = document.getElementById('pvp-battle-view');
    if (listView) listView.style.display = 'block';
    if (battleView) battleView.style.display = 'none';
    showToast('Вы сдались', 'info');
}

function cancelPvpBattle() {
    currentPvpBattle = null;
    loadUser();
    loadArena();
}

function showArenaTab(tab, btn) {
    document.querySelectorAll('.arena-tabs .tab').forEach(function(b) { b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
    if (tab === 'search') {
        var content = document.getElementById('arena-content');
        if (content) {
            content.innerHTML = '<p>Найдите соперника для PvP боя!</p><button onclick="searchOpponents()">🔍 Найти соперников</button><div id="opponents-list"></div>';
        }
    }
}

function searchOpponents() {
    api('getOpponentsForArena', {}, function(users) {
        var list = document.getElementById('opponents-list');
        if (!list) return;
        
        if (!users || !users.length) {
            list.innerHTML = '<p>Нет соперников</p>';
            return;
        }
        
        var html = '';
        for (var i = 0; i < users.length; i++) {
            var u = users[i];
            if (currentUser && u.id === currentUser.id) continue;
            html += '<div class="opponent-card" onclick="startPvpBattle(' + u.id + ')">' +
                '<div>' + u.username + ' (' + u.class + ' Ур.' + u.level + ')</div>' +
                '<div>🏆 ' + u.pvp_rating + '</div></div>';
            if (i >= 9) break;
        }
        list.innerHTML = html;
    });
}

function startPvpBattle(opponentId) {
    api('startPvpBattle', { opponent_id: opponentId }, function(result) {
        if (result.battle_id) {
            showSection('pvp');
            showToast('PvP бой начался!', 'success');
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function pvpAttack(type) {
    type = type || 'normal';
    api('pvpAttack', { battle_id: currentBattle.id, attack_type: type }, function(result) {
        if (result.won) {
            showToast('Победа! Рейтинг +' + result.rating_change, 'success');
            currentBattle = null;
            refreshUser();
        } else if (result.player_dmg) {
            showToast('Нанесено ' + result.player_dmg + ' урона', 'info');
        }
    });
}

// ================================
// GUILDS
// ================================

function loadGuilds() {
    api('getGuilds', {}, function(guilds) {
        var container = document.getElementById('guilds-list');
        if (!container) return;
        
        if (!guilds || !guilds.length) {
            container.innerHTML = '<p>Нет гильдий</p>';
            return;
        }
        
        var html = '';
        for (var i = 0; i < guilds.length; i++) {
            var g = guilds[i];
            html += '<div class="guild-card" onclick="joinGuild(' + g.id + ')">' +
                '<div>' + g.name + '</div>' +
                '<div>Уровень ' + g.level + '</div></div>';
        }
        container.innerHTML = html;
    });
}

function createGuild() {
    var name = document.getElementById('guild-name');
    var guildName = name ? name.value.trim() : '';
    if (!guildName || guildName.length < 3) {
        showToast('Название 3-30 символов', 'error');
        return;
    }
    
    var desc = document.getElementById('guild-desc');
    var description = desc ? desc.value.trim() : '';
    
    api('createGuild', { name: guildName, description: description }, function(result) {
        if (result.success) {
            showToast('Гильдия создана!', 'success');
            loadGuilds();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function joinGuild(guildId) {
    api('joinGuild', { guild_id: guildId }, function(result) {
        if (result.success) {
            showToast('Вы в гильдии!', 'success');
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function leaveGuild() {
    if (!confirm('Покинуть гильдию?')) return;
    api('leaveGuild', {}, function(result) {
        if (result.success) {
            showToast('Вы покинули гильдию', 'info');
        }
    });
}

function showGuildDonate() {
    var modal = document.getElementById('guild-donate-modal');
    if (modal) modal.style.display = 'block';
}

function closeGuildDonate() {
    var modal = document.getElementById('guild-donate-modal');
    if (modal) modal.style.display = 'none';
}

function showGuildTab(tab, btn) {
    document.querySelectorAll('.guild-tab').forEach(function(b) { b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
    
    var members = document.getElementById('guild-members-tab');
    var chat = document.getElementById('guild-chat-tab');
    var quests = document.getElementById('guild-quests-tab');
    
    if (members) members.style.display = tab === 'members' ? 'block' : 'none';
    if (chat) chat.style.display = tab === 'chat' ? 'block' : 'none';
    if (quests) quests.style.display = tab === 'quests' ? 'block' : 'none';
}

function selectDonate(amount) {
    var input = document.getElementById('donate-amount');
    if (input) input.value = amount;
    
    var expEl = document.getElementById('donate-exp-gain');
    if (expEl) expEl.textContent = Math.floor(amount * 0.5);
}

function donateToGuild() {
    var input = document.getElementById('donate-amount');
    var amount = parseInt(input ? input.value : 0);
    
    if (!amount || amount < 10) {
        showToast('Минимум 10 золота', 'error');
        return;
    }
    
    api('donateToGuild', { amount: amount }, function(result) {
        if (result.success) {
            showToast('+' + result.exp_gained + ' опыта гильдии', 'success');
            closeGuildDonate();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

// ================================
// CHAT
// ================================

var chatChannel = 'global';
var lastChatId = 0;

function setChatChannel(channel) {
    chatChannel = channel;
    var channels = document.querySelectorAll('.chat-channel');
    if (channels.length) {
        channels.forEach(function(c) { c.classList.remove('active'); });
        if (event && event.target) event.target.classList.add('active');
    }
    lastChatId = 0;
    loadChat();
}

function loadChat() {
    if (!currentUser) return;
    
    var container = document.getElementById('chat-messages');
    if (!container) return;
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'api.php?action=getChat&channel=' + chatChannel + '&since=' + lastChatId, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var messages = JSON.parse(xhr.responseText);
                if (!messages || !messages.length) {
                    if (lastChatId === 0) {
                        container.innerHTML = '<div class="chat-welcome"><p>🌍 Добро пожаловать в чат!</p><p>Каналы: 🌍 Глобальный | 💰 Торговля | ❓ Помощь</p></div>';
                    }
                    return;
                }
                
                if (lastChatId === 0) {
                    container.innerHTML = '';
                }
                
                var html = '';
                var classIcons = { warrior: '⚔️', mage: '🔮', archer: '🏹', rogue: '🗡️', paladin: '🛡️' };
                
                for (var i = 0; i < messages.length; i++) {
                    var m = messages[i];
                    var isMe = currentUser && m.user_id === currentUser.id;
                    var classIcon = classIcons[m.class] || '🎮';
                    var time = m.timestamp ? new Date(m.timestamp).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' }) : '';
                    
                    var extraClass = '';
                    var prefix = '';
                    if (m.message_type === 'system') {
                        extraClass = 'system';
                        prefix = '🔔 ';
                    } else if (m.message_type === 'announcement') {
                        extraClass = 'announcement';
                        prefix = '📢 ';
                    }
                    
                    html += '<div class="chat-message ' + extraClass + '">';
                    html += '<span class="chat-time">' + time + '</span>';
                    if (m.level) html += '<span class="chat-level">[' + m.level + ']</span> ';
                    html += '<span class="chat-user">' + classIcon + ' ' + escapeHtml(m.username) + ':</span> ';
                    html += '<span class="chat-text' + (isMe ? ' me' : '') + '">' + prefix + escapeHtml(m.message) + '</span>';
                    html += '</div>';
                    
                    lastChatId = Math.max(lastChatId, m.id);
                }
                
                container.insertAdjacentHTML('beforeend', html);
                container.scrollTop = container.scrollHeight;
            } catch(e) {
                console.error('Chat parse error:', e);
            }
        }
    };
    xhr.send();
}

function sendMessage() {
    var input = document.getElementById('chat-input');
    var message = input ? input.value.trim() : '';
    if (!message) return;
    if (!currentUser) {
        showToast('Войдите в игру!', 'error');
        return;
    }
    
    var formData = new FormData();
    formData.append('action', 'sendChat');
    formData.append('message', message);
    formData.append('channel', chatChannel);
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var result = JSON.parse(xhr.responseText);
                if (result.success) {
                    input.value = '';
                    lastChatId = 0;
                    loadChat();
                } else if (result.error) {
                    showToast(result.error, 'error');
                }
            } catch(e) {
                showToast('Ошибка отправки', 'error');
            }
        }
    };
    xhr.send(formData);
}

function insertEmoji(emoji) {
    var input = document.getElementById('chat-input');
    if (input) {
        input.value += emoji;
        input.focus();
    }
    toggleEmojiPicker();
}

function toggleEmojiPicker() {
    var picker = document.getElementById('emoji-picker');
    if (picker) {
        picker.style.display = picker.style.display === 'none' ? 'grid' : 'none';
    }
}

function showUserProfile(userId) {
    showToast('Профиль игрока ID: ' + userId, 'info');
}

function showUserProfile(userId) {
    showToast('Профиль игрока ID: ' + userId, 'info');
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function loadOnlineCount() {
    api('getOnlineUsers', {}, function(result) {
        if (result.success) {
            var countEl = document.getElementById('online-count');
            if (countEl) countEl.textContent = result.count || 1;
        }
    });
}

// ================================
// FILTER
// ================================

function filterShop(type) {
    var btns = document.querySelectorAll('.filter-btn');
    btns.forEach(function(b) { b.classList.remove('active'); });
    event.target.classList.add('active');
    loadShop();
}

function filterInventory(type) {
    var tabs = document.querySelectorAll('.inventory-tabs .tab');
    tabs.forEach(function(t) { t.classList.remove('active'); });
    event.target.classList.add('active');
    loadInventory();
}

// ================================
// INVENTORY - IMPROVED
// ================================

function loadInventory() {
    api('getInventory', {}, function(items) {
        var inv = document.getElementById('inventory-items');
        if (!inv) return;
        
        if (!items || !items.length) {
            inv.innerHTML = '<p>Инвентарь пуст</p>';
            return;
        }
        
        currentInventory = items;
        var html = '';
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            var htmlItem = renderInventoryCard(item);
            html += htmlItem;
        }
        inv.innerHTML = html;
        
        startTemporaryItemsTimer();
    });
}

function renderInventoryCard(item) {
    var icons = { 
        weapon: '⚔️', 
        armor: '🛡️', 
        helmet: '⛑️', 
        boots: '👢', 
        ring: '💍', 
        amulet: '📿',
        potion: '🧪',
        scroll: '📜',
        food: '🍖'
    };
    var icon = icons[item.type] || '📦';
    var equipped = item.equipped ? 'equipped' : '';
    var temporary = item.expires_at ? 'temporary' : '';
    var equippedBadge = item.equipped ? '<span class="item-card-equipped-badge">Надето</span>' : '';
    var rarityClass = item.rarity || 'common';
    
    var durabilityHtml = '';
    if (item.durability !== undefined && item.max_durability > 0) {
        var durPercent = (item.durability / item.max_durability) * 100;
        var durClass = 'high';
        if (durPercent < 30) durClass = 'critical';
        else if (durPercent < 60) durClass = 'low';
        else if (durPercent < 80) durClass = 'medium';
        
        durabilityHtml = '<div class="durability-bar">' +
            '<div class="durability-label"><span>Прочность</span><span>' + item.durability + '/' + item.max_durability + '</span></div>' +
            '<div class="durability-track">' +
            '<div class="durability-fill ' + durClass + '" style="width: ' + durPercent + '%"></div>' +
            '</div></div>';
    }
    
    var timerHtml = '';
    if (item.expires_at) {
        var timeLeft = getTimeRemaining(item.expires_at);
        timerHtml = '<div class="item-timer">' +
            '<span class="item-timer-icon">⏱️</span>' +
            '<span class="item-timer-text">Осталось:</span>' +
            '<span class="item-timer-countdown" data-expires="' + item.expires_at + '">' + timeLeft + '</span>' +
            '</div>';
    }
    
    var actionBtn = getActionButton(item);
    
    var cardClass = 'inventory-card ' + equipped + ' ' + temporary;
    var statsText = getItemStatsText(item);
    
    return '<div class="' + cardClass + '" onclick="showItemTooltip(' + item.id + ', this)" data-item-id="' + item.id + '">' +
        '<div class="item-card-header">' +
        '<span class="item-card-icon">' + icon + '</span>' +
        '<div class="item-card-name">' + item.name + '</div>' +
        equippedBadge +
        '</div>' +
        (item.quantity > 1 ? '<div class="item-card-qty">Кол-во: ' + item.quantity + '</div>' : '') +
        (statsText ? '<div class="item-card-stats">' + statsText + '</div>' : '') +
        durabilityHtml +
        timerHtml +
        actionBtn +
        '</div>';
}

function getItemStatsText(item) {
    var stats = [];
    if (item.atk_bonus > 0) stats.push('⚔️ +' + item.atk_bonus);
    if (item.def_bonus > 0) stats.push('🛡️ +' + item.def_bonus);
    if (item.hp_bonus > 0) stats.push('❤️ +' + item.hp_bonus);
    if (item.mp_bonus > 0) stats.push('💧 +' + item.mp_bonus);
    return stats.join(' ');
}

function getActionButton(item) {
    var btnHtml = '';
    var type = item.type;
    
    if (type === 'weapon' || type === 'armor' || type === 'helmet' || type === 'boots' || type === 'ring' || type === 'amulet') {
        if (item.equipped) {
            btnHtml = '<button class="btn-action btn-unequip" onclick="unequipItem(event, ' + item.id + ')">Снять</button>';
        } else {
            btnHtml = '<button class="btn-action btn-equip" onclick="equipItem(event, ' + item.id + ')">Одеть</button>';
        }
    } else if (type === 'potion' || type === 'food' || type === 'scroll') {
        btnHtml = '<button class="btn-action btn-use" onclick="useItem(event, ' + item.id + ')">Использовать</button>';
    } else {
        btnHtml = '<button class="btn-action btn-change" onclick="changeItem(event, ' + item.id + ')">Изменить</button>';
    }
    
    return btnHtml;
}

function getTimeRemaining(expiresAt) {
    if (!expiresAt) return '00:00:00';
    
    var expires = new Date(expiresAt).getTime();
    var now = new Date().getTime();
    var diff = Math.max(0, expires - now);
    
    var hours = Math.floor(diff / (1000 * 60 * 60));
    var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((diff % (1000 * 60)) / 1000);
    
    var h = hours < 10 ? '0' + hours : hours;
    var m = minutes < 10 ? '0' + minutes : minutes;
    var s = seconds < 10 ? '0' + seconds : seconds;
    
    return h + ':' + m + ':' + s;
}

function startTemporaryItemsTimer() {
    if (temporaryItemsTimer) {
        clearInterval(temporaryItemsTimer);
    }
    
    temporaryItemsTimer = setInterval(function() {
        var timers = document.querySelectorAll('.item-timer-countdown[data-expires]');
        timers.forEach(function(timer) {
            var expiresAt = timer.getAttribute('data-expires');
            var timeLeft = getTimeRemaining(expiresAt);
            timer.textContent = timeLeft;
            
            if (timeLeft === '00:00:00') {
                var card = timer.closest('.inventory-card');
                if (card) card.remove();
                loadInventory();
            }
        });
    }, 1000);
}

function showItemTooltip(itemId, element) {
    var item = getInventoryItem(itemId);
    if (!item) return;
    
    selectedItem = item;
    var tooltip = document.getElementById('item-tooltip');
    if (!tooltip) return;
    
    var icons = { 
        weapon: '⚔️', 
        armor: '🛡️', 
        helmet: '⛑️', 
        boots: '👢', 
        ring: '💍', 
        amulet: '📿',
        potion: '🧪',
        scroll: '📜',
        food: '🍖'
    };
    
    document.getElementById('tooltip-icon').textContent = icons[item.type] || '📦';
    document.getElementById('tooltip-name').textContent = item.name;
    document.getElementById('tooltip-type').textContent = item.type;
    
    var rarityEl = document.getElementById('tooltip-rarity');
    var rarityNames = { common: 'Обычный', uncommon: 'Необычный', rare: 'Редкий', epic: 'Эпический', legendary: 'Легендарный' };
    rarityEl.textContent = rarityNames[item.rarity] || 'Обычный';
    rarityEl.className = 'tooltip-rarity ' + (item.rarity || 'common');
    
    var statsEl = document.getElementById('tooltip-stats');
    var statsText = getItemStatsText(item);
    statsEl.textContent = statsText || 'Нет бонусов';
    
    document.getElementById('tooltip-desc').textContent = item.description || 'Полезный предмет';
    
    // Durability
    var durBar = document.getElementById('tooltip-durability');
    if (item.durability !== undefined && item.max_durability > 0) {
        durBar.style.display = 'block';
        var durPercent = (item.durability / item.max_durability) * 100;
        document.getElementById('durability-text').textContent = item.durability + '/' + item.max_durability;
        
        var fill = document.getElementById('durability-fill');
        fill.style.width = durPercent + '%';
        fill.className = 'durability-fill';
        if (durPercent < 30) fill.classList.add('critical');
        else if (durPercent < 60) fill.classList.add('low');
        else if (durPercent < 80) fill.classList.add('medium');
        else fill.classList.add('high');
    } else {
        durBar.style.display = 'none';
    }
    
    // Timer for temporary items
    var timer = document.getElementById('tooltip-timer');
    if (item.expires_at) {
        timer.style.display = 'flex';
        document.getElementById('tooltip-countdown').textContent = getTimeRemaining(item.expires_at);
    } else {
        timer.style.display = 'none';
    }
    
    // Action buttons
    var actions = document.getElementById('tooltip-actions');
    var type = item.type;
    
    var equipBtn = document.getElementById('btn-equip');
    var applyBtn = document.getElementById('btn-apply');
    var changeBtn = document.getElementById('btn-change');
    
    if (type === 'weapon' || type === 'armor' || type === 'helmet' || type === 'boots' || type === 'ring' || type === 'amulet') {
        equipBtn.style.display = item.equipped ? 'none' : 'block';
        applyBtn.style.display = 'none';
        changeBtn.style.display = 'none';
    } else if (type === 'potion' || type === 'food' || type === 'scroll') {
        equipBtn.style.display = 'none';
        applyBtn.style.display = 'block';
        changeBtn.style.display = 'none';
    } else {
        equipBtn.style.display = 'none';
        applyBtn.style.display = 'none';
        changeBtn.style.display = 'block';
    }
    
    tooltip.style.display = 'block';
    
    // Position
    var rect = element.getBoundingClientRect();
    var tooltipWidth = 240;
    var leftPos = rect.left;
    if (leftPos + tooltipWidth > window.innerWidth) {
        leftPos = window.innerWidth - tooltipWidth - 20;
    }
    tooltip.style.left = leftPos + 'px';
    tooltip.style.top = (rect.bottom + 10) + 'px';
    
    // Close on click outside
    setTimeout(function() {
        document.addEventListener('click', function(e) {
            if (!tooltip.contains(e.target)) {
                tooltip.style.display = 'none';
            }
        });
    }, 100);
}

function getInventoryItem(itemId) {
    for (var i = 0; i < currentInventory.length; i++) {
        if (currentInventory[i].id === itemId) {
            return currentInventory[i];
        }
    }
    return null;
}

function equipItem(event, itemId) {
    event.stopPropagation();
    api('equipItem', { item_id: itemId }, function(result) {
        if (result.success) {
            showToast('Предмет надет!', 'success');
            loadInventory();
            loadCharacter();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function unequipItem(event, itemId) {
    event.stopPropagation();
    api('unequipItem', { item_id: itemId }, function(result) {
        if (result.success) {
            showToast('Предмет снят!', 'success');
            loadInventory();
            loadCharacter();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function useItem(event, itemId) {
    event.stopPropagation();
    api('useItem', { item_id: itemId }, function(result) {
        if (result.success) {
            showToast(result.message || 'Предмет использован!', 'success');
            loadInventory();
            loadCharacter();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function changeItem(event, itemId) {
    event.stopPropagation();
    showToast('Изменение предмета...', 'info');
}

function equipItemFromTooltip() {
    if (selectedItem) {
        equipItem({ stopPropagation: function() {} }, selectedItem.id);
        hideTooltip();
    }
}

function useItemFromTooltip() {
    if (selectedItem) {
        useItem({ stopPropagation: function() {} }, selectedItem.id);
        hideTooltip();
    }
}

function changeItemFromTooltip() {
    if (selectedItem) {
        changeItem({ stopPropagation: function() {} }, selectedItem.id);
        hideTooltip();
    }
}

function hideTooltip() {
    var tooltip = document.getElementById('item-tooltip');
    if (tooltip) tooltip.style.display = 'none';
    selectedItem = null;
}

// ================================
// MARKET
// ================================

var marketPage = 1;

function showMarketTab(tab) {
    document.querySelectorAll('.market-tab').forEach(function(t) { t.classList.remove('active'); });
    event.target.classList.add('active');
    
    document.getElementById('market-browse').style.display = tab === 'browse' ? 'block' : 'none';
    document.getElementById('market-sell').style.display = tab === 'sell' ? 'block' : 'none';
    document.getElementById('market-my').style.display = tab === 'my' ? 'block' : 'none';
    
    if (tab === 'sell') loadInventoryForMarket();
    if (tab === 'my') loadMyMarketListings();
}

function loadMarketListings() {
    var type = document.getElementById('market-type-filter') ? document.getElementById('market-type-filter').value : 'all';
    var rarity = document.getElementById('market-rarity-filter') ? document.getElementById('market-rarity-filter').value : 'all';
    var search = document.getElementById('market-search') ? document.getElementById('market-search').value : '';
    
    api('getMarketListings', { type: type, rarity: rarity, search: search, page: marketPage }, function(result) {
        if (result.success) {
            renderMarketListings(result.listings);
            renderMarketPagination(result.pages);
        }
    });
}

function renderMarketListings(listings) {
    var container = document.getElementById('market-listings');
    if (!container) return;
    
    if (!listings || listings.length === 0) {
        container.innerHTML = '<div class="empty-state">На рынке пока нет товаров</div>';
        return;
    }
    
    var html = '';
    var typeIcons = { weapon: '⚔️', armor: '🛡️', helmet: '⛑️', boots: '👢', ring: '💍', amulet: '📿', potion: '🧪', food: '🍖', material: '🪨' };
    var rarityLabels = { common: 'Обычный', uncommon: 'Необычный', rare: 'Редкий', epic: 'Эпический', legendary: 'Легендарный' };
    
    listings.forEach(function(item) {
        var priceHtml = '';
        if (item.price_gold > 0) priceHtml += '<span style="color:#d97706">💰 ' + item.price_gold + '</span> ';
        if (item.price_crystals > 0) priceHtml += '<span style="color:#8b5cf6">💎 ' + item.price_crystals + '</span>';
        
        html += '<div class="market-item">';
        html += '<div class="market-item-header">';
        html += '<div><div class="market-item-name">' + (typeIcons[item.item_type] || '📦') + ' ' + item.item_name + '</div>';
        html += '<div class="market-item-type">' + (item.quantity > 1 ? 'x' + item.quantity : item.item_type) + '</div></div>';
        html += '<div class="market-item-rarity rarity-' + item.item_rarity + '">' + rarityLabels[item.item_rarity] + '</div>';
        html += '</div>';
        html += '<div class="market-item-price">Цена: ' + priceHtml + '</div>';
        html += '<div class="market-item-seller">Продавец: ' + item.seller_name + '</div>';
        if (item.description) html += '<div class="market-item-desc">' + item.description + '</div>';
        html += '<div class="market-item-actions"><button class="btn-buy" onclick="buyFromMarket(' + item.id + ')">Купить</button></div>';
        html += '</div>';
    });
    
    container.innerHTML = html;
}

function renderMarketPagination(pages) {
    var container = document.getElementById('market-pagination');
    if (!container) return;
    
    if (pages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    var html = '';
    for (var i = 1; i <= pages; i++) {
        html += '<button class="' + (i === marketPage ? 'active' : '') + '" onclick="marketPage=' + i + ';loadMarketListings()">' + i + '</button>';
    }
    container.innerHTML = html;
}

function buyFromMarket(listingId) {
    if (!currentUser) {
        showToast('Войдите в игру', 'error');
        return;
    }
    
    if (!confirm('Купить этот предмет?')) return;
    
    api('buyFromMarket', { listing_id: listingId }, function(result) {
        if (result.success) {
            showToast('Куплено: ' + result.item_name + ' (x' + result.quantity + ')', 'success');
            loadMarketListings();
            loadUser();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function loadInventoryForMarket() {
    api('getInventory', {}, function(result) {
        if (result.success) {
            var select = document.getElementById('sell-item-select');
            if (!select) return;
            
            var html = '<option value="">Выберите предмет</option>';
            var sellableTypes = ['potion', 'food', 'material'];
            
            (result.inventory || []).forEach(function(inv) {
                if (inv.item && !inv.equipped && (inv.quantity > 1 || sellableTypes.includes(inv.item.type))) {
                    var icon = { weapon: '⚔️', armor: '🛡️', helmet: '⛑️', boots: '👢', ring: '💍', amulet: '📿', potion: '🧪', food: '🍖', material: '🪨' }[inv.item.type] || '📦';
                    html += '<option value="' + inv.item.id + '" data-qty="' + inv.quantity + '">' + icon + ' ' + inv.item.name + ' (x' + inv.quantity + ')</option>';
                }
            });
            
            select.innerHTML = html;
            
            select.onchange = function() {
                var qty = this.options[this.selectedIndex] ? parseInt(this.options[this.selectedIndex].getAttribute('data-qty') || 1) : 1;
                document.getElementById('sell-quantity').max = qty;
                document.getElementById('sell-quantity').value = Math.min(qty, 1);
            };
        }
    });
}

function createMarketListing() {
    var itemId = document.getElementById('sell-item-select').value;
    var priceGold = parseInt(document.getElementById('sell-price-gold').value) || 0;
    var priceCrystals = parseInt(document.getElementById('sell-price-crystals').value) || 0;
    var quantity = parseInt(document.getElementById('sell-quantity').value) || 1;
    var description = document.getElementById('sell-description').value;
    
    if (!itemId) {
        showToast('Выберите предмет', 'error');
        return;
    }
    if (priceGold < 1 && priceCrystals < 1) {
        showToast('Укажите цену', 'error');
        return;
    }
    
    api('createMarketListing', {
        item_id: itemId,
        price_gold: priceGold,
        price_crystals: priceCrystals,
        quantity: quantity,
        description: description
    }, function(result) {
        if (result.success) {
            showToast('Предмет выставлен на продажу!', 'success');
            document.getElementById('sell-item-select').value = '';
            document.getElementById('sell-price-gold').value = '';
            document.getElementById('sell-price-crystals').value = '';
            document.getElementById('sell-quantity').value = 1;
            document.getElementById('sell-description').value = '';
            loadInventory();
            loadMyMarketListings();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function loadMyMarketListings() {
    api('getMyMarketListings', {}, function(result) {
        if (result.success) {
            renderMyListings(result.listings);
        }
    });
}

function renderMyListings(listings) {
    var container = document.getElementById('my-listings');
    if (!container) return;
    
    if (!listings || listings.length === 0) {
        container.innerHTML = '<div class="empty-state">У вас нет активных объявлений</div>';
        return;
    }
    
    var html = '';
    var typeIcons = { weapon: '⚔️', armor: '🛡️', helmet: '⛑️', boots: '👢', ring: '💍', amulet: '📿', potion: '🧪', food: '🍖', material: '🪨' };
    var rarityLabels = { common: 'Обычный', uncommon: 'Необычный', rare: 'Редкий', epic: 'Эпический', legendary: 'Легендарный' };
    
    listings.forEach(function(item) {
        var statusHtml = item.is_active ? '<span style="color:#10b981">Активно</span>' : '<span style="color:#ef4444">Завершено</span>';
        var priceHtml = '';
        if (item.price_gold > 0) priceHtml += '💰 ' + item.price_gold + ' ';
        if (item.price_crystals > 0) priceHtml += '💎 ' + item.price_crystals;
        
        html += '<div class="market-item">';
        html += '<div class="market-item-header">';
        html += '<div><div class="market-item-name">' + (typeIcons[item.item_type] || '📦') + ' ' + item.item_name + '</div>';
        html += '<div class="market-item-type">x' + item.quantity + '</div></div>';
        html += '<div class="market-item-rarity rarity-' + item.item_rarity + '">' + rarityLabels[item.item_rarity] + '</div>';
        html += '</div>';
        html += '<div class="market-item-price">Цена: ' + priceHtml + '</div>';
        html += '<div class="market-item-seller">Статус: ' + statusHtml + '</div>';
        if (item.is_active) {
            html += '<div class="market-item-actions"><button class="btn-cancel" onclick="cancelMarketListing(' + item.id + ')">Снять с продажи</button></div>';
        }
        html += '</div>';
    });
    
    container.innerHTML = html;
}

function cancelMarketListing(listingId) {
    if (!confirm('Снять предмет с продажи?')) return;
    
    api('cancelMarketListing', { listing_id: listingId }, function(result) {
        if (result.success) {
            showToast('Предмет снят с продажи', 'success');
            loadMyMarketListings();
            loadInventory();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

// Auto refresh chat and online
setInterval(function() { loadChat(); loadOnlineCount(); }, 3000);

// ================================
// GUILD WARS
// ================================

function showWarTab(tab) {
    document.querySelectorAll('.war-tab').forEach(function(t) { t.classList.remove('active'); });
    event.target.classList.add('active');
    
    document.getElementById('war-active').style.display = tab === 'active' ? 'block' : 'none';
    document.getElementById('war-my').style.display = tab === 'my' ? 'block' : 'none';
    document.getElementById('war-leaderboard').style.display = tab === 'leaderboard' ? 'block' : 'none';
    
    if (tab === 'active') loadActiveWars();
    if (tab === 'my') loadMyGuildWarStatus();
    if (tab === 'leaderboard') loadWarLeaderboard();
}

function loadGuildWars() {
    loadActiveWars();
}

function loadActiveWars() {
    api('getGuildWars', {}, function(result) {
        var container = document.getElementById('active-wars-list');
        if (!container) return;
        
        if (!result.wars || result.wars.length === 0) {
            container.innerHTML = '<div class="empty-state">Нет активных войн</div>';
            return;
        }
        
        var html = '';
        result.wars.forEach(function(war) {
            html += '<div class="war-card">';
            html += '<div class="war-card-header">';
            html += '<span class="war-status active">⚔️ Война</span>';
            html += '<span style="color:var(--text-muted)">Началась: ' + formatDate(war.start_time) + '</span>';
            html += '</div>';
            html += '<div class="war-guilds">';
            html += '<div class="war-guild"><div class="war-guild-name">' + war.attacker_name + '</div><div class="war-guild-level">ур.' + war.attacker_level + '</div></div>';
            html += '<div class="war-vs">VS</div>';
            html += '<div class="war-guild"><div class="war-guild-name">' + war.defender_name + '</div><div class="war-guild-level">ур.' + war.defender_level + '</div></div>';
            html += '</div>';
            html += '<div class="war-score">';
            html += '<span>' + war.attacker_score + ' 👑</span>';
            html += '<span>' + war.defender_score + ' 💀</span>';
            html += '</div>';
            html += '</div>';
        });
        
        container.innerHTML = html;
    });
}

function loadMyGuildWarStatus() {
    api('getMyGuildWarStatus', {}, function(result) {
        var statusContainer = document.getElementById('my-war-status');
        var enemiesContainer = document.getElementById('war-enemies');
        var btnContainer = document.getElementById('btn-declare-war');
        
        if (!result.in_guild) {
            statusContainer.innerHTML = '<p>Вы не состоите в гильдии</p>';
            enemiesContainer.innerHTML = '';
            if (btnContainer) btnContainer.style.display = 'none';
            return;
        }
        
        if (btnContainer) {
            btnContainer.style.display = result.active_war ? 'none' : 'block';
        }
        
        if (!result.active_war) {
            statusContainer.innerHTML = '<h3>🏰 ' + result.guild.name + '</h3><p>Ваша гильдия не участвует в войнах</p>';
            statusContainer.innerHTML += '<p>Побед: ' + (result.stats?.wins || 0) + ' | Убийств: ' + (result.stats?.kills || 0) + '</p>';
            enemiesContainer.innerHTML = '<p>Нет активных врагов</p>';
            return;
        }
        
        var war = result.active_war;
        var isAttacker = war.attacker_guild_id === result.guild.id;
        
        statusContainer.innerHTML = '<h3>⚔️ ВОЙНА!</h3>';
        statusContainer.innerHTML += '<p>Противник: ' + (isAttacker ? war.defender_name : war.attacker_name) + '</p>';
        statusContainer.innerHTML += '<p>Счёт: ' + (isAttacker ? war.attacker_score : war.defender_score) + ' - ' + (isAttacker ? war.defender_score : war.attacker_score) + '</p>';
        statusContainer.innerHTML += '<p>Ваши убийства: ' + (result.stats?.kills || 0) + ' | Смерти: ' + (result.stats?.deaths || 0) + '</p>';
        
        var html = '<h4>Участники войны</h4>';
        if (result.top_kills && result.top_kills.length > 0) {
            html += '<div class="war-enemies">';
            result.top_kills.forEach(function(p) {
                html += '<div class="war-enemy">';
                html += '<span>🎮 ' + p.username + '</span>';
                html += '<span>⚔️' + p.kills + ' 💀' + p.deaths + '</span>';
                html += '</div>';
            });
            html += '</div>';
        } else {
            html += '<p>Нет участников</p>';
        }
        
        enemiesContainer.innerHTML = html;
    });
}

function loadWarLeaderboard() {
    api('getGuildWarLeaderboard', {}, function(result) {
        var container = document.getElementById('war-leaderboard-list');
        if (!container) return;
        
        if (!result.leaderboard || result.leaderboard.length === 0) {
            container.innerHTML = '<div class="empty-state">Нет данных</div>';
            return;
        }
        
        var html = '<div class="war-lb-row" style="font-weight:700;background:var(--bg-hover)">';
        html += '<span>#</span><span>Гильдия</span><span>Уровень</span><span>Победы</span></div>';
        
        result.leaderboard.forEach(function(g, i) {
            var rankClass = i === 0 ? 'gold' : (i === 1 ? 'silver' : (i === 2 ? 'bronze' : ''));
            html += '<div class="war-lb-row">';
            html += '<span class="lb-rank ' + rankClass + '">' + (i + 1) + '</span>';
            html += '<span>' + g.name + '</span>';
            html += '<span>' + g.level + '</span>';
            html += '<span>' + g.wins + '</span>';
            html += '</div>';
        });
        
        container.innerHTML = html;
    });
}

function declareWar() {
    api('getGuilds', {}, function(result) {
        var modal = document.getElementById('declare-war-modal');
        var list = document.getElementById('guild-select-list');
        
        if (!result.guilds || result.guilds.length === 0) {
            showToast('Нет гильдий для войны', 'error');
            return;
        }
        
        var html = '';
        result.guilds.forEach(function(g) {
            html += '<div class="guild-select-item" onclick="startWarWithGuild(' + g.id + ', \'' + g.name + '\')">';
            html += '<strong>' + g.name + '</strong> (ур.' + g.level + ')';
            html += '</div>';
        });
        
        list.innerHTML = html;
        modal.style.display = 'flex';
    });
}

function startWarWithGuild(guildId, guildName) {
    if (!confirm('Объявить войну гильдии ' + guildName + ' за 500 золота?')) return;
    
    api('declareGuildWar', { guild_id: guildId }, function(result) {
        if (result.success) {
            showToast('Война объявлена гильдии ' + result.target_guild + '!', 'success');
            closeDeclareWarModal();
            loadMyGuildWarStatus();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function closeDeclareWarModal() {
    var modal = document.getElementById('declare-war-modal');
    if (modal) modal.style.display = 'none';
}

function joinWar() {
    api('joinGuildWar', {}, function(result) {
        if (result.success) {
            showToast('Вы присоединились к войне!', 'success');
            loadMyGuildWarStatus();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    var d = new Date(dateStr);
    return d.toLocaleDateString('ru-RU') + ' ' + d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
}

// ================================
// ADMIN
// ================================

var isAdmin = false;

window.showAdmin = function() {
    var username = prompt('Админ логин:');
    if (!username) return;
    var password = prompt('Админ пароль:');
    if (!password) return;
    api('adminLogin', { username: username, password: password }, function(result) {
        if (result.success) {
            isAdmin = true;
            document.querySelectorAll('main > section').forEach(function(s) { s.style.display = 'none'; });
            document.getElementById('admin-section').style.display = 'block';
            loadAdminUsers();
            showToast('Добро пожаловать, админ!', 'success');
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
};

function showAdminTab(tab) {
    document.querySelectorAll('.admin-tab').forEach(function(b) { b.classList.remove('active'); });
    if (event && event.target) event.target.classList.add('active');
    
    document.querySelectorAll('.admin-content').forEach(function(c) { c.style.display = 'none'; });
    var el = document.getElementById('admin-' + tab);
    if (el) el.style.display = 'block';
    
    switch(tab) {
        case 'users': loadAdminUsers(); break;
        case 'items': loadAdminItems(); break;
        case 'enemies': loadAdminEnemies(); break;
        case 'quests': loadAdminQuests(); break;
        case 'guilds': loadAdminGuilds(); break;
        case 'raids': loadAdminRaids(); break;
        case 'stats': loadAdminStats(); break;
    }
}

function showAdminTab(tab) {
    document.querySelectorAll('.admin-tab').forEach(function(b) { b.classList.remove('active'); });
    event.target.classList.add('active');
    
    document.querySelectorAll('.admin-content').forEach(function(c) { c.style.display = 'none'; });
    document.getElementById('admin-' + tab).style.display = 'block';
    
    switch(tab) {
        case 'users': loadAdminUsers(); break;
        case 'items': loadAdminItems(); break;
        case 'enemies': loadAdminEnemies(); break;
        case 'quests': loadAdminQuests(); break;
        case 'guilds': loadAdminGuilds(); break;
        case 'raids': loadAdminRaids(); break;
        case 'stats': loadAdminStats(); break;
    }
}

function adminSearchUser() {
    var search = document.getElementById('admin-user-search').value;
    api('adminGetUsers', { search: search }, function(result) {
        renderAdminUsers(result.users || []);
    });
}

function loadAdminUsers() {
    api('adminGetUsers', {}, function(result) {
        if (result.success) {
            renderAdminUsers(result.users || []);
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function renderAdminUsers(users) {
    var tbody = document.getElementById('admin-users-body');
    if (!tbody) return;
    
    if (!users.length) {
        tbody.innerHTML = '<tr><td colspan="8">Нет игроков</td></tr>';
        return;
    }
    
    var html = '';
    for (var i = 0; i < users.length; i++) {
        var u = users[i];
        var classIcon = { warrior: '⚔️', mage: '🔮', archer: '🏹', rogue: '🗡️', paladin: '🛡️' }[u.class] || '⚔️';
        html += '<tr>' +
            '<td>' + u.id + '</td>' +
            '<td>' + classIcon + ' ' + u.username + '</td>' +
            '<td>' + u.class + '</td>' +
            '<td>' + u.level + '</td>' +
            '<td>' + u.hp + '/' + u.max_hp + '</td>' +
            '<td>' + u.gold + '</td>' +
            '<td>' + u.pvp_rating + '</td>' +
            '<td>' +
                '<button onclick="adminEditUser(' + u.id + ')">✏️</button>' +
                '<button onclick="adminDeleteUser(' + u.id + ')">🗑️</button>' +
                '<button onclick="adminResetUserPass(' + u.id + ')">🔑</button>' +
            '</td>' +
        '</tr>';
    }
    tbody.innerHTML = html;
}

function adminEditUser(userId) {
    var field = prompt('Введите поле для изменения (level, gold, crystals, hp, atk, def, pvp_rating, pvp_wins):');
    if (!field) return;
    var value = prompt('Введите новое значение:');
    if (!value) return;
    
    var data = { user_id: userId };
    data[field] = isNaN(value) ? value : parseInt(value);
    
    api('adminUpdateUser', data, function(result) {
        if (result.success) {
            showToast('Обновлено!', 'success');
            loadAdminUsers();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function adminDeleteUser(userId) {
    if (!confirm('Удалить игрока?')) return;
    api('adminDeleteUser', { user_id: userId }, function(result) {
        if (result.success) {
            showToast('Удалён!', 'success');
            loadAdminUsers();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function adminResetUserPass(userId) {
    var newPass = prompt('Новый пароль (или пусто для hero123):') || 'hero123';
    api('adminResetPassword', { user_id: userId, new_password: newPass }, function(result) {
        if (result.success) {
            showToast('Пароль: ' + result.new_password, 'success');
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function loadAdminItems() {
    api('adminGetItems', {}, function(result) {
        if (result.success) {
            renderAdminItems(result.items || []);
        }
    });
}

function renderAdminItems(items) {
    var tbody = document.getElementById('admin-items-body');
    if (!tbody) return;
    
    var html = '';
    for (var i = 0; i < items.length; i++) {
        var it = items[i];
        html += '<tr>' +
            '<td>' + it.id + '</td>' +
            '<td>' + it.name + '</td>' +
            '<td>' + it.type + '</td>' +
            '<td>' + it.rarity + '</td>' +
            '<td>' + it.value + '</td>' +
            '<td>' + it.atk_bonus + '</td>' +
            '<td>' + it.def_bonus + '</td>' +
            '<td>' +
                '<button onclick="editAdminItem(' + it.id + ')">✏️</button>' +
                '<button onclick="deleteAdminItem(' + it.id + ')">🗑️</button>' +
            '</td>' +
        '</tr>';
    }
    tbody.innerHTML = html;
}

function showAdminItemForm() {
    document.getElementById('admin-item-form').style.display = 'block';
}

function hideAdminItemForm() {
    document.getElementById('admin-item-form').style.display = 'none';
}

function saveAdminItem() {
    var data = {
        name: document.getElementById('item-name').value,
        type: document.getElementById('item-type').value,
        rarity: document.getElementById('item-rarity').value,
        value: parseInt(document.getElementById('item-value').value) || 10,
        required_level: parseInt(document.getElementById('item-required-level').value) || 1,
        atk_bonus: parseInt(document.getElementById('item-atk-bonus').value) || 0,
        def_bonus: parseInt(document.getElementById('item-def-bonus').value) || 0,
        hp_bonus: parseInt(document.getElementById('item-hp-bonus').value) || 0,
        mp_bonus: parseInt(document.getElementById('item-mp-bonus').value) || 0,
        description: document.getElementById('item-description').value
    };
    
    api('adminSaveItem', data, function(result) {
        if (result.success) {
            showToast('Сохранено!', 'success');
            hideAdminItemForm();
            loadAdminItems();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

function editAdminItem(itemId) {
    showToast('Редактирование ID: ' + itemId, 'info');
}

function deleteAdminItem(itemId) {
    if (!confirm('Удалить предмет?')) return;
    api('adminDeleteItem', { item_id: itemId }, function(result) {
        if (result.success) {
            showToast('Удалён!', 'success');
            loadAdminItems();
        }
    });
}

function loadAdminEnemies() {
    api('adminGetEnemies', {}, function(result) {
        if (result.success) {
            renderAdminEnemies(result.enemies || []);
        }
    });
}

function renderAdminEnemies(enemies) {
    var tbody = document.getElementById('admin-enemies-body');
    if (!tbody) return;
    
    var html = '';
    for (var i = 0; i < enemies.length; i++) {
        var e = enemies[i];
        html += '<tr>' +
            '<td>' + e.id + '</td>' +
            '<td>' + (e.is_boss ? '👑 ' : '') + e.name + '</td>' +
            '<td>' + e.level + '</td>' +
            '<td>' + e.hp + '</td>' +
            '<td>' + e.atk + '</td>' +
            '<td>' + e.def + '</td>' +
            '<td>' + e.exp_reward + '</td>' +
            '<td>' + e.gold_reward + '</td>' +
            '<td>' + (e.is_boss ? 'Да' : 'Нет') + '</td>' +
            '<td>' +
                '<button onclick="deleteAdminEnemy(' + e.id + ')">🗑️</button>' +
            '</td>' +
        '</tr>';
    }
    tbody.innerHTML = html;
}

function showAdminEnemyForm() {
    document.getElementById('admin-enemy-form').style.display = 'block';
}

function hideAdminEnemyForm() {
    document.getElementById('admin-enemy-form').style.display = 'none';
}

function saveAdminEnemy() {
    var data = {
        name: document.getElementById('enemy-name').value,
        level: parseInt(document.getElementById('enemy-level').value) || 1,
        hp: parseInt(document.getElementById('enemy-hp').value) || 50,
        atk: parseInt(document.getElementById('enemy-atk').value) || 5,
        def: parseInt(document.getElementById('enemy-def').value) || 2,
        exp_reward: parseInt(document.getElementById('enemy-exp').value) || 10,
        gold_reward: parseInt(document.getElementById('enemy-gold').value) || 5,
        crystals_reward: parseInt(document.getElementById('enemy-crystals').value) || 0,
        is_boss: document.getElementById('enemy-boss').checked ? 1 : 0
    };
    
    api('adminSaveEnemy', data, function(result) {
        if (result.success) {
            showToast('Сохранено!', 'success');
            hideAdminEnemyForm();
            loadAdminEnemies();
        }
    });
}

function deleteAdminEnemy(enemyId) {
    if (!confirm('Удалить монстра?')) return;
    api('adminDeleteEnemy', { enemy_id: enemyId }, function(result) {
        if (result.success) loadAdminEnemies();
    });
}

function loadAdminQuests() {
    api('adminGetQuests', {}, function(result) {
        if (result.success) renderAdminQuests(result.quests || []);
    });
}

function renderAdminQuests(quests) {
    var tbody = document.getElementById('admin-quests-body');
    if (!tbody) return;
    
    var html = '';
    for (var i = 0; i < quests.length; i++) {
        var q = quests[i];
        html += '<tr>' +
            '<td>' + q.id + '</td>' +
            '<td>' + q.title + '</td>' +
            '<td>' + q.type + '</td>' +
            '<td>' + q.target_id + '</td>' +
            '<td>' + q.exp_reward + '</td>' +
            '<td>' + q.gold_reward + '</td>' +
            '<td>' + q.required_level + '</td>' +
            '<td><button onclick="deleteAdminQuest(' + q.id + ')">🗑️</button></td>' +
        '</tr>';
    }
    tbody.innerHTML = html;
}

function showAdminQuestForm() {
    document.getElementById('admin-quest-form').style.display = 'block';
}

function hideAdminQuestForm() {
    document.getElementById('admin-quest-form').style.display = 'none';
}

function saveAdminQuest() {
    var data = {
        title: document.getElementById('quest-title').value,
        description: document.getElementById('quest-desc').value,
        type: document.getElementById('quest-type').value,
        target_id: parseInt(document.getElementById('quest-target').value) || 0,
        target_count: parseInt(document.getElementById('quest-count').value) || 1,
        exp_reward: parseInt(document.getElementById('quest-exp').value) || 50,
        gold_reward: parseInt(document.getElementById('quest-gold').value) || 25,
        required_level: parseInt(document.getElementById('quest-level').value) || 1
    };
    
    api('adminSaveQuest', data, function(result) {
        if (result.success) {
            showToast('Сохранено!', 'success');
            hideAdminQuestForm();
            loadAdminQuests();
        }
    });
}

function deleteAdminQuest(questId) {
    if (!confirm('Удалить квест?')) return;
    api('adminDeleteQuest', { quest_id: questId }, function(result) {
        if (result.success) loadAdminQuests();
    });
}

function loadAdminGuilds() {
    api('adminGetGuilds', {}, function(result) {
        if (result.success) renderAdminGuilds(result.guilds || []);
    });
}

function renderAdminGuilds(guilds) {
    var tbody = document.getElementById('admin-guilds-body');
    if (!tbody) return;
    
    var html = '';
    for (var i = 0; i < guilds.length; i++) {
        var g = guilds[i];
        html += '<tr>' +
            '<td>' + g.id + '</td>' +
            '<td>' + g.name + '</td>' +
            '<td>' + g.leader_name + '</td>' +
            '<td>' + g.level + '</td>' +
            '<td>' + g.exp + '</td>' +
            '<td>' + g.gold + '</td>' +
            '<td><button onclick="deleteAdminGuild(' + g.id + ')">🗑️</button></td>' +
        '</tr>';
    }
    tbody.innerHTML = html;
}

function deleteAdminGuild(guildId) {
    if (!confirm('Удалить гильдию?')) return;
    api('adminDeleteGuild', { guild_id: guildId }, function(result) {
        if (result.success) loadAdminGuilds();
    });
}

function loadAdminRaids() {
    api('adminGetRaids', {}, function(result) {
        if (result.success) renderAdminRaids(result.raids || []);
    });
}

function renderAdminRaids(raids) {
    var tbody = document.getElementById('admin-raids-body');
    if (!tbody) return;
    
    var html = '';
    for (var i = 0; i < raids.length; i++) {
        var r = raids[i];
        html += '<tr>' +
            '<td>' + r.id + '</td>' +
            '<td>🔥 ' + r.name + '</td>' +
            '<td>' + r.level + '</td>' +
            '<td>' + r.hp + '</td>' +
            '<td>' + r.atk + '</td>' +
            '<td>' + r.exp_reward + '</td>' +
            '<td>' + r.gold_reward + '</td>' +
            '<td>' + r.participants_limit + '</td>' +
            '<td>' + (r.is_active ? 'Да' : 'Нет') + '</td>' +
            '<td><button onclick="deleteAdminRaid(' + r.id + ')">🗑️</button></td>' +
        '</tr>';
    }
    tbody.innerHTML = html;
}

function showAdminRaidForm() {
    document.getElementById('admin-raid-form').style.display = 'block';
}

function hideAdminRaidForm() {
    document.getElementById('admin-raid-form').style.display = 'none';
}

function saveAdminRaid() {
    var data = {
        name: document.getElementById('raid-name').value,
        description: document.getElementById('raid-desc').value,
        level: parseInt(document.getElementById('raid-level').value) || 10,
        hp: parseInt(document.getElementById('raid-hp').value) || 500,
        atk: parseInt(document.getElementById('raid-atk').value) || 30,
        exp_reward: parseInt(document.getElementById('raid-exp').value) || 200,
        gold_reward: parseInt(document.getElementById('raid-gold').value) || 150,
        participants_limit: parseInt(document.getElementById('raid-limit').value) || 10,
        is_active: document.getElementById('raid-active').checked ? 1 : 0
    };
    
    api('adminSaveRaid', data, function(result) {
        if (result.success) {
            showToast('Сохранено!', 'success');
            hideAdminRaidForm();
            loadAdminRaids();
        }
    });
}

function deleteAdminRaid(raidId) {
    if (!confirm('Удалить рейд?')) return;
    api('adminDeleteRaid', { raid_id: raidId }, function(result) {
        if (result.success) loadAdminRaids();
    });
}

function loadAdminStats() {
    api('adminGetStats', {}, function(result) {
        if (result.success && result.stats) {
            var s = result.stats;
            document.getElementById('stat-total-users').textContent = s.total_users;
            document.getElementById('stat-pvp-wins').textContent = s.pvp_wins;
            document.getElementById('stat-total-gold').textContent = s.total_gold;
            document.getElementById('stat-total-guilds').textContent = s.total_guilds;
            document.getElementById('stat-total-quests').textContent = s.total_quests;
            document.getElementById('stat-total-raids').textContent = s.total_raids;
        }
    });
}

function saveAdminSettings() {
    showToast('Настройки сохранены!', 'success');
}

// Expose admin functions to window
window.showAdminTab = showAdminTab;
window.loadAdminUsers = loadAdminUsers;
window.loadAdminItems = loadAdminItems;
window.loadAdminEnemies = loadAdminEnemies;
window.loadAdminQuests = loadAdminQuests;
window.loadAdminGuilds = loadAdminGuilds;
window.loadAdminRaids = loadAdminRaids;
window.loadAdminStats = loadAdminStats;
window.adminSearchUser = adminSearchUser;
window.adminEditUser = adminEditUser;
window.adminDeleteUser = adminDeleteUser;
window.adminResetUserPass = adminResetUserPass;
window.showAdminItemForm = showAdminItemForm;
window.hideAdminItemForm = hideAdminItemForm;
window.saveAdminItem = saveAdminItem;
window.deleteAdminItem = deleteAdminItem;
window.showAdminEnemyForm = showAdminEnemyForm;
window.hideAdminEnemyForm = hideAdminEnemyForm;
window.saveAdminEnemy = saveAdminEnemy;
window.deleteAdminEnemy = deleteAdminEnemy;
window.showAdminQuestForm = showAdminQuestForm;
window.hideAdminQuestForm = hideAdminQuestForm;
window.saveAdminQuest = saveAdminQuest;
window.deleteAdminQuest = deleteAdminQuest;
window.deleteAdminGuild = deleteAdminGuild;
window.showAdminRaidForm = showAdminRaidForm;
window.hideAdminRaidForm = hideAdminRaidForm;
window.saveAdminRaid = saveAdminRaid;
window.deleteAdminRaid = deleteAdminRaid;
window.saveAdminSettings = saveAdminSettings;

// Start - only if not already initialized by game.php
if (typeof router === 'undefined') {
    document.addEventListener('DOMContentLoaded', init);
}