<?php
/* ============================
   BULLETPROOF LOCALIZATION (Bypass Method)
============================ */
$astro_footer_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
$astro_footer_translations = [
    'hi' => [
        'Incoming Call' => 'आ रही कॉल',
        'Incoming Chat' => 'आ रही चैट',
        'Client is calling...' => 'क्लाइंट कॉल कर रहा है...',
        'Client wants to chat...' => 'क्लाइंट चैट करना चाहता है...',
        'Client Calling' => 'क्लाइंट कॉलिंग',
        'ACCEPT CALL' => 'कॉल स्वीकार करें',
        'ACCEPT CHAT' => 'चैट स्वीकार करें',
        'Dashboard' => 'डैशबोर्ड',
        'Calls' => 'कॉल्स',
        'Chats' => 'चैट्स',
        'Reports' => 'रिपोर्ट्स',
        'Profile' => 'प्रोफ़ाइल',
        'Active Chat' => 'सक्रिय चैट' // Added translation for dynamic chat state
    ]
];

if (!function_exists('__af')) {
    function __af($key) {
        global $astro_footer_translations, $astro_footer_lang;
        return isset($astro_footer_translations[$astro_footer_lang][$key]) ? $astro_footer_translations[$astro_footer_lang][$key] : $key;
    }
}

