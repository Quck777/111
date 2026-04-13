/**
 * Medieval Realm RPG - CHAT
 * Chat system
 */

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

// Auto refresh chat
setInterval(function() { 
    if (currentUser && document.getElementById('chat-section')) loadChat(); 
}, 5000);

// Expose to window
window.chatChannel = chatChannel;
window.lastChatId = lastChatId;
window.setChatChannel = setChatChannel;
window.loadChat = loadChat;
window.sendMessage = sendMessage;
window.sendMsg = sendMessage; // alias for sections.php
window.insertEmoji = insertEmoji;
window.toggleEmojiPicker = toggleEmojiPicker;
window.showUserProfile = showUserProfile;