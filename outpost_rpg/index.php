<?php
/**
 * Главная страница игры ФОРПОСТ
 */

require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/user.php';

include 'includes/header.php';
?>

<!-- Герой-секция -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h2 class="hero-title">Добро пожаловать в <?= GAME_NAME ?></h2>
            <p class="hero-subtitle">Пиксельная РПГ игра нового поколения</p>
            <p class="hero-description">
                Погрузитесь в увлекательный мир приключений, где каждый игрок может стать легендой.
                Сражайтесь с монстрами, исследуйте локации, торгуйте на рынке и создавайте свою историю!
            </p>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="hero-actions">
                    <a href="register.php" class="btn btn-success btn-lg">Начать игру</a>
                    <a href="about.php" class="btn btn-primary btn-lg">Узнать больше</a>
                </div>
            <?php else: ?>
                <div class="hero-actions">
                    <a href="game.php" class="btn btn-success btn-lg">Продолжить игру</a>
                    <a href="profile.php" class="btn btn-primary btn-lg">Профиль</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="hero-image">
            <img src="assets/images/characters/hero.png" alt="Герой" class="float-animation">
        </div>
    </div>
</section>

<!-- Особенности игры -->
<section class="features-section">
    <div class="container">
        <h2 class="section-title">Особенности игры</h2>
        
        <div class="features-grid">
            <div class="feature-card card">
                <div class="feature-icon">⚔️</div>
                <h3>Эпические сражения</h3>
                <p>Сражайтесь с уникальными монстрами и боссами в пошаговых боях</p>
            </div>
            
            <div class="feature-card card">
                <div class="feature-icon">🗺️</div>
                <h3>Исследование мира</h3>
                <p>Открывайте новые локации: от темных шахт до древних лесов</p>
            </div>
            
            <div class="feature-card card">
                <div class="feature-icon">💰</div>
                <h3>Торговля и экономика</h3>
                <p>Торгуйте предметами на рынке, создавайте свои лоты</p>
            </div>
            
            <div class="feature-card card">
                <div class="feature-icon">🎯</div>
                <h3>Квесты и достижения</h3>
                <p>Выполняйте задания и получайте уникальные награды</p>
            </div>
            
            <div class="feature-card card">
                <div class="feature-icon">👥</div>
                <h3>Социальная система</h3>
                <p>Общайтесь в чате, создавайте гильдии, находите друзей</p>
            </div>
            
            <div class="feature-card card">
                <div class="feature-icon">🏆</div>
                <h3>Лидерборд</h3>
                <p>Соревнуйтесь с другими игроками за звание лучшего</p>
            </div>
        </div>
    </div>
</section>

<!-- Статистика сервера -->
<section class="stats-section">
    <div class="container">
        <h2 class="section-title">Статистика сервера</h2>
        
        <div class="server-stats">
            <?php
            $db = Database::getInstance();
            
            // Получение статистики
            $totalPlayers = $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'];
            $onlinePlayers = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE is_online = 1")['count'];
            $totalMonsters = $db->fetchOne("SELECT COUNT(*) as count FROM monsters")['count'];
            $totalItems = $db->fetchOne("SELECT COUNT(*) as count FROM items")['count'];
            ?>
            
            <div class="stat-box">
                <div class="stat-value"><?= $totalPlayers ?></div>
                <div class="stat-label">Игроков</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-value"><?= $onlinePlayers ?></div>
                <div class="stat-label">Онлайн</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-value"><?= $totalMonsters ?></div>
                <div class="stat-label">Монстров</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-value"><?= $totalItems ?></div>
                <div class="stat-label">Предметов</div>
            </div>
        </div>
    </div>
</section>

