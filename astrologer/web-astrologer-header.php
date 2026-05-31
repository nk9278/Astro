<?php
// Fetch astrologer name
$astroName = "Astrologer";
if (isset($_SESSION['userid'])) {
    require_once __DIR__ . '/../db.php';
    $stmt = $conn->prepare("SELECT astrologer_name FROM users WHERE id=?");
    $stmt->execute([$_SESSION['userid']]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u) $astroName = htmlspecialchars($u['astrologer_name'], ENT_QUOTES, 'UTF-8');
}

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Astrologer Panel' => 'ज्योतिषी पैनल',
            'Dashboard' => 'डैशबोर्ड',
            'Call History' => 'कॉल इतिहास',
            'Reports' => 'रिपोर्ट्स',
            'Earnings' => 'कमाई',
            'My Profile' => 'मेरी प्रोफ़ाइल',
            'Commission History' => 'कमीशन इतिहास',
            'Logout' => 'लॉग आउट',
            'Incoming Call' => 'आ रही कॉल',
            'Client Calling' => 'क्लाइंट कॉलिंग',
            'Accept' => 'स्वीकार करें',
            'Reject' => 'अस्वीकार करें'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}
?>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
/* =========================================
   ENHANCED DARK MODE STYLES FOR HEADER
========================================= */
body.dark-theme header.bg-white { background-color: #1f2937 !important; border-bottom: 1px solid #374151 !important; }
body.dark-theme header .text-gray-700 { color: #d1d5db !important; }
body.dark-theme header .text-gray-800 { color: #f9fafb !important; }
body.dark-theme header .hover\:text-indigo-600:hover { color: #818cf8 !important; }

body.dark-theme header .bg-amber-50 { background-color: rgba(245, 158, 11, 0.15) !important; border-color: rgba(245, 158, 11, 0.2) !important; color: #fbbf24 !important; }

/* Dropdown Menu */
body.dark-theme .group .absolute.bg-white { background-color: #374151 !important; border-color: #4b5563 !important; }
body.dark-theme .hover\:bg-indigo-50:hover { background-color: #4b5563 !important; }
body.dark-theme .border-gray-100 { border-color: #4b5563 !important; }

/* Incoming Call Popup */
body.dark-theme #incomingCallBox { background-color: #1f2937 !important; border-color: #10b981 !important; }
body.dark-theme #incomingCallBox h3 { color: #f9fafb !important; }
body.dark-theme #incomingUser { color: #d1d5db !important; }
</style>

<header class="hidden lg:block bg-white border-b border-gray-200 sticky top-0 z-[1001] transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between h-16">

            <a href="/astrologer/dashboard.php" class="flex items-center space-x-2 text-xl font-bold tracking-wide text-indigo-700">
                <img src="/assets/images/FP Logo.png" alt="Logo" class="w-9 h-9 object-contain rounded-full shadow-sm">
                <span><?= __('Astrologer Panel') ?></span>
            </a>

            <nav class="flex items-center space-x-8 text-sm font-medium">
                <a href="/astrologer/dashboard.php" class="flex items-center text-gray-700 hover:text-indigo-600 transition">
                    <i class="fa fa-dashboard mr-2"></i> <?= __('Dashboard') ?>
                </a>
                <a href="/astrologer/call_history.php" class="flex items-center text-gray-700 hover:text-indigo-600 transition">
                    <i class="fa fa-phone mr-2"></i> <?= __('Call History') ?>
                </a>
                <a href="/astrologer/reports.php" class="flex items-center text-gray-700 hover:text-indigo-600 transition">
                    <i class="fa fa-file mr-2"></i> <?= __('Reports') ?>
                </a>
            </nav>

            <div class="flex items-center space-x-6">
                
                <a href="/astrologer/wallet.php" class="flex items-center bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200 text-amber-700 hover:bg-amber-100 transition">
                    <span class="material-icons text-lg mr-1">payments</span>
                    <span class="text-xs font-bold uppercase tracking-tight"><?= __('Earnings') ?></span>
                </a>

                <div class="relative group h-16 flex items-center">
                    <div class="flex items-center text-sm font-semibold text-gray-800 cursor-pointer group-hover:text-indigo-600 transition">
                        <span class="material-icons text-2xl mr-1 text-indigo-500">account_circle</span>
                        <span><?= $astroName ?></span>
                        <span class="material-icons text-xs ml-1">expand_more</span>
                    </div>

                    <div class="absolute top-16 right-0 w-56 bg-white border border-gray-200 shadow-xl rounded-b-xl py-2 invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-300 transform origin-top translate-y-2 group-hover:translate-y-0">
                        
                        <a href="/astrologer/my_profile.php" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                            <span class="material-icons text-lg mr-3 opacity-70">badge</span> <?= __('My Profile') ?>
                        </a>
                        
                        <a href="/astrologer/commission_history.php" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                            <span class="material-icons text-lg mr-3 opacity-70">account_balance</span> <?= __('Commission History') ?>
                        </a>

                        <div class="my-2 border-t border-gray-100"></div>
                        
                        <a href="/logout.php" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 font-bold transition">
                            <span class="material-icons text-lg mr-3">logout</span> <?= __('Logout') ?>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</header>

<!-- Desktop Call Popup Window -->
<div id="incomingCallBoxDesk" style="position:fixed; bottom:30px; right:30px; background:#fff; padding:20px; width:320px; border-radius:16px; box-shadow:0 15px 50px rgba(0,0,0,0.3); text-align:center; display:none; z-index:999999; border: 2px solid #22c55e;">
  <h3 style="margin:0; font-size:18px; color:#222;">📞 <?= __('Incoming Call') ?></h3>
  <p id="incomingUserDesk" style="color:#666; margin:10px 0; font-weight:bold;"></p>
  <div style="display:flex;justify-content:center;gap:12px;margin-top:15px;">
    <button id="acceptBtnDesk" style="background:#22c55e;color:#fff;padding:10px 25px;border-radius:8px; border:none; cursor:pointer; font-weight:600;"><?= __('Accept') ?></button>
    <button id="rejectBtnDesk" style="background:#ef4444;color:#fff;padding:10px 25px;border-radius:8px; border:none; cursor:pointer; font-weight:600;"><?= __('Reject') ?></button>
  </div>
</div>

<audio id="globalRingtoneDesk" src="/assets/audio/ringtone.mp3" preload="auto" loop></audio>

<script>
    // Apply Dark Theme Class to body if setting is present
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }

    /* ---------- DESKTOP INCOMING CALL TRACKER ---------- */
    (function() {
        let isCheckingCallDesk = false;
        const ringDesk = document.getElementById('globalRingtoneDesk');
        const popupDesk = document.getElementById('incomingCallBoxDesk');
        const txtClientCallingDesk = "<?= __('Client Calling') ?>";

        async function pollIncomingDesk() {
            if (isCheckingCallDesk) return;
            isCheckingCallDesk = true;
            try {
                const response = await fetch('/astrologer/check_incoming.php');
                const data = await response.json();
                
                if (data.has_call) {
                    document.getElementById('incomingUserDesk').innerText = data.user_name || txtClientCallingDesk;
                    document.getElementById('acceptBtnDesk').onclick = () => {
                        window.location.href = "/astrologer/accept_call.php?call_id=" + data.call_id;
                    };
                    document.getElementById('rejectBtnDesk').onclick = () => {
                        window.location.href = "/astrologer/reject_call.php?call_id=" + data.call_id;
                    };
                    
                    popupDesk.style.display = "block";
                    
                    // Trigger Audio
                    if (typeof AndroidBridge !== 'undefined') {
                        AndroidBridge.playNativeRingtone();
                    } else if (ringDesk.paused) {
                        ringDesk.play().catch(e => {});
                    }
                } else {
                    popupDesk.style.display = "none";
                    
                    // Stop Audio
                    if (typeof AndroidBridge !== 'undefined') {
                        AndroidBridge.stopNativeRingtone();
                    } else if (!ringDesk.paused) {
                        ringDesk.pause();
                        ringDesk.currentTime = 0;
                    }
                }
            } catch (e) {} finally { isCheckingCallDesk = false; }
        }
        setInterval(pollIncomingDesk, 3000);
    })();
</script>