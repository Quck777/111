<?php
/**
 * Sections Renderer Template
 * All game sections HTML
 */

function renderSection($name) {
    $sections = [
        'character' => '<section id="character-section">
            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar" id="char-avatar">⚔️</div>
                    <div class="profile-info">
                        <h1 id="char-name">Hero</h1>
                        <span class="profile-class" id="char-class">ВОИН</span>
                        <span class="profile-race" id="char-race">Человек</span>
                    </div>
                    <div class="profile-level-badge">Уровень <span id="char-level">1</span></div>
                </div>
                <div class="profile-bars">
                    <div class="bar-row"><span>❤️ HP</span><div class="bar"><div class="bar-fill hp" id="hp-bar" style="width:100%"></div></div><span><span id="char-hp">100</span>/<span id="char-max-hp">100</span></span></div>
                    <div class="bar-row"><span>💧 MP</span><div class="bar"><div class="bar-fill mp" id="mp-bar" style="width:100%"></div></div><span><span id="char-mp">50</span>/<span id="char-max-mp">50</span></span></div>
                </div>
                <div class="profile-stats">
                    <div class="stat-box"><span>⚔️</span><strong id="char-atk">10</strong><small>Атака</small></div>
                    <div class="stat-box"><span>🛡️</span><strong id="char-def">5</strong><small>Защита</small></div>
                    <div class="stat-box"><span>💰</span><strong id="char-gold">100</strong><small>Золото</small></div>
                    <div class="stat-box"><span>💎</span><strong id="char-crystals">0</strong><small>Кристаллы</small></div>
                </div>
                <div class="profile-badges">
                    <span class="badge">🏆 <span id="char-pvp-rating">1000</span></span>
                    <span class="badge">⚔️ <span id="char-pvp-wins">0</span> побед</span>
                </div>
                <div class="profile-equipment">
                    <h3>Снаряжение</h3>
                    <div class="equip-grid">
                        <div class="equip-slot" data-slot="weapon">⚔️</div>
                        <div class="equip-slot" data-slot="armor">🛡️</div>
                        <div class="equip-slot" data-slot="helmet">⛑️</div>
                        <div class="equip-slot" data-slot="boots">👢</div>
                        <div class="equip-slot" data-slot="ring">💍</div>
                        <div class="equip-slot" data-slot="amulet">📿</div>
                    </div>
                </div>
            </div>
        </section>',

        'battle' => '<section id="battle-section" style="display:none;">
            <h2>⚔️ Битва</h2>
            <div class="battle-container">
                <div class="enemies-list-view" id="enemies-list-view">
                    <h3>Выберите противника</h3>
                    <div class="enemies-grid" id="enemies-list"></div>
                </div>
                <div class="battle-view" id="battle-view" style="display:none;">
                    <div class="battle-enemy">
                        <div class="enemy-avatar" id="battle-enemy-avatar">👹</div>
                        <div class="enemy-name" id="battle-enemy-name">Монстр</div>
                        <div class="enemy-level">Уровень <span id="battle-enemy-level">1</span></div>
                        <div class="hp-bar-container"><div class="bar"><div class="bar-fill hp" id="battle-enemy-hp-bar" style="width:100%"></div></div><span><span id="battle-enemy-hp">100</span>/<span id="battle-enemy-max-hp">100</span> HP</span></div>
                    </div>
                    <div class="battle-player">
                        <div class="player-stats"><span>❤️ <span id="battle-player-hp">100</span>/<span id="battle-player-max-hp">100</span></span><span>💧 <span id="battle-player-mp">50</span>/<span id="battle-player-max-mp">50</span></span></div>
                        <div class="hp-bar-container"><div class="bar"><div class="bar-fill hp" id="battle-player-hp-bar" style="width:100%"></div></div></div>
                    </div>
                    <div class="battle-log" id="battle-log"><p>Бой начался!</p></div>
                    <div class="battle-actions">
                        <button class="btn-attack" onclick="attack(\'normal\')">⚔️ Атаковать</button>
                        <button class="btn-attack" onclick="attack(\'strong\')">💥 Сильный удар</button>
                        <button class="btn-potion" onclick="useBattlePotion()">🧪 Зелье HP</button>
                        <button class="btn-flee" onclick="fleeBattle()">🏃 Бежать</button>
                    </div>
                </div>
            </div>
        </section>',

        'arena' => '<section id="arena-section" style="display:none;">
            <h2>🏟️ PvP Арена</h2>
            <div class="battle-container">
                <div class="enemies-list-view" id="arena-list-view">
                    <h3>Выберите соперника</h3>
                    <div class="enemies-grid" id="arena-opponents"></div>
                </div>
                <div class="battle-view" id="pvp-battle-view" style="display:none;">
                    <div class="battle-enemy">
                        <div class="enemy-avatar" id="pvp-enemy-avatar">⚔️</div>
                        <div class="enemy-name" id="pvp-enemy-name">Игрок</div>
                        <div class="hp-bar-container"><div class="bar"><div class="bar-fill arena" id="pvp-enemy-hp-bar" style="width:100%"></div></div><span><span id="pvp-enemy-hp">100</span>/<span id="pvp-enemy-max-hp">100</span></span></div>
                    </div>
                    <div class="battle-player">
                        <div class="player-stats"><span>❤️ <span id="pvp-player-hp">100</span>/<span id="pvp-player-max-hp">100</span></span></div>
                        <div class="hp-bar-container"><div class="bar"><div class="bar-fill arena" id="pvp-player-hp-bar" style="width:100%"></div></div></div>
                    </div>
                    <div class="battle-actions">
                        <button class="btn-attack" onclick="pvpAttack(\'normal\')">⚔️</button>
                        <button class="btn-attack" onclick="pvpAttack(\'strong\')">💥</button>
                        <button class="btn-flee" onclick="fleePvpBattle()">🏳️</button>
                    </div>
                </div>
            </div>
        </section>',

        'shop' => '<section id="shop-section" style="display:none;">
            <h2>🏪 Магазин</h2>
            <div class="shop-items" id="shop-items"></div>
        </section>',

        'inventory' => '<section id="inventory-section" style="display:none;">
            <h2>🎒 Инвентарь</h2>
            <div class="inventory-grid" id="inventory-grid"></div>
        </section>',

        'map' => '<section id="map-section" style="display:none;">
            <h2>🗺️ Карта мира</h2>
            <div class="locations-grid" id="locations-grid"></div>
        </section>',

        'licenses' => '<section id="licenses-section" style="display:none;">
            <h2>📜 Лицензии</h2>
            <div class="licenses-shop" id="licenses-shop"></div>
        </section>',

        'skills' => '<section id="skills-section" style="display:none;">
            <h2>⚡ Навыки</h2>
            <div class="skills-list" id="skills-list"></div>
        </section>',

        'quests' => '<section id="quests-section" style="display:none;">
            <h2>📜 Квесты</h2>
            <div class="quests-list" id="quests-list"></div>
        </section>',

        'achievements' => '<section id="achievements-section" style="display:none;">
            <h2>🏅 Достижения</h2>
            <div class="achievements-list" id="achievements-list"></div>
        </section>',

        'leaderboard' => '<section id="leaderboard-section" style="display:none;">
            <h2>🏆 Рейтинг</h2>
            <div class="rating-tabs">
                <button class="rating-tab active" onclick="switchLB(\'players\')">👤 Игроки</button>
                <button class="rating-tab" onclick="switchLB(\'guilds\')">🏰 Гильдии</button>
            </div>
            <div id="players-rating">
                <table class="leaderboard-table"><thead><tr><th>#</th><th>Игрок</th><th>Класс</th><th>Уровень</th><th>Рейтинг</th></tr></thead><tbody id="leaderboard-body"></tbody></table>
            </div>
            <div id="guilds-rating" style="display:none;">
                <table class="leaderboard-table"><thead><tr><th>#</th><th>Гильдия</th><th>Уровень</th><th>Участники</th></tr></thead><tbody id="guilds-list-body"></tbody></table>
            </div>
        </section>',

        'guilds' => '<section id="guilds-section" style="display:none;">
            <h2>🏰 Гильдии</h2>
            <div class="guilds-list" id="guilds-list"></div>
        </section>',

        'guildWars' => '<section id="guildWars-section" style="display:none;">
            <h2>⚔️ Войны гильдий</h2>
            <div class="guild-wars-list" id="guild-wars-list"></div>
        </section>',

        'market' => '<section id="market-section" style="display:none;">
            <h2>💱 Рынок</h2>
            <div class="market-grid" id="market-grid"></div>
        </section>',

        'history' => '<section id="history-section" style="display:none;">
            <h2>📊 История боёв</h2>
            <div class="history-list" id="history-list"></div>
        </section>',

        'raids' => '<section id="raids-section" style="display:none;">
            <h2>🔥 Рейд-боссы</h2>
            <div class="raids-list" id="raids-list"></div>
        </section>',

        'chat' => '<section id="chat-section" style="display:none;">
            <div class="chat-container">
                <div class="chat-channels">
                    <button class="chat-channel active" onclick="setChat(\'global\')">🌍 Глобальный</button>
                    <button class="chat-channel" onclick="setChat(\'trade\')">💰 Торговля</button>
                    <button class="chat-channel" onclick="setChat(\'help\')">❓ Помощь</button>
                </div>
                <div class="chat-messages" id="chat-messages"></div>
                <div class="chat-input-container">
                    <input type="text" id="chat-input" placeholder="Сообщение..." onkeypress="if(event.key==\'Enter\')sendMsg()">
                    <button onclick="sendMsg()">➤</button>
                </div>
            </div>
        </section>'
    ];

    return $sections[$name] ?? '';
}
?>
<main class="content" id="game-content">
    <?= renderSection('character') ?>
    <?= renderSection('battle') ?>
    <?= renderSection('arena') ?>
    <?= renderSection('shop') ?>
    <?= renderSection('inventory') ?>
    <?= renderSection('map') ?>
    <?= renderSection('licenses') ?>
    <?= renderSection('skills') ?>
    <?= renderSection('quests') ?>
    <?= renderSection('achievements') ?>
    <?= renderSection('leaderboard') ?>
    <?= renderSection('guilds') ?>
    <?= renderSection('guildWars') ?>
    <?= renderSection('market') ?>
    <?= renderSection('history') ?>
    <?= renderSection('raids') ?>
    <?= renderSection('chat') ?>
    <?php include __DIR__ . '/admin-section.php'; ?>
</main>