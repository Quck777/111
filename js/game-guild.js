/**
 * Medieval Realm RPG - GUILDS
 * Guild management
 */

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

// Guild Wars
function loadGuildWars() {
    api('getGuildWars', {}, function(wars) {
        var list = document.getElementById('guild-wars-list');
        if (!list) return;
        
        if (!wars || !wars.length) {
            list.innerHTML = '<div class="empty-state">Нет активных войн</div>';
            return;
        }
        
        var html = '';
        wars.forEach(function(w) {
            html += '<div class="guild-war-card">' +
                '<div>' + w.attacker_name + ' ⚔️ ' + w.defender_name + '</div>' +
                '<div>Статус: ' + (w.status === 'active' ? 'Активна' : 'Завершена') + '</div>' +
                '</div>';
        });
        list.innerHTML = html;
    });
}

function declareGuildWar(guildId) {
    if (!confirm('Объявить войну?')) return;
    api('declareGuildWar', { guild_id: guildId }, function(result) {
        if (result.success) {
            showToast('Война объявлена!', 'success');
            loadGuildWars();
        } else {
            showToast(result.error || 'Ошибка', 'error');
        }
    });
}

// Expose
window.loadGuilds = loadGuilds;
window.createGuild = createGuild;
window.joinGuild = joinGuild;
window.leaveGuild = leaveGuild;
window.showGuildDonate = showGuildDonate;
window.closeGuildDonate = closeGuildDonate;
window.showGuildTab = showGuildTab;
window.selectDonate = selectDonate;
window.donateToGuild = donateToGuild;
window.loadGuildWars = loadGuildWars;
window.declareGuildWar = declareGuildWar;