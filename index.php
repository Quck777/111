<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medieval Realm - Главная</title>
    <meta name="description" content="Medieval Realm - браузерная MMORPG">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='⚔️'></svg>">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Page Loader -->
    <div id="page-loader" class="page-loader">
        <div class="loader-spinner"></div>
    </div>
    
    <!-- Main Site -->
    <div class="main-site">
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <div class="logo">⚔️ Medieval Realm</div>
                <h1>Браузерная MMORPG нового поколения</h1>
                <p>Погрузись в мир средневековья, сражайся с монстрами, побеждай на арене и создай свою гильдию!</p>
                <div class="hero-buttons">
                    <button class="btn-primary" onclick="showModal('login')">Вход</button>
                    <button class="btn-secondary" onclick="showModal('register')">Регистрация</button>
                </div>
            </div>
        </section>
        
        <!-- Features -->
        <section class="features">
            <div class="feature">
                <div class="feature-icon">⚔️</div>
                <h3>PvE Бои</h3>
                <p>Сражайся с монстрами и боссами</p>
            </div>
            <div class="feature">
                <div class="feature-icon">🏟️</div>
                <h3>PvP Арена</h3>
                <p>Докажи своё мастерство</p>
            </div>
            <div class="feature">
                <div class="feature-icon">🏰</div>
                <h3>Гильдии</h3>
                <p>Объединяйся с игроками</p>
            </div>
            <div class="feature">
                <div class="feature-icon">🔥</div>
                <h3>Рейды</h3>
                <p>Сражайся с боссами вместе</p>
            </div>
        </section>
        
        <!-- Leaderboard Preview -->
        <section class="leaderboard-preview">
            <h2>🏆 Топ игроков</h2>
            <table class="lb-table" id="top-players">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Игрок</th>
                        <th>Класс</th>
                        <th>Уровень</th>
                        <th>Рейтинг</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="5">Загрузка...</td></tr>
                </tbody>
            </table>
        </section>
        
        <!-- Guild Leaderboard -->
        <section class="leaderboard-preview">
            <h2>🏰 Топ гильдий</h2>
            <table class="lb-table" id="top-guilds">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Гильдия</th>
                        <th>Уровень</th>
                        <th>Участники</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="4">Загрузка...</td></tr>
                </tbody>
            </table>
        </section>
        
        <!-- Footer -->
        <footer>
            <p>Medieval Realm v3.0.0 © 2026</p>
        </footer>
    </div>
    
    <!-- Auth Modal -->
    <div id="auth-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal()">&times;</span>
            
            <div class="auth-tabs">
                <button class="auth-tab active" onclick="showAuth('login')">Вход</button>
                <button class="auth-tab" onclick="showAuth('register')">Регистрация</button>
            </div>
            
            <!-- Login -->
            <div id="login-form" class="auth-form">
                <input type="text" id="login-username" placeholder="Имя персонажа">
                <input type="password" id="login-password" placeholder="Пароль">
                <button onclick="doLogin()" class="btn-primary">Войти</button>
            </div>
            
            <!-- Register -->
            <div id="register-form" class="auth-form" style="display:none;">
                <input type="text" id="reg-username" placeholder="Имя персонажа">
                <input type="password" id="reg-password" placeholder="Пароль">
                <select id="reg-race">
                    <option value="human">👤 Человек</option>
                    <option value="elf">🧝 Эльф</option>
                    <option value="dwarf">🧔 Гном</option>
                    <option value="orc">👹 Орк</option>
                    <option value="undead">💀 Нежить</option>
                </select>
                <select id="reg-class">
                    <option value="warrior">⚔️ Воин</option>
                    <option value="mage">🔮 Маг</option>
                    <option value="archer">🏹 Лучник</option>
                    <option value="rogue">🗡️ Плут</option>
                    <option value="paladin">🛡️ Паладин</option>
                </select>
                <button onclick="doRegister()" class="btn-primary">Создать персонажа</button>
            </div>
            
            <p id="auth-error" class="error"></p>
        </div>
    </div>
    
    <script>
    var API_URL = '';
    
    function api(action, data, callback) {
        var formData = new FormData();
        formData.append('action', action);
        for (var key in data) formData.append(key, data[key]);
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'api.php', true);
        xhr.onload = function() {
            try {
                callback(JSON.parse(xhr.responseText));
            } catch(e) {
                document.getElementById('auth-error').textContent = 'Ошибка сервера';
            }
        };
        xhr.send(formData);
    }
    
    function showModal(type) {
        document.getElementById('auth-modal').style.display = 'flex';
        showAuth(type);
    }
    
    function hideModal() {
        document.getElementById('auth-modal').style.display = 'none';
    }
    
    function showAuth(type) {
        document.querySelectorAll('.auth-tab').forEach(function(t) { t.classList.remove('active'); });
        document.querySelector('.auth-tab:nth-child(' + (type === 'login' ? 1 : 2) + ')').classList.add('active');
        document.getElementById('login-form').style.display = type === 'login' ? 'block' : 'none';
        document.getElementById('register-form').style.display = type === 'register' ? 'block' : 'none';
        document.getElementById('auth-error').textContent = '';
    }
    
    function doLogin() {
        var username = document.getElementById('login-username').value.trim();
        var password = document.getElementById('login-password').value;
        
        if (!username || !password) {
            document.getElementById('auth-error').textContent = 'Заполните все поля';
            return;
        }
        
        api('login', { username: username, password: password }, function(result) {
            if (result.success) {
                window.location.href = 'game.php';
            } else {
                document.getElementById('auth-error').textContent = result.error || 'Ошибка входа';
            }
        });
    }
    
    function doRegister() {
        var username = document.getElementById('reg-username').value.trim();
        var password = document.getElementById('reg-password').value;
        var cls = document.getElementById('reg-class').value;
        var race = document.getElementById('reg-race').value;
        
        if (!username || username.length < 3) {
            document.getElementById('auth-error').textContent = 'Имя минимум 3 символа';
            return;
        }
        if (!password || password.length < 4) {
            document.getElementById('auth-error').textContent = 'Пароль минимум 4 символа';
            return;
        }
        
        api('register', { username: username, password: password, class: cls, race: race }, function(result) {
            if (result.success) {
                document.getElementById('auth-error').style.color = '#10b981';
                document.getElementById('auth-error').textContent = 'Персонаж создан! Войдите.';
                setTimeout(function() { showAuth('login'); }, 1500);
            } else {
                document.getElementById('auth-error').textContent = result.error || 'Ошибка регистрации';
            }
        });
    }
    
    function loadLeaderboard() {
        api('getLeaderboard', {}, function(result) {
            var tbody = document.querySelector('#top-players tbody');
            if (!result || !result.length) {
                tbody.innerHTML = '<tr><td colspan="5">Нет игроков</td></tr>';
                return;
            }
            var html = '';
            var icons = { warrior: '⚔️', mage: '🔮', archer: '🏹', rogue: '🗡️', paladin: '🛡️' };
            for (var i = 0; i < Math.min(5, result.length); i++) {
                var u = result[i];
                html += '<tr>' +
                    '<td>' + (i + 1) + '</td>' +
                    '<td>' + (icons[u.class] || '⚔️') + ' ' + u.username + '</td>' +
                    '<td>' + u.class + '</td>' +
                    '<td>' + u.level + '</td>' +
                    '<td>' + u.pvp_rating + '</td>' +
                '</tr>';
            }
            tbody.innerHTML = html;
        });
        
        api('getGuilds', {}, function(result) {
            var tbody = document.querySelector('#top-guilds tbody');
            if (!result || !result.length) {
                tbody.innerHTML = '<tr><td colspan="4">Нет гильдий</td></tr>';
                return;
            }
            var html = '';
            for (var i = 0; i < Math.min(5, result.length); i++) {
                var g = result[i];
                html += '<tr>' +
                    '<td>' + (i + 1) + '</td>' +
                    '<td>🏰 ' + g.name + '</td>' +
                    '<td>' + g.level + '</td>' +
                    '<td>' + (g.members || 0) + '</td>' +
                '</tr>';
            }
            tbody.innerHTML = html;
        });
    }
    
    // Hide loader
    window.onload = function() {
        setTimeout(function() {
            var loader = document.getElementById('page-loader');
            if (loader) loader.classList.add('hidden');
        }, 500);
        loadLeaderboard();
    };
    </script>
</body>
</html>