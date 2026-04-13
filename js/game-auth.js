/**
 * Medieval Realm RPG - AUTH
 * Login, registration, user session
 */

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
    
    var clickEl = document.querySelector('[data-slot="' + slotType + '"]');
    if (clickEl) {
        var rect = clickEl.getBoundingClientRect();
        tooltip.style.left = rect.left + 'px';
        tooltip.style.top = (rect.bottom + 10) + 'px';
    }
    
    setTimeout(function() {
        document.addEventListener('click', hideTooltip);
    }, 100);
}

function hideTooltip() {
    var tooltip = document.getElementById('item-tooltip');
    if (tooltip) tooltip.style.display = 'none';
    document.removeEventListener('click', hideTooltip);
}

// Expose to window
window.showAuth = showAuth;
window.register = register;
window.updateClassPreview = updateClassPreview;
window.login = login;
window.logout = logout;
window.showGame = showGame;
window.refreshUser = refreshUser;
window.updateUserStats = updateUserStats;
window.loadCharacter = loadCharacter;
window.updateEquipmentSlots = updateEquipmentSlots;
window.showSlotInfo = showSlotInfo;
window.hideTooltip = hideTooltip;