// --- Hardened Security: Safely Check for Active Chat Session ---
$active_chat = false;
if (isset($_SESSION['userid']) && $_SESSION['role'] == 1) {
    global $conn; 
    if (isset($conn)) {
        // High-level security: Prepared PDO statement to prevent injection
        $stmtActiveChat = $conn->prepare("SELECT id FROM chat_sessions WHERE astrologer_id = ? AND status = 'active' LIMIT 1");
        $stmtActiveChat->execute([(int)$_SESSION['userid']]);
        $active_chat = $stmtActiveChat->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<div id="globalIncomingPopup" class="hidden fixed bottom-24 left-4 right-4 bg-white rounded-[32px] shadow-[0_20px_50px_rgba(0,0,0,0.2)] border-2 border-emerald-500 p-5 z-[1000] transform transition-all duration-500 translate-y-full">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
        
        <div class="flex items-center gap-4 w-full sm:w-auto">
            <div id="incomingIconBg" class="flex-shrink-0 w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center animate-pulse">
                <i id="incomingIcon" class="fas fa-phone-alt text-emerald-600"></i>
            </div>
            <div class="overflow-hidden">
                <h4 id="incomingType" class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5"><?= __af('Incoming Call') ?></h4>
                <p id="globalCallerName" class="text-base font-bold text-gray-900 truncate"><?= __af('Client is calling...') ?></p>
            </div>
        </div>
        
        <div class="w-full sm:w-auto flex gap-2">
            <button onclick="rejectIncoming()" class="flex-1 sm:flex-none text-center bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-6 py-3 rounded-2xl font-bold text-sm transition dark:bg-red-900/30 dark:border-red-800 dark:text-red-400">
                REJECT
            </button>
            <a id="globalAcceptBtn" href="#" class="flex-1 sm:flex-none text-center bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3 rounded-2xl font-bold shadow-lg text-sm transition">
                <?= __af('ACCEPT CALL') ?>
            </a>
        </div>
        
    </div>
</div>

<audio id="globalRingtone" loop preload="auto">
    <source src="/assets/audio/ringtone.mp3" type="audio/mpeg">
</audio>

<div class="footer mobile-footer">
    <a href="/astrologer/dashboard.php" class="footer-item">
        <i class="fa fa-dashboard"></i>
        <span class="footer-text"><?= __af('Dashboard') ?></span>
    </a>
    <a href="/astrologer/call_history.php" class="footer-item">
        <i class="fa fa-phone"></i>
        <span class="footer-text"><?= __af('Calls') ?></span>
    </a>
    
    <?php if ($active_chat): ?>
        <a href="/astrologer/active_chat.php?session_id=<?= $active_chat['id'] ?>" class="footer-item" style="color: #10b981;">
            <div class="relative inline-block">
                <i class="fa fa-comment-dots"></i>
                <span class="absolute -top-1 -right-2 flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500 border-2 border-white dark:border-[#1f2937]"></span>
                </span>
            </div>
            <span class="footer-text" style="font-weight: 900;"><?= __af('Active Chat') ?></span>
        </a>
    <?php else: ?>
        <a href="/astrologer/active_chat.php" class="footer-item">
            <i class="fa fa-comment-dots"></i>
            <span class="footer-text"><?= __af('Chats') ?></span>
        </a>
    <?php endif; ?>

    <a href="/astrologer/commission_history.php" class="footer-item">
        <i class="fa fa-file"></i>
        <span class="footer-text"><?= __af('Reports') ?></span>
    </a>
    <a href="/astrologer/details.php" class="footer-item">
        <i class="fa fa-user"></i>
        <span class="footer-text"><?= __af('Profile') ?></span>
    </a>
</div>

<style>
    #globalIncomingPopup { max-width: 600px; margin: 0 auto; }
    .mobile-footer { display: flex; justify-content: space-around; align-items: center; position: fixed; left: 0; bottom: 0; width: 100%; background: #ffffff; padding: 10px 0; border-top: 1px solid #eee; z-index: 999; transition: background 0.3s ease; }
    .footer-item { flex: 1; text-align: center; color: #94a3b8; text-decoration: none; display: flex; flex-direction: column; align-items: center; gap: 4px; transition: color 0.3s ease; }
    .footer-item i { font-size: 18px; }
    .footer-item:hover, .footer-item.active { color: #0f172a; }
    .footer-text { font-size: 10px; font-weight: 700; }

    /* =========================================
       ENHANCED DARK MODE STYLES
    ========================================= */
    body.dark-theme .mobile-footer { background: #1f2937 !important; border-top: 1px solid #374151 !important; }
    body.dark-theme .footer-item { color: #9ca3af !important; }
    body.dark-theme .footer-item:hover, body.dark-theme .footer-item.active { color: #f9fafb !important; }
    
    body.dark-theme #globalIncomingPopup { background: #1f2937 !important; border-color: #10b981 !important; }
    body.dark-theme #globalIncomingPopup.chat-mode { border-color: #3b82f6 !important; }
    body.dark-theme #globalCallerName { color: #f9fafb !important; }
</style>

<script>
    /* ---------- GLOBAL INACTIVITY & CALL/CHAT TRACKER ---------- */
    (function() {
        // 1. Inactivity Logic
        let idleTimeout;
        const oneMinute = 1 * 60 * 1000; // 1 Minute Timer
        const isOnline = <?= isset($_SESSION['online_status']) ? (int)$_SESSION['online_status'] : 0 ?>;

        function resetIdleTimer() {
            clearTimeout(idleTimeout);
            if (isOnline === 1) {
                idleTimeout = setTimeout(forceGlobalOffline, oneMinute);
            }
        }

        function forceGlobalOffline() {
            console.log("Global Inactivity: Switching to Offline.");
            const params = new URLSearchParams();
            params.append('set_status', '1');
            params.append('online_status', '0');

            fetch('/astrologer/dashboard.php', {
                method: 'POST',
                body: params,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            }).then(() => {
                window.location.reload(); 
            });
        }

        // Activity Listeners
        window.onload = resetIdleTimer;
        document.onmousemove = resetIdleTimer;
        document.onkeypress = resetIdleTimer;
        document.onclick = resetIdleTimer;
        document.onscroll = resetIdleTimer;

        // 2. Incoming Check Logic (Calls & Chats)
        let isCheckingIncoming = false;
        const globalRing = document.getElementById('globalRingtone');
        const globalPopup = document.getElementById('globalIncomingPopup');
        
        // Dynamic Elements
        const titleEl = document.getElementById('incomingType');
        const nameEl = document.getElementById('globalCallerName');
        const iconEl = document.getElementById('incomingIcon');
        const iconBgEl = document.getElementById('incomingIconBg');
        const acceptBtn = document.getElementById('globalAcceptBtn');

        // State Tracking for Reject
        let activeIncomingType = '';
        let activeIncomingId = 0;

        // Translation Strings
        const txtClientCalling = "<?= __af('Client Calling') ?>";
        const txtClientChatting = "<?= __af('Client wants to chat...') ?>";
        const txtCallTitle = "<?= __af('Incoming Call') ?>";
        const txtChatTitle = "<?= __af('Incoming Chat') ?>";
        const txtAcceptCall = "<?= __af('ACCEPT CALL') ?>";
        const txtAcceptChat = "<?= __af('ACCEPT CHAT') ?>";

        function showPopup(type, id, userName) {
            activeIncomingType = type;
            activeIncomingId = id;
            nameEl.innerText = userName || (type === 'call' ? txtClientCalling : txtClientChatting);
            
            if (type === 'call') {
                titleEl.innerText = txtCallTitle;
                iconEl.className = "fas fa-phone-alt text-emerald-600";
                iconBgEl.className = "flex-shrink-0 w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center animate-pulse";
                acceptBtn.className = "flex-1 sm:flex-none text-center bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3 rounded-2xl font-bold shadow-lg text-sm transition";
                acceptBtn.innerText = txtAcceptCall;
                acceptBtn.href = "/astrologer/accept_call.php?call_id=" + id;
                globalPopup.classList.remove('chat-mode');
            } else {
                titleEl.innerText = txtChatTitle;
                iconEl.className = "fas fa-comment-dots text-blue-600";
                iconBgEl.className = "flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center animate-pulse";
                acceptBtn.className = "flex-1 sm:flex-none text-center bg-blue-500 hover:bg-blue-600 text-white px-8 py-3 rounded-2xl font-bold shadow-lg text-sm transition";
                acceptBtn.innerText = txtAcceptChat;
                acceptBtn.href = "/astrologer/accept_chat.php?session_id=" + id;
                globalPopup.classList.add('chat-mode');
            }

            globalPopup.classList.remove('hidden', 'translate-y-full');
            
            // Play Ringtone
            if (typeof AndroidBridge !== 'undefined') {
                AndroidBridge.playNativeRingtone();
            } else {
                if (globalRing.paused) globalRing.play().catch(e => {});
            }
        }

        function hidePopup() {
            globalPopup.classList.add('hidden', 'translate-y-full');
            // Stop Ringtone
            if (typeof AndroidBridge !== 'undefined') {
                AndroidBridge.stopNativeRingtone();
            } else {
                if (!globalRing.paused) { globalRing.pause(); globalRing.currentTime = 0; }
            }
        }

        // Action: Reject Incoming
        window.rejectIncoming = async function() {
            hidePopup();
            try {
                // Hits the unified reject endpoint for both calls and chats
                await fetch(`/astrologer/reject_incoming.php?type=${activeIncomingType}&id=${activeIncomingId}`);
            } catch(e) {
                console.error("Failed to reject incoming request", e);
            }
        };

        async function checkIncoming() {
            if (isCheckingIncoming) return;
            isCheckingIncoming = true;
            try {
                // 1. Check for Calls First
                const respCall = await fetch('/astrologer/check_incoming.php');
                const dataCall = await respCall.json();
                
                if (dataCall.has_call) {
                    showPopup('call', dataCall.call_id, dataCall.user_name);
                    isCheckingIncoming = false;
                    return; // Stop checking chats if a call is ringing
                }

                // 2. Check for Chats Second
                const respChat = await fetch('/astrologer/check_incoming_chat.php');
                const dataChat = await respChat.json();

                if (dataChat.has_chat) {
                    showPopup('chat', dataChat.session_id, dataChat.user_name);
                } else {
                    hidePopup(); // Hide if neither call nor chat exists
                }

            } catch (e) {} finally { isCheckingIncoming = false; }
        }
        
        // Poll for incoming requests every 2 seconds
        setInterval(checkIncoming, 2000);
    })();
</script>