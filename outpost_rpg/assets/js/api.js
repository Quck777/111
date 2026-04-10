/**
 * ФОРПОСТ - API клиент для взаимодействия с сервером
 */

const API = {
    baseUrl: 'api',
    
    /**
     * Выполнение запроса к API
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}/${endpoint}`;
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };
        
        const config = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Ошибка запроса');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            Utils.showNotification(error.message, 'error');
            throw error;
        }
    },
    
    /**
     * GET запрос
     */
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url, { method: 'GET' });
    },
    
    /**
     * POST запрос
     */
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },
    
    /**
     * PUT запрос
     */
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },
    
    /**
     * DELETE запрос
     */
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    },
    
    // ==================== Аутентификация ====================
    
    /**
     * Регистрация пользователя
     */
    async register(username, email, password) {
        return this.post('auth/register.php', { username, email, password });
    },
    
    /**
     * Вход пользователя
     */
    async login(username, password) {
        return this.post('auth/login.php', { username, password });
    },
    
    /**
     * Выход пользователя
     */
    async logout() {
        return this.post('auth/logout.php');
    },
    
    // ==================== Пользователь ====================
    
    /**
     * Получение профиля пользователя
     */
    async getProfile(userId = null) {
        const params = userId ? { user_id: userId } : {};
        return this.get('user/profile.php', params);
    },
    
    /**
     * Обновление профиля
     */
    async updateProfile(data) {
        return this.post('user/update.php', data);
    },
    
    /**
     * Получение статистики пользователя
     */
    async getUserStats(userId = null) {
        const params = userId ? { user_id: userId } : {};
        return this.get('user/stats.php', params);
    },
    
    // ==================== Инвентарь ====================
    
    /**
     * Получение инвентаря
     */
    async getInventory() {
        return this.get('inventory/list.php');
    },
    
    /**
     * Использование предмета
     */
    async useItem(itemId) {
        return this.post('inventory/use.php', { item_id: itemId });
    },
    
    /**
     * Экипировка предмета
     */
    async equipItem(itemId) {
        return this.post('inventory/equip.php', { item_id: itemId });
    },
    
    /**
     * Снятие предмета
     */
    async unequipItem(slotType) {
        return this.post('inventory/unequip.php', { slot_type: slotType });
    },
    
    /**
     * Продажа предмета
     */
    async sellItem(itemId, quantity = 1) {
        return this.post('inventory/sell.php', { item_id: itemId, quantity });
    },
    
    // ==================== Бой ====================
    
    /**
     * Атака монстра
     */
    async attackMonster(monsterId) {
        return this.post('combat/attack.php', { monster_id: monsterId });
    },
    
    /**
     * Использование умения в бою
     */
    async useSkill(skillId, targetId = null) {
        return this.post('combat/skill.php', { skill_id: skillId, target_id: targetId });
    },
    
    /**
     * Побег из боя
     */
    async flee() {
        return this.post('combat/flee.php');
    },
    
    // ==================== Локации ====================
    
    /**
     * Получение списка локаций
     */
    async getLocations() {
        return this.get('locations/list.php');
    },
    
    /**
     * Перемещение в локацию
     */
    async moveTo(locationId) {
        return this.post('locations/move.php', { location_id: locationId });
    },
    
    /**
     * Получение информации о локации
     */
    async getLocationInfo(locationId) {
        return this.get('locations/info.php', { location_id: locationId });
    },
    
    // ==================== Чат ====================
    
    /**
     * Отправка сообщения
     */
    async sendMessage(message, channel = 'global') {
        return this.post('chat/send.php', { message, channel });
    },
    
    /**
     * Получение сообщений чата
     */
    async getMessages(channel = 'global', limit = 50) {
        return this.get('chat/messages.php', { channel, limit });
    },
    
    // ==================== Рынок ====================
    
    /**
     * Получение списка товаров на рынке
     */
    async getMarketListings(page = 1, limit = 20) {
        return this.get('market/listings.php', { page, limit });
    },
    
    /**
     * Создание лота на рынке
     */
    async createListing(itemId, price, quantity = 1) {
        return this.post('market/create.php', { item_id: itemId, price, quantity });
    },
    
    /**
     * Покупка товара
     */
    async buyItem(listingId) {
        return this.post('market/buy.php', { listing_id: listingId });
    },
    
    /**
     * Отмена лота
     */
    async cancelListing(listingId) {
        return this.post('market/cancel.php', { listing_id: listingId });
    },
    
    // ==================== Квесты ====================
    
    /**
     * Получение доступных квестов
     */
    async getAvailableQuests() {
        return this.get('quests/available.php');
    },
    
    /**
     * Получение активных квестов
     */
    async getActiveQuests() {
        return this.get('quests/active.php');
    },
    
    /**
     * Принятие квеста
     */
    async acceptQuest(questId) {
        return this.post('quests/accept.php', { quest_id: questId });
    },
    
    /**
     * Сдача квеста
     */
    async completeQuest(questId) {
        return this.post('quests/complete.php', { quest_id: questId });
    },
    
    // ==================== Умения ====================
    
    /**
     * Получение списка умений
     */
    async getSkills() {
        return this.get('skills/list.php');
    },
    
    /**
     * Повышение уровня умения
     */
    async upgradeSkill(skillId) {
        return this.post('skills/upgrade.php', { skill_id: skillId });
    },
    
    // ==================== Достижения ====================
    
    /**
     * Получение достижений
     */
    async getAchievements() {
        return this.get('achievements/list.php');
    },
    
    /**
     * Получение прогресса достижений
     */
    async getAchievementProgress() {
        return this.get('achievements/progress.php');
    },
    
    // ==================== Лидерборд ====================
    
    /**
     * Получение лидерборда
     */
    async getLeaderboard(limit = 10, offset = 0) {
        return this.get('leaderboard.php', { limit, offset });
    },
    
    // ==================== Магазин ====================
    
    /**
     * Получение товаров магазина
     */
    async getShopItems() {
        return this.get('shop/items.php');
    },
    
    /**
     * Покупка в магазине
     */
    async buyFromShop(itemId, quantity = 1) {
        return this.post('shop/buy.php', { item_id: itemId, quantity });
    },
    
    // ==================== Шахта/Ресурсы ====================
    
    /**
     * Добыча ресурса
     */
    async gatherResource(nodeId) {
        return this.post('resources/gather.php', { node_id: nodeId });
    },
    
    /**
     * Крафт предмета
     */
    async craftItem(recipeId, quantity = 1) {
        return this.post('crafting/craft.php', { recipe_id: recipeId, quantity });
    },
    
    // ==================== Энергия ====================
    
    /**
     * Восстановление энергии
     */
    async restoreEnergy(amount = null) {
        return this.post('energy/restore.php', { amount });
    },
    
    /**
     * Проверка статуса энергии
     */
    async checkEnergy() {
        return this.get('energy/status.php');
    }
};

// Экспорт для использования в других модулях
if (typeof module !== 'undefined' && module.exports) {
    module.exports = API;
}
