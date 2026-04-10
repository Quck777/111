<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= GAME_NAME ?> - Пиксельная РПГ игра</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
</head>
<body class="pixel-font">
    <div id="app">
        <!-- Заголовок -->
        <header class="game-header">
            <div class="container">
                <div class="logo">
                    <h1><?= GAME_NAME ?></h1>
                    <span class="version">v<?= GAME_VERSION ?></span>
                </div>
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php" class="nav-link">Главная</a></li>
                        <li><a href="leaderboard.php" class="nav-link">Лидерборд</a></li>
                        <li><a href="about.php" class="nav-link">Об игре</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="game.php" class="nav-link btn-play">Играть</a></li>
                            <li><a href="profile.php" class="nav-link">Профиль</a></li>
                            <li><a href="logout.php" class="nav-link">Выход</a></li>
                        <?php else: ?>
                            <li><a href="login.php" class="nav-link">Вход</a></li>
                            <li><a href="register.php" class="nav-link btn-register">Регистрация</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </header>

        <!-- Основной контент -->
        <main class="main-content">
