<?php
/**
 * Admin Section Template
 */
?>
<section id="admin-section" style="display:none;">
    <div class="admin-container">
        <div class="admin-header">
            <h2>👑 Админ-панель</h2>
            <button class="admin-close" onclick="router('character')">✕ Выход</button>
        </div>
        <div class="admin-tabs">
            <button class="admin-tab active" data-tab="users" onclick="adminTab('users')">👤 Игроки</button>
            <button class="admin-tab" data-tab="items" onclick="adminTab('items')">🎒 Вещи</button>
            <button class="admin-tab" data-tab="enemies" onclick="adminTab('enemies')">👹 Монстры</button>
            <button class="admin-tab" data-tab="quests" onclick="adminTab('quests')">📜 Квесты</button>
            <button class="admin-tab" data-tab="guilds" onclick="adminTab('guilds')">🏰 Гильдии</button>
            <button class="admin-tab" data-tab="raids" onclick="adminTab('raids')">🔥 Рейды</button>
            <button class="admin-tab" data-tab="stats" onclick="adminTab('stats')">📊 Статистика</button>
        </div>
        
        <!-- Stats -->
        <div id="admin-stats" class="admin-content">
            <div class="stats-grid">
                <div class="stat-card"><h3>👤 Игроков</h3><div class="value" id="stat-total-users">0</div></div>
                <div class="stat-card"><h3>⚔️ PvP Побед</h3><div class="value" id="stat-pvp-wins">0</div></div>
                <div class="stat-card"><h3>💰 Золота</h3><div class="value" id="stat-total-gold">0</div></div>
                <div class="stat-card"><h3>🏰 Гильдий</h3><div class="value" id="stat-total-guilds">0</div></div>
                <div class="stat-card"><h3>📜 Квестов</h3><div class="value" id="stat-total-quests">0</div></div>
                <div class="stat-card"><h3>🔥 Рейдов</h3><div class="value" id="stat-total-raids">0</div></div>
            </div>
            <button class="admin-btn primary" onclick="loadStats()">🔄 Обновить</button>
        </div>
        
        <!-- Users -->
        <div id="admin-users" class="admin-content" style="display:none;">
            <div class="admin-toolbar">
                <input type="text" id="admin-user-search" class="admin-search" placeholder="Поиск...">
                <button class="admin-btn primary" onclick="loadUsers()">🔄</button>
            </div>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Игрок</th><th>Класс</th><th>Ур.</th><th>HP</th><th>💰</th><th>🏆</th><th>Действ.</th></tr></thead>
                <tbody id="admin-users-body"></tbody>
            </table>
        </div>
        
        <!-- Items -->
        <div id="admin-items" class="admin-content" style="display:none;">
            <div class="admin-toolbar">
                <button class="admin-btn primary" onclick="loadItems()">🔄</button>
                <button class="admin-btn success" onclick="showItemForm()">➕</button>
            </div>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Название</th><th>Тип</th><th>Редкость</th><th>Цена</th><th>АТК</th><th>ЗАЩ</th><th>Действ.</th></tr></thead>
                <tbody id="admin-items-body"></tbody>
            </table>
            <div id="admin-item-form" class="admin-form">
                <h3>Добавить предмет</h3>
                <div class="form-row">
                    <div class="form-group"><label>Название</label><input type="text" id="item-name"></div>
                    <div class="form-group"><label>Тип</label><select id="item-type"><option value="weapon">Оружие</option><option value="armor">Броня</option><option value="potion">Зелье</option></select></div>
                    <div class="form-group"><label>Редкость</label><select id="item-rarity"><option value="common">Обычный</option><option value="rare">Редкий</option></select></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Цена</label><input type="number" id="item-value" value="10"></div>
                    <div class="form-group"><label>Ур.</label><input type="number" id="item-required-level" value="1"></div>
                    <div class="form-group"><label>АТК</label><input type="number" id="item-atk-bonus" value="0"></div>
                </div>
                <div class="form-actions">
                    <button class="admin-btn success" onclick="saveItem()">💾</button>
                    <button class="admin-btn" onclick="hideItemForm()">Отмена</button>
                </div>
            </div>
        </div>
        
        <!-- Enemies -->
        <div id="admin-enemies" class="admin-content" style="display:none;">
            <div class="admin-toolbar"><button class="admin-btn primary" onclick="loadEnemies()">🔄</button></div>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Имя</th><th>Ур.</th><th>HP</th><th>АТК</th><th>ЗАЩ</th><th>Опыт</th><th>Золото</th><th>Босс</th><th>Действ.</th></tr></thead>
                <tbody id="admin-enemies-body"></tbody>
            </table>
        </div>
        
        <!-- Quests -->
        <div id="admin-quests" class="admin-content" style="display:none;">
            <div class="admin-toolbar"><button class="admin-btn primary" onclick="loadAdminQuests()">🔄</button></div>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Название</th><th>Тип</th><th>Цель</th><th>Опыт</th><th>Золото</th><th>Мин.ур</th><th>Действ.</th></tr></thead>
                <tbody id="admin-quests-body"></tbody>
            </table>
        </div>
        
        <!-- Guilds -->
        <div id="admin-guilds" class="admin-content" style="display:none;">
            <div class="admin-toolbar"><button class="admin-btn primary" onclick="loadAdminGuilds()">🔄</button></div>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Название</th><th>Лидер</th><th>Ур.</th><th>EXP</th><th>Золото</th><th>Действ.</th></tr></thead>
                <tbody id="admin-guilds-body"></tbody>
            </table>
        </div>
        
        <!-- Raids -->
        <div id="admin-raids" class="admin-content" style="display:none;">
            <div class="admin-toolbar">
                <button class="admin-btn primary" onclick="loadRaids()">🔄</button>
                <button class="admin-btn success" onclick="showRaidForm()">➕</button>
            </div>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Имя</th><th>Ур.</th><th>HP</th><th>АТК</th><th>Опыт</th><th>Золото</th><th>Лимит</th><th>Активен</th><th>Действ.</th></tr></thead>
                <tbody id="admin-raids-body"></tbody>
            </table>
        </div>
    </div>
</section>