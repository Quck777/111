<?php
/**
 * API Router - Main Entry Point for All API Requests
 * Medieval Realm RPG
 * 
 * This file loads all modules and routes API actions
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config.php';

try {
    Database::getConnection();
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Ошибка подключения к БД: ' . $e->getMessage()]);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

define('GAME_MODULE', true);

// Load all modules
require_once __DIR__ . '/auth/auth.php';
require_once __DIR__ . '/battle/battle.php';
require_once __DIR__ . '/inventory/inventory.php';
require_once __DIR__ . '/chat/chat.php';
require_once __DIR__ . '/guild/guild.php';
require_once __DIR__ . '/market/market.php';
require_once __DIR__ . '/admin/admin.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (empty($action)) {
    echo json_encode(['error' => 'No action specified']);
    exit;
}

$allowedActions = [
    // Auth
    'register', 'login', 'logout', 'getUser', 'getOnlineUsers',
    // Battle
    'getEnemies', 'startPveBattle', 'attack', 'usePotion',
    'startPvpBattle', 'pvpAttack',
    'getLeaderboard', 'getPvpLeaderboard', 'getOpponentsForArena',
    'getHistory',
    // Inventory & Items
    'getInventory', 'getItems', 'buyItem', 'equipItem', 'unequipItem', 'useItem',
    'getSkills', 'getLocations', 'getLicenses', 'buyLicense',
    // Chat
    'getChat', 'sendChat',
    // Guild
    'getGuilds', 'createGuild', 'joinGuild', 'leaveGuild', 'donateToGuild',
    'getGuildWars', 'getMyGuildWarStatus', 'declareGuildWar', 'joinGuildWar', 
    'attackGuildWarEnemy', 'getGuildWarLeaderboard',
    // Market
    'getMarketListings', 'createMarketListing', 'buyFromMarket', 'cancelMarketListing', 'getMyMarketListings',
    // Admin
    'adminLogin', 'adminGetUsers', 'adminUpdateUser', 'adminDeleteUser', 'adminResetPassword',
    'adminGetItems', 'adminSaveItem', 'adminDeleteItem',
    'adminGetEnemies', 'adminSaveEnemy', 'adminDeleteEnemy',
    'adminGetQuests', 'adminSaveQuest', 'adminDeleteQuest',
    'adminGetGuilds', 'adminDeleteGuild',
    'adminGetRaids', 'adminSaveRaid', 'adminDeleteRaid', 'adminGetStats',
    'adminSendAnnouncement', 'adminGiveItem', 'adminTeleportUser', 'adminBanUser'
];

if (!in_array($action, $allowedActions)) {
    echo json_encode(['error' => 'Unknown action: ' . $action]);
    exit;
}

try {
    switch ($action) {
        // Auth
        case 'register': register(); break;
        case 'login': login(); break;
        case 'logout': logout(); break;
        case 'getUser': getUser(); break;
        case 'getOnlineUsers': getOnlineUsers(); break;
        
        // Battle
        case 'getEnemies': getEnemies(); break;
        case 'startPveBattle': startPveBattle(); break;
        case 'attack': attack(); break;
        case 'usePotion': usePotion(); break;
        case 'startPvpBattle': startPvpBattleApi(); break;
        case 'pvpAttack': pvpAttack(); break;
        case 'getLeaderboard': getLeaderboard(); break;
        case 'getPvpLeaderboard': getPvpLeaderboard(); break;
        case 'getOpponentsForArena': getOpponentsForArena(); break;
        case 'getHistory': getHistory(); break;
        
        // Inventory & Items
        case 'getInventory': getInventory(); break;
        case 'getItems': getItems(); break;
        case 'buyItem': buyItem(); break;
        case 'equipItem': equipItem(); break;
        case 'unequipItem': unequipItem(); break;
        case 'useItem': useItem(); break;
        case 'getSkills': getSkills(); break;
        case 'getLocations': getLocations(); break;
        case 'getLicenses': getLicenses(); break;
        case 'buyLicense': buyLicense(); break;
        
        // Chat
        case 'getChat': getChat(); break;
        case 'sendChat': sendChat(); break;
        
        // Guild
        case 'getGuilds': getGuilds(); break;
        case 'createGuild': createGuild(); break;
        case 'joinGuild': joinGuild(); break;
        case 'leaveGuild': leaveGuild(); break;
        case 'donateToGuild': donateToGuild(); break;
        case 'getGuildWars': getGuildWars(); break;
        case 'getMyGuildWarStatus': getMyGuildWarStatus(); break;
        case 'declareGuildWar': declareGuildWar(); break;
        case 'joinGuildWar': joinGuildWar(); break;
        case 'attackGuildWarEnemy': attackGuildWarEnemy(); break;
        case 'getGuildWarLeaderboard': getGuildWarLeaderboard(); break;
        
        // Market
        case 'getMarketListings': getMarketListings(); break;
        case 'createMarketListing': createMarketListing(); break;
        case 'buyFromMarket': buyFromMarket(); break;
        case 'cancelMarketListing': cancelMarketListing(); break;
        case 'getMyMarketListings': getMyMarketListings(); break;
        
        // Admin
        case 'adminLogin': adminLogin(); break;
        case 'adminGetUsers': adminGetUsers(); break;
        case 'adminUpdateUser': adminUpdateUser(); break;
        case 'adminDeleteUser': adminDeleteUser(); break;
        case 'adminResetPassword': adminResetPassword(); break;
        case 'adminGetItems': adminGetItems(); break;
        case 'adminSaveItem': adminSaveItem(); break;
        case 'adminDeleteItem': adminDeleteItem(); break;
        case 'adminGetEnemies': adminGetEnemies(); break;
        case 'adminSaveEnemy': adminSaveEnemy(); break;
        case 'adminDeleteEnemy': adminDeleteEnemy(); break;
        case 'adminGetQuests': adminGetQuests(); break;
        case 'adminSaveQuest': adminSaveQuest(); break;
        case 'adminDeleteQuest': adminDeleteQuest(); break;
        case 'adminGetGuilds': adminGetGuilds(); break;
        case 'adminDeleteGuild': adminDeleteGuild(); break;
        case 'adminGetRaids': adminGetRaids(); break;
        case 'adminSaveRaid': adminSaveRaid(); break;
        case 'adminDeleteRaid': adminDeleteRaid(); break;
        case 'adminGetStats': adminGetStats(); break;
        case 'adminSendAnnouncement': adminSendAnnouncement(); break;
        case 'adminGiveItem': adminGiveItem(); break;
        case 'adminTeleportUser': adminTeleportUser(); break;
        case 'adminBanUser': adminBanUser(); break;
        
        default: 
            echo json_encode(['error' => 'Unknown action']);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
