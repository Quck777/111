/**
 * ФОРПОСТ - Основной JavaScript файл игры
 */

// Глобальное состояние приложения
const AppState = {
    user: null,
    currentLocation: null,
    inCombat: false,
    chatChannel: 'global',
    lastUpdate: Date.now()
};

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    console.log('ФОРПОСТ загружен');
    
    // Инициализация компонентов
    initNavigation();
    initChat();
    initInventory();
    initCharacterPanel();
    startGameLoop();
    
    // Проверка авторизации
    checkAuthStatus();
});

/**
 * Проверка статуса авторизации
 */
async function checkAuthStatus() {
    try {
        const response = await API.get('auth/status.php');
        if (response && response.success && response.user) {
            AppState.user = response.user;
            updateUserInterface();
            console.log('Пользователь авторизован:', AppState.user.username);
        } else {
            console.log('Пользователь не авторизован');
        }
    } catch (error) {
        // Тихая ошибка - пользователь просто не авторизован
        console.log('Статус авторизации: гость');
    }
}

/**
 * Обновление пользовательского интерфейса
 */
function updateUserInterface() {
    if (!AppState.user) return;
    
    // Обновление статистики
    updateStatsPanel();
    updateEnergyBar();
    updateExperienceBar();
    updateGoldDisplay();
    
    // Обновление имени пользователя
    const usernameElements = document.querySelectorAll('.user-username');
    usernameElements.forEach(el => {
        el.textContent = AppState.user.username;
    });
}

/**
 * Обновление панели статистики
 */
function updateStatsPanel() {
    if (!AppState.user) return;
    
    const statsMap = {
        'stat-strength': AppState.user.strength,
        'stat-agility': AppState.user.agility,
        'stat-intelligence': AppState.user.intelligence,
        'stat-stamina': AppState.user.stamina,
        'stat-luck': AppState.user.luck
    };
    
    Object.entries(statsMap).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            Utils.animateNumber(element, parseInt(element.textContent) || 0, value, 500);
        }
    });
}

/**
 * Обновление полоски энергии
 */
function updateEnergyBar() {
    if (!AppState.user) return;
    
    const energyBar = document.getElementById('energy-bar');
    const energyText = document.getElementById('energy-text');
    
    if (energyBar && energyText) {
        const percentage = (AppState.user.energy / AppState.user.max_energy) * 100;
        energyBar.style.width = `${percentage}%`;
        energyText.textContent = `${AppState.user.energy}/${AppState.user.max_energy}`;
    }
}

/**
 * Обновление полоски опыта
 */
function updateExperienceBar() {
    if (!AppState.user) return;
    
    const xpBar = document.getElementById('xp-bar');
    const xpText = document.getElementById('xp-text');
    
    if (xpBar && xpText) {
        // Расчет опыта для следующего уровня
        const xpToNextLevel = AppState.user.level * 1000;
        const previousLevelXp = (AppState.user.level - 1) * 1000;
        const progress = AppState.user.experience - previousLevelXp;
        const total = xpToNextLevel - previousLevelXp;
        const percentage = (progress / total) * 100;
        
        xpBar.style.width = `${Math.min(percentage, 100)}%`;
        xpText.textContent = `Уровень ${AppState.user.level} (${Utils.formatNumber(AppState.user.experience)} XP)`;
    }
}

/**
 * Обновление отображения золота
 */
function updateGoldDisplay() {
    if (!AppState.user) return;
    
    const goldElements = document.querySelectorAll('.gold-amount');
    goldElements.forEach(el => {
        el.textContent = Utils.formatNumber(AppState.user.gold);
    });
}

/**
 * Инициализация навигации
 */
function initNavigation() {
    // Обработка кликов по навигационным ссылкам
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
            // Убираем активный класс у всех ссылок
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            // Добавляем активный класс текущей ссылке
            e.target.classList.add('active');
        });
    });
}

/**
 * Инициализация чата
 */
function initChat() {
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    
    if (!chatForm || !chatInput || !chatMessages) return;
    
    // Отправка сообщения
    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (!message) return;
        
        try {
            const response = await API.sendMessage(message, AppState.chatChannel);
            if (response.success) {
                chatInput.value = '';
                loadChatMessages();
            }
        } catch (error) {
            console.error('Ошибка отправки сообщения:', error);
        }
    });
    
    // Загрузка сообщений
    loadChatMessages();
    
    // Автообновление чата каждые 3 секунды
    setInterval(loadChatMessages, 3000);
}

/**
 * Загрузка сообщений чата
 */
async function loadChatMessages() {
    const chatMessages = document.getElementById('chat-messages');
    if (!chatMessages) return;
    
    try {
        const response = await API.getMessages(AppState.chatChannel, 50);
        if (response.success) {
            renderChatMessages(response.messages);
        }
    } catch (error) {
        console.error('Ошибка загрузки сообщений:', error);
    }
}

/**
 * Отрисовка сообщений чата
 */