<!-- Последние новости -->
<section class="news-section">
    <div class="container">
        <h2 class="section-title">Последние новости</h2>
        
        <div class="news-list">
            <article class="news-item card">
                <h3>🎉 Добро пожаловать в ФОРПОСТ!</h3>
                <p class="news-date"><?= date('d.m.Y') ?></p>
                <p>Мы рады приветствовать вас в нашей новой пиксельной РПГ игре! Регистрируйтесь и начинайте свое приключение прямо сейчас.</p>
            </article>
            
            <article class="news-item card">
                <h3>⚔️ Система боя</h3>
                <p class="news-date"><?= date('d.m.Y') ?></p>
                <p>В игре реализована полноценная система пошаговых боев с монстрами и PvP сражения между игроками.</p>
            </article>
            
            <article class="news-item card">
                <h3>💰 Экономика и торговля</h3>
                <p class="news-date"><?= date('d.m.Y') ?></p>
                <p>Торгуйте предметами на рынке, покупайте и продавайте, создавайте свои лоты и зарабатывайте золото!</p>
            </article>
        </div>
    </div>
</section>

<!-- Призыв к действию -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content card">
            <h2>Готовы начать приключение?</h2>
            <p>Присоединяйтесь к тысячам игроков в мире ФОРПОСТ</p>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn btn-success btn-lg">Создать персонажа</a>
            <?php else: ?>
                <a href="game.php" class="btn btn-success btn-lg">Играть сейчас</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
/* Дополнительные стили для главной страницы */
.hero-section {
    padding: 60px 0;
    background: linear-gradient(135deg, rgba(26, 26, 46, 0.9) 0%, rgba(22, 33, 62, 0.9) 100%);
}

.hero-section .container {
    display: flex;
    align-items: center;
    gap: 40px;
}

.hero-content {
    flex: 1;
}

.hero-title {
    font-size: 2.5rem;
    color: var(--accent-color);
    margin-bottom: 20px;
    text-shadow: 3px 3px 0px var(--shadow-color);
}

.hero-subtitle {
    font-size: 1rem;
    color: var(--text-secondary);
    margin-bottom: 20px;
}

.hero-description {
    font-size: 0.7rem;
    line-height: 2;
    margin-bottom: 30px;
    color: var(--text-primary);
}

.hero-actions {
    display: flex;
    gap: 20px;
}

.btn-lg {
    padding: 15px 30px;
    font-size: 0.8rem;
}

.hero-image {
    flex: 1;
    text-align: center;
}

.hero-image img {
    max-width: 100%;
    height: auto;
    image-rendering: pixelated;
}

.features-section,
.stats-section,
.news-section,
.cta-section {
    padding: 60px 0;
}

.section-title {
    text-align: center;
    font-size: 1.5rem;
    color: var(--accent-color);
    margin-bottom: 40px;
    text-shadow: 2px 2px 0px var(--shadow-color);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.feature-card {
    text-align: center;
    transition: transform 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-icon {
    font-size: 3rem;
    margin-bottom: 20px;
}

.feature-card h3 {
    font-size: 0.9rem;
    color: var(--accent-color);
    margin-bottom: 15px;
}

.feature-card p {
    font-size: 0.6rem;
    color: var(--text-secondary);
    line-height: 1.8;
}

.server-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 30px;
}

.news-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.news-item h3 {
    font-size: 0.9rem;
    color: var(--accent-color);
    margin-bottom: 10px;
}

.news-date {
    font-size: 0.5rem;
    color: var(--text-muted);
    margin-bottom: 15px;
}

.news-item p {
    font-size: 0.6rem;
    color: var(--text-secondary);
    line-height: 1.8;
}

.cta-content {
    text-align: center;
    padding: 40px;
}

.cta-content h2 {
    font-size: 1.5rem;
    color: var(--accent-color);
    margin-bottom: 20px;
}

.cta-content p {
    font-size: 0.7rem;
    color: var(--text-secondary);
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .hero-section .container {
        flex-direction: column-reverse;
    }
    
    .hero-actions {
        flex-direction: column;
    }
    
    .hero-title {
        font-size: 1.8rem;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
