/**
 * Medieval Realm RPG - BATTLE
 * PvE Battle system
 */

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

// Expose to window
window.loadEnemies = loadEnemies;
window.startBattle = startBattle;
window.showBattleUI = showBattleUI;
window.updateBattleDisplay = updateBattleDisplay;
window.attack = attack;
window.cancelBattle = cancelBattle;
window.useBattlePotion = useBattlePotion;
window.fleeBattle = fleeBattle;
window.selectAttackType = selectAttackType;
window.showEnemyType = showEnemyType;
window.showBattleSkills = showBattleSkills;