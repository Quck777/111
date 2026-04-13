/**
 * Medieval Realm RPG - Router
 * SPA Router for navigation
 */

// Current route
var currentRoute = 'character';

// Route definitions - load functions resolved at call time
var routes = {
    character: 'loadCharacter',
    battle: 'loadEnemies',
    arena: 'loadArena',
    shop: 'loadShop',
    inventory: 'loadInventory',
    map: 'loadLocations',
    licenses: 'loadLicenses',
    skills: 'loadSkills',
    quests: 'loadQuests',
    achievements: 'loadAchievements',
    leaderboard: 'loadLeaderboard',
    guilds: 'loadGuilds',
    guildWars: 'loadGuildWars',
    market: 'loadMarketListings',
    history: 'loadHistory',
    raids: 'loadRaidBosses',
    chat: 'loadChat',
    admin: null
};

// Router function
function router(section) {
    // Hide all sections
    var sections = document.querySelectorAll('main > section');
    sections.forEach(function(s) {
        s.style.display = 'none';
    });
    
    // Show target section
    var target = document.getElementById(section + '-section');
    if (target) {
        target.style.display = 'block';
        target.style.animation = 'fadeIn 0.2s ease';
    }
    
    currentRoute = section;
    
    // Load data for section (only if logged in)
    if (currentUser && routes[section]) {
        var loadFn = routes[section];
        if (typeof loadFn === 'string' && window[loadFn]) {
            window[loadFn]();
        } else if (typeof loadFn === 'function') {
            loadFn();
        }
    }
}

// Shortcut aliases
function showSection(section) {
    router(section);
}

// Leaderboard switch
function switchLB(tab) {
    document.querySelectorAll('.rating-tab').forEach(function(b) { b.classList.remove('active'); });
    event.target.classList.add('active');
    document.getElementById('players-rating').style.display = tab === 'players' ? 'block' : 'none';
    document.getElementById('guilds-rating').style.display = tab === 'guilds' ? 'block' : 'none';
    if (tab === 'players') loadLeaderboard();
    else loadGuildsForRating();
}

// Expose to window
window.router = router;
window.showSection = showSection;
window.switchLB = switchLB;