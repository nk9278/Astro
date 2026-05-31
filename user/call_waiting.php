<?php
session_start();
require '../db.php';

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Connecting... - Fortune Parth' => 'कनेक्ट हो रहा है... - फॉर्च्यून पाथ',
            'Invalid waiting link' => 'अमान्य प्रतीक्षा लिंक',
            'Connecting Expert' => 'विशेषज्ञ कनेक्ट हो रहा है',
            'Waiting for astrologer to accept...' => 'ज्योतिषी के स्वीकार करने की प्रतीक्षा में...',
            'Ringing Time:' => 'रिंगिंग समय:',
            'sec' => 'सेकंड',
            'Cancel Call' => 'कॉल रद्द करें',
            'Recent Calls' => 'हाल की कॉल',
            'Syncing records...' => 'रिकॉर्ड सिंक कर रहे हैं...',
            'Live Signal' => 'लाइव सिग्नल',
            'Establishing Line' => 'लाइन स्थापित कर रहे हैं',
            'Please do not refresh. We are currently notifying the astrologer of your request.' => 'कृपया रीफ्रेश न करें। हम वर्तमान में आपके अनुरोध के बारे में ज्योतिषी को सूचित कर रहे हैं।',
            'Time Remaining' => 'समय शेष',
            'Encryption' => 'एन्क्रिप्शन',
            'Secure' => 'सुरक्षित',
            'End Call Attempt' => 'कॉल प्रयास समाप्त करें',
            'Recent Activity' => 'हाल की गतिविधि',
            'Failed to load history' => 'इतिहास लोड करने में विफल'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

$astro_id = intval($_GET['astro_id'] ?? 0);
$call_id  = intval($_GET['call_id'] ?? 0);

if ($astro_id <= 0 || $call_id <= 0) {
    die(__('Invalid waiting link'));
}

