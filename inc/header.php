<?php
/**
 * Header Template
 * Game header with navigation
 */

$headerNav = [
    ['id' => 'character', 'icon' => '👤', 'label' => 'Персонаж', 'dropdown' => []],
    ['id' => 'world', 'icon' => '🌍', 'label' => 'Мир', 'dropdown' => [
        ['section' => 'map', 'icon' => '🗺️', 'label' => 'Карта'],
        ['section' => 'licenses', 'icon' => '📜', 'label' => 'Лицензии']
    ]],
    ['id' => 'battle', 'icon' => '⚔️', 'label' => 'Бой', 'dropdown' => [
        ['section' => 'battle', 'icon' => '⚔️', 'label' => 'PvE'],
        ['section' => 'arena', 'icon' => '🏟️', 'label' => 'PvP'],
        ['section' => 'raids', 'icon' => '🔥', 'label' => 'Рейды']
    ]],
    ['id' => 'quests', 'icon' => '📜', 'label' => 'Квесты', 'dropdown' => [
        ['section' => 'quests', 'icon' => '📜', 'label' => 'Квесты'],
        ['section' => 'achievements', 'icon' => '🏅', 'label' => 'Достижения']
    ]],
    ['id' => 'inventory', 'icon' => '🎒', 'label' => 'Инвентарь', 'dropdown' => [
        ['section' => 'inventory', 'icon' => '🎒', 'label' => 'Инвентарь'],
        ['section' => 'shop', 'icon' => '🏪', 'label' => 'Магазин'],
        ['section' => 'skills', 'icon' => '⚡', 'label' => 'Навыки']
    ]],
    ['id' => 'guild', 'icon' => '🏰', 'label' => 'Гильдия', 'dropdown' => [
        ['section' => 'guilds', 'icon' => '🏰', 'label' => 'Гильдии'],
        ['section' => 'guildWars', 'icon' => '⚔️', 'label' => 'Войны']
    ]],
    ['id' => 'rating', 'icon' => '🏆', 'label' => 'Рейтинг', 'dropdown' => [
        ['section' => 'leaderboard', 'icon' => '🏆', 'label' => 'Рейтинг'],
        ['section' => 'market', 'icon' => '💱', 'label' => 'Рынок'],
        ['section' => 'history', 'icon' => '📊', 'label' => 'История']
    ]]
];

function renderHeaderNav() {
    global $headerNav;
    $html = '';
    foreach ($headerNav as $item) {
        if (!empty($item['dropdown'])) {
            $dropdownHtml = '';
            foreach ($item['dropdown'] as $d) {
                $dropdownHtml .= '<a onclick="router(\'' . $d['section'] . '\')">' . $d['icon'] . ' ' . $d['label'] . '</a>';
            }
            $html .= '<div class="nav-dropdown">' .
                '<button class="nav-link">' . $item['icon'] . ' ' . $item['label'] . '</button>' .
                '<div class="dropdown-content">' . $dropdownHtml . '</div></div>';
        } else {
            $html .= '<div class="nav-dropdown"><button class="nav-link" onclick="router(\'' . $item['id'] . '\')">' . $item['icon'] . ' ' . $item['label'] . '</button></div>';
        }
    }
    return $html;
}
?>
<header class="game-header">
    <div class="header-left">
        <div class="logo">
            <span class="logo-icon">⚔</span>
            <h1>Medieval Realm</h1>
        </div>
        <nav class="header-nav" id="main-nav">
            <?= renderHeaderNav() ?>
        </nav>
        <button class="btn-admin" onclick="showAdmin()">👑 Админ</button>
    </div>
    <div class="header-right">
        <span class="gold">💰 <span id="header-gold">0</span></span>
        <span class="rating">🏆 <span id="header-rating">1000</span></span>
        <span class="char-level" id="header-level">1</span>
        <span class="char-name" id="header-name">Player</span>
        <button onclick="logout()" class="btn-logout">Выход</button>
    </div>
</header>