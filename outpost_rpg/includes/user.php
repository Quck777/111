<?php
/**
 * Класс для работы с пользователями
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Регистрация нового пользователя
     */
    public function register($username, $email, $password) {
        // Валидация
        if (strlen($username) < USERNAME_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Имя пользователя слишком короткое'];
        }
        
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Пароль слишком короткий'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Некорректный email'];
        }
        
        // Проверка существования пользователя
        $existing = $this->db->fetchOne(
            "SELECT id FROM users WHERE username = :username OR email = :email",
            ['username' => $username, 'email' => $email]
        );
        
        if ($existing) {
            return ['success' => false, 'message' => 'Пользователь с таким именем или email уже существует'];
        }
        
        // Хэширование пароля
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Создание пользователя
        try {
            $userId = $this->db->insert('users', [
                'username' => $username,
                'email' => $email,
                'password_hash' => $passwordHash
            ]);
            
            // Логирование действия
            $this->logAction($userId, 'login', 'Регистрация нового пользователя');
            
            return ['success' => true, 'user_id' => $userId, 'message' => 'Регистрация успешна'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Ошибка при регистрации: ' . $e->getMessage()];
        }
    }
    
    /**
     * Вход пользователя
     */
    public function login($username, $password) {
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE username = :username OR email = :email",
            ['username' => $username, 'email' => $username]
        );
        
        if (!$user) {
            return ['success' => false, 'message' => 'Пользователь не найден'];
        }
        
        if ($user['is_banned']) {
            return ['success' => false, 'message' => 'Аккаунт заблокирован: ' . ($user['ban_reason'] ?? 'Без причины')];
        }
        
        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Неверный пароль'];
        }
        
        // Обновление последней активности
        $this->db->update('users', [
            'last_login' => date('Y-m-d H:i:s'),
            'is_online' => 1
        ], 'id = :id', ['id' => $user['id']]);
        
        // Создание сессии
        $sessionToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        
        $this->db->insert('user_sessions', [
            'user_id' => $user['id'],
            'session_token' => $sessionToken,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'expires_at' => $expiresAt
        ]);
        
        // Сохранение в сессию PHP
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['session_token'] = $sessionToken;
        $_SESSION['username'] = $user['username'];
        
        // Логирование действия
        $this->logAction($user['id'], 'login', 'Вход в систему');
        
        return [
            'success' => true, 
            'user' => $user,
            'session_token' => $sessionToken,
            'message' => 'Вход выполнен успешно'
        ];
    }
    
    /**
     * Выход пользователя
     */
    public function logout($userId) {
        // Удаление сессии из БД
        $this->db->delete('user_sessions', 'user_id = :user_id', ['user_id' => $userId]);
        
        // Обновление статуса пользователя
        $this->db->update('users', ['is_online' => 0], 'id = :id', ['id' => $userId]);
        
        // Логирование действия
        $this->logAction($userId, 'logout', 'Выход из системы');
        
        // Очистка сессии PHP
        session_destroy();
        
        return ['success' => true, 'message' => 'Выход выполнен успешно'];
    }
    
    /**
     * Получение данных пользователя
     */
    public function getById($userId) {
        return $this->db->fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $userId]);
    }
    
    /**
     * Получение пользователя по имени
     */
    public function getByUsername($username) {
        return $this->db->fetchOne("SELECT * FROM users WHERE username = :username", ['username' => $username]);
    }
    
    /**
     * Проверка авторизации
     */
    public function isAuthenticated() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
            return false;
        }
        
        $session = $this->db->fetchOne(
            "SELECT * FROM user_sessions 
             WHERE user_id = :user_id 
             AND session_token = :token 
             AND expires_at > NOW()",
            [
                'user_id' => $_SESSION['user_id'],
                'token' => $_SESSION['session_token']
            ]
        );
        
        if (!$session) {
            return false;
        }
        
        // Обновление времени последней активности
        $this->db->update('user_sessions', 
            ['last_activity' => date('Y-m-d H:i:s')], 
            'id = :id', 
            ['id' => $session['id']]
        );
        
        return true;
    }
    
    /**
     * Получение текущего пользователя
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return $this->getById($_SESSION['user_id']);
    }
    
    /**
     * Обновление характеристик пользователя
     */
    public function updateStats($userId, $stats) {
        $allowedStats = ['strength', 'agility', 'intelligence', 'stamina', 'luck'];
        $data = [];
        
        foreach ($stats as $key => $value) {
            if (in_array($key, $allowedStats) && is_numeric($value)) {
                $data[$key] = (int)$value;
            }
        }
        
        if (empty($data)) {
            return false;
        }
        
        return $this->db->update('users', $data, 'id = :id', ['id' => $userId]) > 0;
    }
    
    /**
     * Добавление опыта
     */
    public function addExperience($userId, $amount) {
        $user = $this->getById($userId);
        if (!$user) return false;
        
        $bonus = $this->getRankBonus($user['rank_id']);
        $actualAmount = floor($amount * (1 + $bonus['bonus_xp'] / 100));
        
        $this->db->query(
            "UPDATE users SET experience = experience + :amount WHERE id = :id",
            ['amount' => $actualAmount, 'id' => $userId]
        );
        
        // Логирование
        $this->logAction($userId, 'other', "Получено {$actualAmount} опыта");
        
        return true;
    }
    
    /**
     * Добавление золота
     */
    public function addGold($userId, $amount) {
        $user = $this->getById($userId);
        if (!$user) return false;
        
        $bonus = $this->getRankBonus($user['rank_id']);
        $actualAmount = floor($amount * (1 + $bonus['bonus_gold'] / 100));
        
        $this->db->query(
            "UPDATE users SET gold = gold + :amount WHERE id = :id",
            ['amount' => $actualAmount, 'id' => $userId]
        );
        
        return true;
    }
    
    /**
     * Получение бонусов ранга
     */
    public function getRankBonus($rankId) {
        return $this->db->fetchOne("SELECT * FROM ranks WHERE id = :id", ['id' => $rankId]);
    }
    
    /**
     * Логирование действий пользователя
     */
    public function logAction($userId, $actionType, $description, $details = null) {
        $this->db->insert('action_logs', [
            'user_id' => $userId,
            'action_type' => $actionType,
            'description' => $description,
            'details' => $details ? json_encode($details) : null
        ]);
    }
    
    /**
     * Получение лидеров (лидерборд)
     */
    public function getLeaderboard($limit = 10, $offset = 0) {
        return $this->db->fetchAll(
            "SELECT * FROM leaderboard LIMIT :limit OFFSET :offset",
            ['limit' => (int)$limit, 'offset' => (int)$offset]
        );
    }
    
    /**
     * Восстановление энергии
     */
    public function regenerateEnergy($userId) {
        $user = $this->getById($userId);
        if (!$user) return false;
        
        $lastRegen = strtotime($user['last_login']);
        $now = time();
        $minutesPassed = floor(($now - $lastRegen) / 60);
        
        if ($minutesPassed > 0 && $user['energy'] < $user['max_energy']) {
            $energyToAdd = min($minutesPassed * ENERGY_REGEN_RATE, $user['max_energy'] - $user['energy']);
            
            $this->db->update('users', [
                'energy' => $user['energy'] + $energyToAdd
            ], 'id = :id', ['id' => $userId]);
            
            return $energyToAdd;
        }
        
        return 0;
    }
}