$user_id = $_SESSION['userid'] ?? 0;
if ($user_id <= 0) {
    header("Location: /login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title><?= __('Connecting... - Fortune Parth') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; transition: background 0.3s ease; }
        
        .loader-ring {
            position: relative;
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loader-ring::before, .loader-ring::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            border: 3px solid #10b981;
            animation: ripple 2s linear infinite;
        }

        .loader-ring::after { animation-delay: 1s; }

        @keyframes ripple {
            0% { width: 80px; height: 80px; opacity: 1; border-width: 4px; }
            100% { width: 180px; height: 180px; opacity: 0; border-width: 1px; }
        }

        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }

        /* DARK MODE STYLES */
        body.dark-theme { background-color: #121212 !important; color: #f9fafb !important; }
        body.dark-theme .bg-slate-50 { background-color: #121212 !important; }
        body.dark-theme .bg-white { background-color: #1f2937 !important; border-color: #374151 !important; }
        body.dark-theme .text-slate-900, body.dark-theme .text-slate-800 { color: #ffffff !important; }
        body.dark-theme .text-slate-500, body.dark-theme .text-slate-400 { color: #d1d5db !important; }
        body.dark-theme .border-slate-100, body.dark-theme .border-slate-200 { border-color: #374151 !important; }
        body.dark-theme .bg-rose-50 { background-color: rgba(225, 29, 72, 0.15) !important; border-color: rgba(225, 29, 72, 0.3) !important; }
        body.dark-theme .shadow-slate-200\/50 { box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5) !important; }
        body.dark-theme .bg-emerald-50 { background-color: rgba(16, 185, 129, 0.15) !important; }
        body.dark-theme .bg-slate-900 { background-color: #374151 !important; color: #ffffff !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<!-- MOBILE VIEW -->
<div class="lg:hidden flex flex-col min-h-screen p-6">
    <div class="flex-grow flex flex-col items-center justify-center py-10">
        <div class="loader-ring mb-12">
            <div class="w-20 h-20 bg-emerald-500 rounded-full flex items-center justify-center text-white shadow-xl shadow-emerald-200 relative z-10">
                <i class="fas fa-phone-alt text-2xl animate-pulse"></i>
            </div>
        </div>

        <h2 class="text-3xl font-extrabold text-slate-900 mb-2 tracking-tight"><?= __('Connecting Expert') ?></h2>
        <p id="status_mob" class="text-slate-500 font-medium mb-8"><?= __('Waiting for astrologer to accept...') ?></p>
        
        <div class="bg-white px-6 py-4 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-4 mb-10">
            <i class="fas fa-clock text-emerald-500"></i>
            <span id="ringTimer_mob" class="text-lg font-bold text-slate-800"><?= __('Ringing Time:') ?> 60 <?= __('sec') ?></span>
        </div>

        <button class="w-full bg-rose-50 text-rose-600 py-5 rounded-[2rem] font-bold text-lg border border-rose-100 active:scale-95 transition-all shadow-sm" onclick="cancelCall()">
            <i class="fas fa-times-circle mr-2"></i> <?= __('Cancel Call') ?>
        </button>
    </div>

    <div class="mt-auto pt-6 border-t border-slate-200">
        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4"><?= __('Recent Calls') ?></h3>
        <div id="callHistory_mob" class="space-y-3 max-h-48 overflow-y-auto custom-scrollbar">
             <div class="p-4 text-center text-slate-400 text-sm italic"><?= __('Syncing records...') ?></div>
        </div>
    </div>
</div>

<!-- DESKTOP VIEW -->
<div class="hidden lg:block">
    <div class="max-w-[1400px] mx-auto px-12 py-16">
        <div class="grid grid-cols-12 gap-16 items-start">
            
            <div class="col-span-7">
                <div class="bg-white rounded-[3.5rem] p-16 shadow-2xl shadow-slate-200/50 border border-slate-100 text-center">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-full text-xs font-black uppercase tracking-widest mb-10">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-ping"></span> <?= __('Live Signal') ?>
                    </div>

                    <div class="flex justify-center mb-12">
                         <div class="loader-ring">
                            <div class="w-24 h-24 bg-slate-900 rounded-full flex items-center justify-center text-white relative z-10">
                                <i class="fas fa-phone-volume text-3xl animate-bounce"></i>
                            </div>
                        </div>
                    </div>

                    <h1 class="text-6xl font-black text-slate-900 mb-6 tracking-tighter"><?= __('Establishing Line') ?></h1>
                    <p id="status_web" class="text-xl text-slate-400 max-w-md mx-auto mb-12 font-medium leading-relaxed"><?= __('Please do not refresh. We are currently notifying the astrologer of your request.') ?></p>

                    <div class="grid grid-cols-2 gap-8 max-w-lg mx-auto mb-12">
                        <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1"><?= __('Time Remaining') ?></p>
                            <p id="ringTimer_web" class="text-3xl font-black text-slate-900">60 <?= __('sec') ?></p>
                        </div>
                        <div class="bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1"><?= __('Encryption') ?></p>
                            <p class="text-3xl font-black text-emerald-500"><?= __('Secure') ?></p>
                        </div>
                    </div>

                    <button onclick="cancelCall()" class="px-16 py-6 bg-slate-900 text-white rounded-[2rem] font-bold text-xl hover:bg-rose-600 transition-all duration-300 shadow-xl shadow-slate-200">
                        <?= __('End Call Attempt') ?>
                    </button>
                </div>
            </div>

            <div class="col-span-5 pt-10">
                <div class="bg-slate-900 rounded-[3.5rem] p-10 shadow-2xl shadow-indigo-900/10">
                    <h3 class="text-2xl font-bold text-white mb-8 flex items-center gap-4">
                        <i class="fas fa-history text-indigo-400"></i>
                        <?= __('Recent Activity') ?>
                    </h3>
                    <div id="callHistory_web" class="space-y-4 max-h-[600px] overflow-y-auto pr-4 custom-scrollbar text-slate-300">
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ================= GLOBAL JAVASCRIPT ENGINE ================= -->
<script>
const callId  = <?= $call_id ?>;
const astroId = <?= $astro_id ?>;

// Localization Strings for JS
const txtRingingTime = "<?= __('Ringing Time:') ?>";
const txtSec = "<?= __('sec') ?>";
const txtFailLoad = "<?= __('Failed to load history') ?>";

let checkInterval;
let ringTimer;
let ringTime = 60;

// 1. Manual Cancellation by User (NO ALERTS, INSTANT REDIRECT)
async function cancelCall() {
    clearInterval(checkInterval);
    clearInterval(ringTimer);
    
    if(document.getElementById('status_mob')) document.getElementById('status_mob').innerText = "Cancelling request...";
    if(document.getElementById('status_web')) document.getElementById('status_web').innerText = "Cancelling request...";
    
    try {
        // Await the TEXT response so the browser does NOT kill the request before it hits the DB
        const response = await fetch(`/user/cancel_call_request.php?call_id=${callId}`);
        await response.text(); 
    } catch (e) {
        console.error(e);
    }
    
    // Redirect instantly to index without any alert popups
    window.location.replace("/index.php");
}

// 2. Automated Timeout (60 Seconds)
ringTimer = setInterval(() => {
    ringTime--;
    const text = txtRingingTime + " " + ringTime + " " + txtSec;
    const simpleText = ringTime + " " + txtSec;
    
    if(document.getElementById("ringTimer_mob")) document.getElementById("ringTimer_mob").innerText = text;
    if(document.getElementById("ringTimer_web")) document.getElementById("ringTimer_web").innerText = simpleText;

    if (ringTime <= 0) {
        clearInterval(ringTimer);
        clearInterval(checkInterval);
        autoEndCall();
    }
}, 1000);

async function autoEndCall() {
    try {
        const response = await fetch(`/user/cancel_call_request.php?call_id=${callId}`);
        await response.text();
    } catch(e) {}
    
    // Redirect instantly to index without any alert popups
    window.location.replace("/index.php");
}

// 3. Status Polling Logic
async function checkStatus() {
    const url = `/user/check_call_status.php?call_id=${callId}&astro_id=${astroId}&_t=${Date.now()}`;
    try {
        const res = await fetch(url, { cache: 'no-store' });
        const data = await res.json();

        // Astrologer rejected or disconnected
        if (data.status === 'rejected' || data.status === 'user_rejected' || data.status === 'ended') {
            clearInterval(checkInterval);
            clearInterval(ringTimer);
            // NO ALERT HERE - Silent instant redirect to index
            window.location.replace("/index.php");
            return;
        }

        // Astrologer Accepted
        if (data.status === 'active') {
            clearInterval(checkInterval);
            clearInterval(ringTimer);
            window.location.replace(`/user/active_call.php?astro_id=${astroId}&call_id=${callId}`);
            return;
        }
    } catch (e) {}
}

// Poll every 2 seconds
checkInterval = setInterval(checkStatus, 2000);

// 4. Background History Sync
async function loadHistory() {
    try {
        const res = await fetch(`/user/call_history_ajax.php?uid=<?= $user_id ?>&t=${Date.now()}`);
        const html = await res.text();
        if(document.getElementById("callHistory_mob")) document.getElementById("callHistory_mob").innerHTML = html;
        if(document.getElementById("callHistory_web")) document.getElementById("callHistory_web").innerHTML = html;
    } catch (e) {
        const err = `<div style='padding:15px;text-align:center;color:red;'>${txtFailLoad}</div>`;
        if(document.getElementById("callHistory_mob")) document.getElementById("callHistory_mob").innerHTML = err;
        if(document.getElementById("callHistory_web")) document.getElementById("callHistory_web").innerHTML = err;
    }
}

loadHistory();
setInterval(loadHistory, 10000);
</script>

</body>
</html>