<?php
/**
 * Medieval Realm RPG - Game Client
 * Using modular structure from inc/ folder
 */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medieval Realm - RPG Game</title>
    <meta name="description" content="Medieval Realm - браузерная MMORPG">
    <meta name="version" content="3.0.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='⚔️'></svg>">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <div id="page-loader" class="page-loader"><div class="loader-spinner"></div><div class="loader-text">Загрузка...</div></div>
    
    <div id="app">
        <div id="game-layout">
            <?php include __DIR__ . '/inc/header.php'; ?>
            <div class="main-container">
                <?php include __DIR__ . '/inc/sections.php'; ?>
            </div>
        </div>
    </div>
    
    <div class="version-info">v3.0.0</div>
    
    <script src="js/game.js"></script>
    <script src="js/router.js"></script>
    <script src="js/game-auth.js"></script>
    <script src="js/game-battle.js"></script>
    <script src="js/game-shop.js"></script>
    <script src="js/game-chat.js"></script>
    <script src="js/game-guild.js"></script>
    <script src="js/game-admin.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        init();
        router('character');
    });
    </script>
</body>
</html>