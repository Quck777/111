# Medieval Realm RPG - Browser MMORPG

## Версия: 3.0.0 (Глобальный релиз)

Браузерная MMORPG игра с PvP, PvE, гильдиями, рынком и админ-панелью.

## 📁 Структура проекта

```
/workspace/
├── api.php                 # API entry point (routes to modules/api/router.php)
├── config.php              # Database configuration
├── game.php                # Main game page
├── index.html              # Landing page
├── settings.php            # User settings page
├── css/
│   ├── style.css          # Main styles
│   └── admin.css          # Admin panel styles
├── js/
│   ├── game.js            # Main game logic
│   ├── router.js          # SPA router
│   ├── game-auth.js       # Authentication
│   ├── game-battle.js     # Battle system
│   ├── game-shop.js       # Shop & inventory
│   ├── game-chat.js       # Chat system
│   ├── game-guild.js      # Guild system
│   └── game-admin.js      # Admin panel JS
├── modules/               # Modular backend system
│   ├── api/
│   │   └── router.php     # API Router (main entry)
│   ├── auth/
│   │   └── auth.php       # Registration, login, users
│   ├── battle/
│   │   └── battle.php     # PvE, PvP, arena combat
│   ├── inventory/
│   │   └── inventory.php  # Items, shop, equipment
│   ├── chat/
│   │   └── chat.php       # Chat system
│   ├── guild/
│   │   └── guild.php      # Guilds, guild wars
│   ├── market/
│   │   └── market.php     # Player marketplace
│   └── admin/
│       └── admin.php      # Admin panel (24 functions)
├── sql/
│   └── database.sql       # Database schema
└── inc/
    ├── header.php         # Site header
    ├── sections.php       # Game sections
    └── admin-section.php  # Admin section
```

## 🚀 Установка

1. **Настройте базу данных** в `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

2. **Импортируйте базу данных**:
```bash
mysql -u username -p database_name < sql/database.sql
```

3. **Запустите веб-сервер** (Apache/Nginx) с PHP 8.0+

## 🎮 Игровые функции

### Для игроков:
- ✅ Регистрация и вход
- ✅ Выбор класса (warrior, mage, archer, rogue, paladin)
- ✅ Выбор расы (human, elf, dwarf, orc, undead)
- ✅ PvE бои с монстрами
- ✅ PvP арена
- ✅ Инвентарь и экипировка
- ✅ Магазин предметов
- ✅ Чат (глобальный, локальный, гильдия)
- ✅ Гильдии и войны гильдий
- ✅ Рынок игроков
- ✅ Квесты и достижения
- ✅ Рейд боссы
- ✅ Лидерборды

### Для админов:
- ✅ Управление пользователями (бан, разбан, изменение уровня, золота)
- ✅ Управление предметами (создание, редактирование, удаление)
- ✅ Управление монстрами
- ✅ Управление квестами
- ✅ Управление гильдиями
- ✅ Управление рейд боссами
- ✅ Статистика сервера
- ✅ Отправка объявлений
- ✅ Выдача предметов игрокам
- ✅ Телепортация игроков
- ✅ Сброс паролей

## 🔒 Безопасность

- PDO с prepared statements
- Валидация всех входных данных
- Фильтрация мата в чате
- Защита от спама (2 сек между сообщениями)
- Проверка прав доступа для админ-функций
- Хеширование паролей (password_hash)

## 🛠️ API

Все API запросы идут через `api.php` → `modules/api/router.php`

Пример запроса:
```javascript
fetch('api.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=login&username=test&password=12345'
})
```

## 📊 Модульная структура

Каждый модуль содержит полный набор функций для своей области:

| Модуль | Функций | Строк кода |
|--------|---------|------------|
| auth | 5 | 124 |
| battle | 12 | 380 |
| inventory | 10 | 345 |
| chat | 4 | 119 |
| guild | 15 | 401 |
| market | 6 | 195 |
| admin | 24 | 520 |

## 📝 Лицензия

Medieval Realm RPG v3.0.0
