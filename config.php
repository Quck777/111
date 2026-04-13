<?php
/**
 * Medieval Realm RPG - Database Config
 * Версия: 2.3.0
 * Дата: 2026-04-12
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'ci767629_1');
define('DB_USER', 'ci767629_1');
define('DB_PASS', '1234');

session_start();

class Database {
    private static ?PDO $pdo = null;
    
    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new Exception("Cannot connect to database");
            }
        }
        return self::$pdo;
    }
}