        </main>

        <!-- Подвал -->
        <footer class="game-footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-section">
                        <h3><?= GAME_NAME ?></h3>
                        <p>Пиксельная РПГ игра нового поколения</p>
                    </div>
                    <div class="footer-section">
                        <h4>Навигация</h4>
                        <ul>
                            <li><a href="index.php">Главная</a></li>
                            <li><a href="leaderboard.php">Лидерборд</a></li>
                            <li><a href="about.php">Об игре</a></li>
                        </ul>
                    </div>
                    <div class="footer-section">
                        <h4>Информация</h4>
                        <ul>
                            <li><a href="rules.php">Правила</a></li>
                            <li><a href="privacy.php">Конфиденциальность</a></li>
                            <li><a href="contact.php">Контакты</a></li>
                        </ul>
                    </div>
                    <div class="footer-section">
                        <h4>Статус сервера</h4>
                        <div class="server-status online">
                            <span class="status-indicator"></span>
                            <span>Онлайн</span>
                        </div>
                        <p>Игроков: <span id="online-count">0</span></p>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; <?= date('Y') ?> <?= GAME_NAME ?>. Все права защищены.</p>
                    <p>Версия: <?= GAME_VERSION ?></p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Скрипты -->
    <script src="<?= ASSETS_URL ?>/js/utils.js"></script>
    <script src="<?= ASSETS_URL ?>/js/api.js"></script>
    <script src="<?= ASSETS_URL ?>/js/main.js"></script>
</body>
</html>