function renderChatMessages(messages) {
    const chatMessages = document.getElementById('chat-messages');
    if (!chatMessages) return;
    
    chatMessages.innerHTML = messages.map(msg => `
        <div class="chat-message">
            <span class="username">${escapeHtml(msg.username)}</span>
            <span class="time">[${new Date(msg.created_at).toLocaleTimeString()}]</span>
            <span class="message">${escapeHtml(msg.message)}</span>
        </div>
    `).join('');
    
    // Прокрутка вниз
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

/**
 * Инициализация инвентаря
 */
function initInventory() {
    loadInventory();
}

/**
 * Загрузка инвентаря
 */
async function loadInventory() {
    const inventoryGrid = document.getElementById('inventory-grid');
    if (!inventoryGrid) return;
    
    try {
        const response = await API.getInventory();
        if (response.success) {
            renderInventory(response.items);
        }
    } catch (error) {
        console.error('Ошибка загрузки инвентаря:', error);
    }
}

/**
 * Отрисовка инвентаря
 */
function renderInventory(items) {
    const inventoryGrid = document.getElementById('inventory-grid');
    if (!inventoryGrid) return;
    
    // Создаем пустые слоты
    const maxSlots = 50;
    const slots = [];
    
    for (let i = 0; i < maxSlots; i++) {
        const item = items.find(item => item.slot_number === i);
        slots.push(createInventorySlot(item, i));
    }
    
    inventoryGrid.innerHTML = '';
    slots.forEach(slot => inventoryGrid.appendChild(slot));
}

/**
 * Создание слота инвентаря
 */
function createInventorySlot(item, slotNumber) {
    const slot = document.createElement('div');
    slot.className = 'inventory-slot';
    slot.dataset.slot = slotNumber;
    
    if (item) {
        const img = document.createElement('img');
        img.src = item.image_path || 'assets/images/items/default.png';
        img.alt = item.name;
        slot.appendChild(img);
        
        if (item.quantity > 1) {
            const qty = document.createElement('span');
            qty.className = 'slot-quantity';
            qty.textContent = item.quantity;
            slot.appendChild(qty);
        }
        
        // Tooltip с информацией о предмете
        slot.title = `${item.name}\n${item.description}\nЦена: ${item.sell_price}`;
        
        // Клик по предмету
        slot.addEventListener('click', () => handleItemClick(item));
    }
    
    return slot;
}

/**
 * Обработка клика по предмету
 */
function handleItemClick(item) {
    console.log('Предмет:', item);
    // Здесь будет логика взаимодействия с предметом
}

/**
 * Инициализация панели персонажа
 */
function initCharacterPanel() {
    loadCharacterInfo();
}

/**
 * Загрузка информации о персонаже
 */
async function loadCharacterInfo() {
    try {
        const response = await API.get('user/profile.php');
        if (response && response.success) {
            AppState.user = response.profile;
            renderCharacterInfo(response.profile);
            console.log('Профиль загружен успешно');
        }
    } catch (error) {
        // Тихая ошибка - профиль не загружен (пользователь не авторизован)
        console.log('Профиль не загружен (гость)');
    }
}

/**
 * Отрисовка информации о персонаже
 */
function renderCharacterInfo(user) {
    // Обновление аватара
    const avatar = document.getElementById('character-avatar');
    if (avatar) {
        avatar.src = user.avatar_path || 'assets/images/characters/default.png';
    }
    
    // Обновление имени
    const name = document.getElementById('character-name');
    if (name) {
        name.textContent = user.username;
    }
    
    // Обновление ранга
    const rank = document.getElementById('character-rank');
    if (rank) {
        rank.textContent = user.rank_name || 'Новичок';
    }
}

/**
 * Игровой цикл
 */
function startGameLoop() {
    // Обновление каждые 10 секунд
    setInterval(async () => {
        if (AppState.user) {
            try {
                // Обновление состояния пользователя
                const response = await API.get('user/status.php');
                if (response.success) {
                    AppState.user = { ...AppState.user, ...response.user };
                    updateUserInterface();
                }
            } catch (error) {
                console.error('Ошибка обновления статуса:', error);
            }
        }
    }, 10000);
}

/**
 * Экранирование HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Переключение канала чата
 */
function switchChatChannel(channel) {
    AppState.chatChannel = channel;
    document.querySelectorAll('.chat-channel-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.channel === channel);
    });
    loadChatMessages();
}

/**
 * Вход в локацию
 */
async function enterLocation(locationId) {
    try {
        const response = await API.moveTo(locationId);
        if (response.success) {
            AppState.currentLocation = response.location;
            renderLocation(response.location);
            Utils.showNotification(`Вы прибыли в локацию: ${response.location.name}`, 'success');
        }
    } catch (error) {
        console.error('Ошибка перемещения:', error);
    }
}

/**
 * Отрисовка локации
 */
function renderLocation(location) {
    const locationName = document.getElementById('location-name');
    const locationBackground = document.getElementById('location-background');
    const locationDescription = document.getElementById('location-description');
    
    if (locationName) locationName.textContent = location.name;
    if (locationBackground) locationBackground.style.backgroundImage = `url(${location.background_path})`;
    if (locationDescription) locationDescription.textContent = location.description;
}

/**
 * Начало боя с монстром
 */
async function startCombat(monsterId) {
    try {
        const response = await API.attackMonster(monsterId);
        if (response.success) {
            AppState.inCombat = true;
            renderCombat(response.combat);
        }
    } catch (error) {
        console.error('Ошибка начала боя:', error);
    }
}

/**
 * Отрисовка боя
 */
function renderCombat(combatData) {
    const combatContainer = document.getElementById('combat-container');
    if (!combatContainer) return;
    
    combatContainer.style.display = 'block';
    // Здесь будет полная отрисовка интерфейса боя
}

// Экспорт функций для использования в других скриптах
window.GameApp = {
    AppState,
    updateUserInterface,
    loadInventory,
    switchChatChannel,
    enterLocation,
    startCombat
};
