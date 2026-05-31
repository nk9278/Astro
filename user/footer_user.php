<?php
// Start session securely if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine authentication state
$is_logged_in = isset($_SESSION['userid']);

// --- BULLETPROOF LOCALIZATION (Bypass Method for Footer) ---
$footer_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
$footer_translations = [
    'hi' => [
        'Home' => 'होम',
        'Call' => 'कॉल',
        'Services' => 'सेवाएं',
        'Wallet' => 'वॉलेट',
        'Chats' => 'चैट्स',
        'Active Chat' => 'सक्रिय चैट'
    ]
];

function __f($key) {
    global $footer_translations, $footer_lang;
    return isset($footer_translations[$footer_lang][$key]) ? $footer_translations[$footer_lang][$key] : htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
}

// --- PROFILE LOCK LOGIC ---
// If details.php has flagged the profile as incomplete, disable navigation
$disable_nav = isset($is_incomplete) && $is_incomplete;
$item_class = 'footer-item' . ($disable_nav ? ' disabled-nav' : '');

/**
 * Hardened Security: Securely determines the correct href for protected routes.
 * Redirects unauthenticated users to login with a sanitized return path.
 */
function get_protected_link($target_path, $is_logged_in, $disable_nav) {
    if ($disable_nav) {
        return 'javascript:void(0)';
    }
    if ($is_logged_in) {
        return htmlspecialchars($target_path, ENT_QUOTES, 'UTF-8');
    }
    return '/auth/login.php?redirect=' . urlencode($target_path);
}

// --- ACTIVE CHAT CHECK ---
$active_chat = false;
if ($is_logged_in) {
    global $conn; 
    if (isset($conn)) {
        $stmtActiveChat = $conn->prepare("SELECT id FROM chat_sessions WHERE user_id = ? AND status = 'active' LIMIT 1");
        $stmtActiveChat->execute([(int)$_SESSION['userid']]);
        $active_chat = $stmtActiveChat->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<footer class="mobile-footer">
    <a href="<?= $disable_nav ? 'javascript:void(0)' : '/' ?>" class="<?= $item_class ?>">
        <span class="material-icons">home</span>
        <span class="footer-text"><?= __f('Home') ?></span>
    </a>
    <a href="<?= $disable_nav ? 'javascript:void(0)' : '/astrologer/list.php' ?>" class="<?= $item_class ?>">
        <span class="material-icons">call</span>
        <span class="footer-text"><?= __f('Call') ?></span>
    </a>
    
    <a href="<?= get_protected_link('/services/all_services.php', $is_logged_in, $disable_nav) ?>" class="<?= $item_class ?>">
        <span class="material-icons">list</span>
        <span class="footer-text"><?= __f('Services') ?></span>
    </a>
    
    <a href="<?= get_protected_link('/user/wallet.php', $is_logged_in, $disable_nav) ?>" class="<?= $item_class ?>">
        <span class="material-icons">account_balance_wallet</span>
        <span class="footer-text"><?= __f('Wallet') ?></span>
    </a>
    
    <?php if ($active_chat): ?>
        <a href="<?= $disable_nav ? 'javascript:void(0)' : '/user/active_chat.php?session_id=' . $active_chat['id'] ?>" class="<?= $item_class ?> active-chat-glow">
            <span class="material-icons relative-icon">
                chat
                <span class="status-indicator">
                    <span class="status-ping"></span>
                    <span class="status-dot"></span>
                </span>
            </span>
            <span class="footer-text"><?= __f('Active Chat') ?></span>
        </a>
    <?php else: ?>
        <a href="<?= get_protected_link('/user/active_chat.php', $is_logged_in, $disable_nav) ?>" class="<?= $item_class ?>">
            <span class="material-icons">chat</span>
            <span class="footer-text"><?= __f('Chats') ?></span>
        </a>
    <?php endif; ?>
</footer>

<style>
/* Base Mobile Footer Styles */
.mobile-footer {
    display: flex;
    justify-content: space-around;
    align-items: center;
    position: fixed;
    left: 0;
    bottom: 0;
    width: 100%;
    background: #f8f8f8;
    padding: 0.5rem 0 0.3rem 0;
    border-top: 1px solid #ccc;
    z-index: 999;
    box-shadow: 0 -2px 8px rgba(0,0,0,0.05);
    transition: background 0.3s, border-color 0.3s;
}
.footer-item {
    flex: 1;
    text-align: center;
    color: #666666;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1px;
    font-size: 15px;
    transition: color 0.2s;
    padding: 0 2px;
    min-width: 58px;
}
.footer-item .material-icons {
    font-size: 24px;
    color: #666666;
    margin-bottom: 4px;
    display: block;
    line-height: 1;
    vertical-align: middle;
    flex-shrink: 0;
    transition: color 0.2s;
}
.footer-item .footer-text {
    font-size: 13px;
    color: #666666;
    line-height: 1;
    transition: color 0.2s;
}
.footer-item.active .material-icons,
.footer-item:active .material-icons,
.footer-item:focus .material-icons,
.footer-item:hover .material-icons {
    color: #1565c0;
}
.footer-item.active .footer-text,
.footer-item:active .footer-text,
.footer-item:focus .footer-text,
.footer-item:hover .footer-text {
    color: #1565c0;
}

/* =========================================
   UI LOCK: DISABLED NAVIGATION STATE
========================================= */
.disabled-nav {
    pointer-events: none !important; /* Physically prevents all clicks/taps */
    opacity: 0.4 !important; /* Visually indicates it is disabled */
    cursor: default !important;
}

/* =========================================
   ACTIVE CHAT ANIMATION & STYLES
========================================= */
.active-chat-glow .material-icons, 
.active-chat-glow .footer-text {
    color: #10b981 !important; /* Emerald Green */
    font-weight: 900;
}
.relative-icon {
    position: relative;
    display: inline-block;
}
.status-indicator {
    position: absolute;
    top: -2px;
    right: -4px;
    display: flex;
    height: 10px;
    width: 10px;
}
.status-ping {
    position: absolute;
    display: inline-flex;
    height: 100%;
    width: 100%;
    border-radius: 50%;
    background-color: #34d399;
    opacity: 0.75;
    animation: custom-ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}
.status-dot {
    position: relative;
    display: inline-flex;
    border-radius: 50%;
    height: 10px;
    width: 10px;
    background-color: #10b981;
    border: 2px solid #f8f8f8;
}

@keyframes custom-ping {
    75%, 100% {
        transform: scale(2);
        opacity: 0;
    }
}

/* =========================================
   ENHANCED DARK MODE STYLES
========================================= */
body.dark-theme .mobile-footer {
    background: #1f2937 !important;
    border-top: 1px solid #374151 !important;
    box-shadow: none !important;
}

body.dark-theme .footer-item .material-icons,
body.dark-theme .footer-item .footer-text {
    color: #9ca3af !important; /* Muted gray for inactive items */
}

/* Active/Hover states in Dark Mode */
body.dark-theme .footer-item.active .material-icons,
body.dark-theme .footer-item:active .material-icons,
body.dark-theme .footer-item:focus .material-icons,
body.dark-theme .footer-item:hover .material-icons,
body.dark-theme .footer-item.active .footer-text,
body.dark-theme .footer-item:active .footer-text,
body.dark-theme .footer-item:focus .footer-text,
body.dark-theme .footer-item:hover .footer-text {
    color: #f9fafb !important; /* Bright white/gray for active items */
}

body.dark-theme .status-dot {
    border-color: #1f2937 !important;
}
</style>