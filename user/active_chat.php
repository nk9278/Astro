<?php
// --- Hardened Security: Headers ---
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// --- Hardened Security: Secure Session Initialization ---
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
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
            'Live Chat' => 'लाइव चैट',
            'Type a message...' => 'एक संदेश टाइप करें...',
            'End Chat' => 'चैट समाप्त करें',
            'End Chat Session' => 'चैट सत्र समाप्त करें',
            'Are you sure you want to end this session?' => 'क्या आप वाकई इस सत्र को समाप्त करना चाहते हैं?',
            'Cancel' => 'रद्द करें'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 2) {
    header('Location: /login.php');
    exit;
}

// --- Status Resolution Logic ---
$is_active_chat = false;
$chat_data = null;
$wallet_balance = 0;
$price_per_minute = 0;
$astro_id = 0;

$session_id = filter_input(INPUT_GET, 'session_id', FILTER_VALIDATE_INT) ?: 0;

if ($session_id > 0) {
    // [HARDENED SECURITY] Selected astro_id from session to preserve secure review binding
    $stmt = $conn->prepare("SELECT cs.status, cs.astrologer_id, u.astrologer_name, u.price_per_minute FROM chat_sessions cs JOIN users u ON cs.astrologer_id = u.id WHERE cs.id = ? AND cs.user_id = ?");
    $stmt->execute([$session_id, $_SESSION['userid']]);
    $chat_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($chat_data && $chat_data['status'] === 'active') {
        $is_active_chat = true;
        $astro_id = (int)$chat_data['astrologer_id'];
    }
}

// Only pull wallet info if we are actively entering a chat
if ($is_active_chat) {
    $stmtWallet = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ? AND role_id = 2");
    $stmtWallet->execute([$_SESSION['userid']]);
    $wallet_balance = floatval($stmtWallet->fetchColumn());
    $price_per_minute = floatval($chat_data['price_per_minute']);
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $is_active_chat ? __('Live Chat') : 'No Active Chats - 2nd Code' ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        kbBase: { light: '#D1D5DB', dark: '#1e1e1e' },
                        kbKey: { light: '#FFFFFF', dark: '#3b3b3b' },
                        kbSpc: { light: '#9CA3AF', dark: '#2d2d2d' },
                        kbAct: { light: '#E5E7EB', dark: '#555555' }
                    }
                }
            }
        }
    </script>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Shared Global Styles */
        body { 
            font-family: 'Space Grotesk', sans-serif; 
            background: #f4f7f6; 
            margin: 0; 
        }

        body.dark-theme { background: #121212; }
        body.dark-theme .bg-white { background-color: #1f2937 !important; border-color: #374151 !important; }
        body.dark-theme .text-gray-900 { color: #f9fafb !important; }
        body.dark-theme .text-gray-500 { color: #9ca3af !important; }

        <?php if ($is_active_chat): ?>
        /* Active Chat Strict Layout Requirements */
        body {
            height: 100dvh; 
            display: flex; 
            flex-direction: column; 
            overflow: hidden; 
        }
        
        .top-header { flex-shrink: 0; }
        
        .chat-container { 
            flex: 1; 
            overflow-y: auto; 
            padding: 1rem; 
            -webkit-overflow-scrolling: touch; 
            scroll-behavior: smooth;
        }

        @keyframes slideUpFade {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .bubble { 
            max-width: 80%; 
            padding: 10px 14px; 
            border-radius: 18px; 
            margin-bottom: 8px; 
            font-size: 15px; 
            line-height: 1.4; 
            position: relative; 
            word-wrap: break-word;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            animation: slideUpFade 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) forwards;
        }
        
        .bubble.me { background: #222; color: #fff; margin-left: auto; border-bottom-right-radius: 4px; }
        .bubble.them { background: #fff; color: #222; border: 1px solid #eaeaea; border-bottom-left-radius: 4px; }
        .msg-time { font-size: 10px; opacity: 0.7; display: block; margin-top: 4px; text-align: right; }

        .input-area { 
            position: relative; 
            width: 100%;
            background: #fff; 
            padding: 10px; 
            border-top: 1px solid #eaeaea; 
            display: flex; 
            gap: 6px; 
            align-items: center; 
            z-index: 60;
            flex-shrink: 0; 
            transition: padding-bottom 0.1s ease-out, bottom 0.3s ease-out;
        }

        .key-btn {
            box-shadow: 0 1px 0 rgba(0,0,0,0.2);
            touch-action: manipulation;
            user-select: none;
            -webkit-user-select: none;
            -webkit-tap-highlight-color: transparent;
            transition: transform 0.05s, opacity 0.05s;
        }
        .key-row { display: flex; justify-content: center; gap: 6px; margin-bottom: 8px; }

        .modal-overlay { transition: opacity 0.3s ease; }
        .modal-content { transition: transform 0.3s ease, opacity 0.3s ease; }
        .hidden-modal { visibility: hidden; pointer-events: none; transition: visibility 0.3s; }
        .hidden-modal .modal-overlay { opacity: 0; pointer-events: none; }
        .hidden-modal .modal-content { transform: scale(0.95); opacity: 0; pointer-events: none; }

        @keyframes flashRed { 0%, 100% { color: #ef4444; } 50% { color: #991b1b; } }
        .timer-warning { animation: flashRed 1s infinite; font-weight: 800; }

        body.dark-theme .bubble.me { background: #10b981; color: #fff; }
        body.dark-theme .bubble.them { background: #374151; color: #f9fafb; border-color: #4b5563; }
        body.dark-theme .input-area { background: #1f2937; border-color: #374151; }
        body.dark-theme input { background: #374151; color: #fff; border-color: #4b5563; }
        body.dark-theme input:focus { border-color: #10b981; }
        body.dark-theme #sendBtn { background: #10b981; }

        <?php else: ?>
        /* Empty State Layout Requirements */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        <?php endif; ?>
    </style>
</head>
<body class="antialiased transition-colors duration-300">

<script>
    // System Theme Detection Handler
    if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.body.classList.add('dark-theme');
        document.documentElement.classList.add('dark');
    }
</script>

<?php if (!$is_active_chat): ?>
    <!-- ==========================================
         NO ACTIVE CHAT VIEW (EMPTY STATE)
    =========================================== -->
    
    <!-- User Mobile Header Included -->
    <?php include '../user/mobile_header.php'; ?>

    <!-- Empty State Content -->
    <main class="flex-1 flex flex-col items-center justify-center p-6 text-center">
        <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center text-gray-400 dark:text-gray-500 mb-6 shadow-inner border border-gray-200 dark:border-gray-700">
            <i class="fas fa-comment-slash text-4xl"></i>
        </div>
        
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-3">No Active Chats</h1>
        
        <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-8 leading-relaxed">
            It looks like you don't have any ongoing chat sessions right now. Browse our experts to start a new consultation.
        </p>
        
        <a href="/index.php" class="bg-black dark:bg-emerald-600 hover:bg-gray-800 dark:hover:bg-emerald-700 text-white font-semibold py-3 px-8 rounded-full shadow-lg transition transform hover:-translate-y-0.5 active:scale-95">
            Start a New Chat
        </a>
    </main>

    <!-- User Footer Included -->
    <?php include '../user/footer_user.php'; ?>

<?php else: ?>
    <!-- ==========================================
         ACTIVE CHAT VIEW
    =========================================== -->
    
    <div class="top-header bg-white shadow-sm px-4 py-3 flex justify-between items-center z-[70] border-b border-gray-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/40 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0 shadow-sm">
                <i class="fas fa-user-astronaut"></i>
            </div>
            <div class="overflow-hidden">
                <h2 class="font-bold text-gray-900 text-lg leading-tight truncate"><?= htmlspecialchars($chat_data['astrologer_name'], ENT_QUOTES, 'UTF-8') ?></h2>
                <div class="flex items-center gap-2 text-xs font-semibold mt-0.5">
                    <span class="flex items-center gap-1 text-emerald-500">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> <?= __('Live Chat') ?>
                    </span>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <span id="walletTimer" class="text-gray-500 dark:text-gray-400 flex items-center gap-1">
                        <i class="fas fa-clock"></i> <span id="timeLeftStr">--:--</span>
                    </span>
                </div>
            </div>
        </div>
        <button onclick="openEndModal()" class="text-red-500 bg-red-50 dark:bg-red-900/30 px-3 py-1.5 rounded-lg text-sm font-bold border border-red-100 dark:border-red-800 relative z-20 shrink-0 hover:bg-red-100 dark:hover:bg-red-900/50 transition">
            <?= __('End Chat') ?>
        </button>
    </div>

    <div id="chatBox" class="chat-container flex flex-col pb-[10px]">
    </div>

    <div id="inputAreaWrapper" class="input-area shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] bg-white dark:bg-gray-800 absolute bottom-0">
        
        <button id="keyboardToggleBtn" class="w-9 h-9 text-gray-500 dark:text-gray-400 rounded-full flex items-center justify-center shrink-0 active:bg-gray-100 dark:active:bg-gray-700 transition">
            <i id="kbToggleIcon" class="fas fa-chevron-down text-lg transition-transform duration-200"></i>
        </button>

        <input type="file" id="galleryInput" accept="image/*" class="hidden">
        <button id="galleryBtn" class="w-9 h-9 text-gray-500 dark:text-gray-400 rounded-full flex items-center justify-center shrink-0 active:bg-gray-100 dark:active:bg-gray-700 transition" title="Choose Photo">
            <i class="fas fa-image text-[17px]"></i>
        </button>

        <input type="text" id="msgInput" inputmode="none" class="flex-1 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-full px-3 py-2.5 text-[14px] focus:outline-none text-gray-900 dark:text-white cursor-text shadow-inner transition-colors font-medium caret-black dark:caret-white min-w-0" placeholder="<?= __('Type a message...') ?>">

        <button id="sendBtn" class="w-10 h-10 flex-shrink-0 bg-black dark:bg-emerald-600 text-white rounded-full flex items-center justify-center shadow-md active:scale-90 transition transform shrink-0">
            <i class="fas fa-paper-plane text-sm -ml-1"></i>
        </button>
    </div>

    <div id="customKeyboard" class="fixed bottom-0 left-0 w-full bg-kbBase-light dark:bg-kbBase-dark p-2 z-[50] transform translate-y-full transition-transform duration-300 ease-out pb-6">
        <div id="keyboardRows" class="flex flex-col max-w-lg mx-auto select-none">
        </div>
    </div>

    <div id="endChatModal" class="fixed inset-0 z-[100] flex items-center justify-center hidden-modal">
        <div class="modal-overlay absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEndModal()"></div>
        <div class="modal-content modal-box relative w-11/12 max-w-sm bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-2xl text-center border dark:border-gray-700">
            <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 dark:bg-red-900/30 dark:text-red-400">
                <i class="fas fa-exclamation-triangle text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2 dark:text-white"><?= __('End Chat Session') ?></h3>
            <p class="text-gray-500 text-sm mb-6 dark:text-gray-400"><?= __('Are you sure you want to end this session?') ?></p>
            <div class="flex gap-3">
                <button onclick="closeEndModal()" class="flex-1 py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"><?= __('Cancel') ?></button>
                <button onclick="confirmEndChat()" class="flex-1 py-3 bg-red-500 text-white font-bold rounded-xl hover:bg-red-600 transition shadow-lg shadow-red-500/30"><?= __('End Chat') ?></button>
            </div>
        </div>
    </div>

    <div id="ratingModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden z-[9999] flex items-center justify-center p-4 transition-all">
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-sm w-full shadow-2xl text-center transform scale-100">
            <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-2">Rate Your Session</h2>
            <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">How was your consultation?</p>

            <div class="flex justify-center gap-2 mb-6" id="starContainer">
                <i class="fas fa-star text-3xl text-gray-300 cursor-pointer hover:text-yellow-400 transition" data-val="1"></i>
                <i class="fas fa-star text-3xl text-gray-300 cursor-pointer hover:text-yellow-400 transition" data-val="2"></i>
                <i class="fas fa-star text-3xl text-gray-300 cursor-pointer hover:text-yellow-400 transition" data-val="3"></i>
                <i class="fas fa-star text-3xl text-gray-300 cursor-pointer hover:text-yellow-400 transition" data-val="4"></i>
                <i class="fas fa-star text-3xl text-gray-300 cursor-pointer hover:text-yellow-400 transition" data-val="5"></i>
            </div>
            <input type="hidden" id="selectedRating" value="0">
            
            <textarea id="reviewText" rows="3" placeholder="Write a short review (optional)..." 
                      class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-600 focus:border-black outline-none mb-6 resize-none"></textarea>

            <button id="submitReviewBtn" class="w-full py-4 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-lg transition-all mb-3">
                Submit Review
            </button>
            <button onclick="window.location.href='/index.php'" class="text-gray-400 dark:text-gray-500 text-sm font-bold hover:text-gray-700 dark:hover:text-white transition">
                Skip & Go Home
            </button>
        </div>
    </div>

    <div id="imageZoomModal" class="fixed inset-0 z-[150] flex items-center justify-center hidden-modal">
        <div class="modal-overlay absolute inset-0 bg-black/90 backdrop-blur-md cursor-pointer" onclick="closeImageZoom()"></div>
        <button onclick="closeImageZoom()" class="absolute top-4 right-4 text-white/70 hover:text-white text-3xl z-[160] transition-colors p-4">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-content relative z-[155] max-w-[95vw] max-h-[90vh] flex items-center justify-center pointer-events-none">
            <img id="zoomedImage" src="" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl pointer-events-auto" alt="Zoomed View">
        </div>
    </div>

    <script>
    // Variables exposed to global scope since IIFE is removed for inline HTML events to function
    const sessionId = <?= intval($session_id) ?>;
    const ASTRO_ID = <?= intval($astro_id) ?>;
    const chatBox = document.getElementById('chatBox');
    const msgInput = document.getElementById('msgInput');
    const inputAreaWrapper = document.getElementById('inputAreaWrapper');
    const customKeyboard = document.getElementById('customKeyboard');
    const keyboardRows = document.getElementById('keyboardRows');
    const keyboardToggleBtn = document.getElementById('keyboardToggleBtn');
    const kbToggleIcon = document.getElementById('kbToggleIcon');
    const endModal = document.getElementById('endChatModal');
    const imageZoomModal = document.getElementById('imageZoomModal');
    const zoomedImage = document.getElementById('zoomedImage');

    let lastMsgId = 0;
    let checkInterval;
    let isEnding = false;
    let isUserScrolling = false;
    let isSending = false;
    let sessionEndedCleanly = false;

    // Custom User History Mapping
    function setupNavigationLock() {
        history.pushState({ activeChat: true }, null, window.location.href);
        window.addEventListener('popstate', handleHardwareBack);
    }

    function handleHardwareBack(event) {
        if (!sessionEndedCleanly) {
            history.pushState({ activeChat: true }, null, window.location.href);
            openEndModal();
        }
    }

    // Image Zoom Functions (Globally Accessible for inline onclick)
    function openImageZoom(src) {
        zoomedImage.src = src;
        imageZoomModal.classList.remove('hidden-modal');
    }
    
    function closeImageZoom() {
        imageZoomModal.classList.add('hidden-modal');
        setTimeout(() => { zoomedImage.src = ''; }, 300);
    }

    // UI State Lock
    function setInterfaceLock(locked) {
        isSending = locked;
        document.getElementById('sendBtn').style.opacity = locked ? '0.5' : '1';
        document.getElementById('sendBtn').style.pointerEvents = locked ? 'none' : 'auto';
        document.getElementById('galleryBtn').style.pointerEvents = locked ? 'none' : 'auto';
        keyboardToggleBtn.style.pointerEvents = locked ? 'none' : 'auto';
    }

    // Scroll Logic
    chatBox.addEventListener('scroll', () => {
        const isAtBottom = chatBox.scrollHeight - chatBox.scrollTop <= chatBox.clientHeight + 50;
        isUserScrolling = !isAtBottom;
    });

    function snapToBottom() {
        if (!isUserScrolling && chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }

    // Transliteration
    let isHindiMode = false;
    async function transliterateWord(word) {
        if (!word || !/^[a-zA-Z]+$/.test(word)) return word; 
        try {
            const res = await fetch(`https://inputtools.google.com/request?text=${word}&itc=hi-t-i0-und&num=1&cp=0&cs=1&ie=utf-8&oe=utf-8&app=chat`);
            const data = await res.json();
            if (data[0] === 'SUCCESS') return data[1][0][1][0]; 
        } catch(e) {}
        return word; 
    }

    async function processHindiInput() {
        let parts = msgInput.value.split(/(\s+)/);
        let lastWord = parts[parts.length - 1];
        if (lastWord.trim().length > 0) {
            let hindiWord = await transliterateWord(lastWord);
            parts[parts.length - 1] = hindiWord;
            msgInput.value = parts.join('');
        }
    }

    // Keyboard Logic
    let currentLang = 'en';
    let isShift = false;

    const layouts = {
        en: {
            base: [['q','w','e','r','t','y','u','i','o','p'],['a','s','d','f','g','h','j','k','l'],['{shift}','z','x','c','v','b','n','m','{bksp}']],
            shift: [['Q','W','E','R','T','Y','U','I','O','P'],['A','S','D','F','G','H','J','K','L'],['{shift}','Z','X','C','V','B','N','M','{bksp}']]
        },
        hi: {
            base: [['ौ','ै','ा','ी','ू','ब','ह','ग','द','ज'],['ो','े','्','ि','ु','प','र','क','त','च'],['{shift}','ॉ','ं','म','न','व','ल','स','य','{bksp}']],
            shift: [['औ','ऐ','आ','ई','ऊ','भ','ङ','घ','ध','झ'],['ओ','ए','अ','इ','उ','फ','ृ','ख','थ','छ'],['{shift}','ऑ','ँ','ळ','ण','श','ष','क्ष','ज्ञ','{bksp}']]
        }
    };

    function renderKeyboard() {
        const layoutMap = layouts[currentLang][isShift ? 'shift' : 'base'];
        let html = '';
        layoutMap.forEach((row, rowIndex) => {
            let paddingClass = rowIndex === 1 ? 'px-4' : (rowIndex === 2 ? 'px-1' : '');
            html += `<div class="key-row ${paddingClass}">`;
            row.forEach(key => {
                if (key === '{shift}') {
                    const activeColor = isShift ? 'bg-gray-400 dark:bg-gray-600 text-white' : 'bg-kbSpc-light dark:bg-kbSpc-dark text-gray-800 dark:text-white';
                    html += `<button data-key="shift" class="key-btn flex-[1.5] ${activeColor} py-3.5 rounded-lg text-sm font-semibold"><i class="fas fa-arrow-up"></i></button>`;
                } else if (key === '{bksp}') {
                    html += `<button data-key="bksp" class="key-btn flex-[1.5] bg-kbSpc-light dark:bg-kbSpc-dark text-gray-800 dark:text-white py-3.5 rounded-lg text-sm font-semibold"><i class="fas fa-delete-left"></i></button>`;
                } else {
                    html += `<button data-char="${key}" class="key-btn flex-1 bg-kbKey-light dark:bg-kbKey-dark text-gray-800 dark:text-white py-3.5 rounded-lg text-[15px] font-semibold">${key}</button>`;
                }
            });
            html += `</div>`;
        });
        html += `<div class="key-row">
            <button data-key="lang" class="key-btn flex-[2] bg-kbSpc-light dark:bg-kbSpc-dark text-gray-800 dark:text-white py-3.5 rounded-lg text-sm font-bold tracking-wider">${currentLang.toUpperCase()}</button>
            <button data-key="close" class="key-btn flex-[1] bg-kbSpc-light dark:bg-kbSpc-dark text-gray-800 dark:text-white py-3.5 rounded-lg text-sm font-semibold"><i class="fas fa-chevron-down"></i></button>
            <button data-key="space" class="key-btn flex-[5] bg-kbKey-light dark:bg-kbKey-dark text-gray-800 dark:text-white py-3.5 rounded-lg text-sm font-semibold">space</button>
            <button data-key="enter" class="key-btn flex-[2] bg-blue-500 text-white py-3.5 rounded-lg text-sm font-semibold shadow-blue-500/50">Enter</button>
        </div>`;
        keyboardRows.innerHTML = html;
    }
    renderKeyboard();

    function insertTextAtCursor(text) {
        const start = msgInput.selectionStart;
        const end = msgInput.selectionEnd;
        const currentVal = msgInput.value;
        msgInput.value = currentVal.substring(0, start) + text + currentVal.substring(end);
        msgInput.setSelectionRange(start + text.length, start + text.length);
        msgInput.focus();
    }

    function deleteTextAtCursor() {
        const start = msgInput.selectionStart;
        const end = msgInput.selectionEnd;
        if (start === end && start > 0) {
            msgInput.value = msgInput.value.substring(0, start - 1) + msgInput.value.substring(end);
            msgInput.setSelectionRange(start - 1, start - 1);
        } else if (start !== end) {
            msgInput.value = msgInput.value.substring(0, start) + msgInput.value.substring(end);
            msgInput.setSelectionRange(start, start);
        }
        msgInput.focus();
    }

    keyboardRows.addEventListener('touchstart', async (e) => {
        const btn = e.target.closest('.key-btn');
        if (!btn || isSending) return;
        e.preventDefault(); 
        btn.classList.add('scale-95', 'opacity-80');

        if (btn.hasAttribute('data-char')) {
            insertTextAtCursor(btn.getAttribute('data-char'));
            if (isShift && currentLang === 'en') { isShift = false; renderKeyboard(); }
        } else {
            const keyAction = btn.getAttribute('data-key');
            switch (keyAction) {
                case 'shift': isShift = !isShift; renderKeyboard(); break;
                case 'bksp': deleteTextAtCursor(); break;
                case 'lang': 
                    currentLang = currentLang === 'en' ? 'hi' : 'en'; 
                    isHindiMode = currentLang === 'hi'; 
                    isShift = false; 
                    renderKeyboard(); 
                    break;
                case 'space': 
                    if (isHindiMode) await processHindiInput();
                    insertTextAtCursor(' '); 
                    break;
                case 'close': closeKeyboard(); break;
                case 'enter': 
                    if (isHindiMode) await processHindiInput();
                    sendMessage(); 
                    closeKeyboard(); 
                    break;
            }
        }
    }, { passive: false });

    keyboardRows.addEventListener('touchend', (e) => {
        const btn = e.target.closest('.key-btn');
        if (btn) btn.classList.remove('scale-95', 'opacity-80');
    });

    function openKeyboard() {
        msgInput.focus();
        customKeyboard.classList.remove('translate-y-full');
        kbToggleIcon.className = 'fas fa-chevron-down text-lg'; 
        const kbHeight = customKeyboard.offsetHeight;
        inputAreaWrapper.style.bottom = `${kbHeight}px`;
        chatBox.style.paddingBottom = `${kbHeight + inputAreaWrapper.offsetHeight + 10}px`;
        setTimeout(snapToBottom, 150);
    }

    function closeKeyboard() {
        msgInput.blur();
        customKeyboard.classList.add('translate-y-full');
        kbToggleIcon.className = 'fas fa-keyboard text-lg'; 
        inputAreaWrapper.style.bottom = '0px';
        chatBox.style.paddingBottom = '10px';
    }

    msgInput.addEventListener('pointerup', (e) => { e.preventDefault(); openKeyboard(); });
    keyboardToggleBtn.addEventListener('click', () => { customKeyboard.classList.contains('translate-y-full') ? openKeyboard() : closeKeyboard(); });

    // Media Attachment (Gallery Only)
    const galleryInput = document.getElementById('galleryInput');
    document.getElementById('galleryBtn').addEventListener('click', () => galleryInput.click());

    function handleImageSelection(e) {
        if (isSending) {
            e.target.value = '';
            return; 
        }
        
        const file = e.target.files[0];
        if (!file) return;
        
        setInterfaceLock(true); 

        const reader = new FileReader();
        reader.onload = function(event) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                const MAX_WIDTH = 600;
                let width = img.width;
                let height = img.height;

                if (width > MAX_WIDTH) {
                    height = Math.round((height * MAX_WIDTH) / width);
                    width = MAX_WIDTH;
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                const base64String = canvas.toDataURL('image/jpeg', 0.6);
                executeSend('[IMAGE]' + base64String); 
            }
            img.src = event.target.result;
        }
        reader.readAsDataURL(file);
        e.target.value = ''; 
    }

    galleryInput.addEventListener('change', handleImageSelection);

    // Network Sender
    async function executeSend(dataString) {
        const fd = new FormData();
        fd.append('action', 'send');
        fd.append('session_id', sessionId);
        fd.append('message', dataString);

        try {
            await fetch('/api/chat_handler.php', { method: 'POST', body: fd });
            isUserScrolling = false;
            fetchMessages();
            snapToBottom();
        } catch (e) { 
            alert("Failed to send. Check connection.");
        } finally {
            setInterfaceLock(false); 
        }
    }

    async function sendMessage() {
        const text = msgInput.value.trim();
        if (!text || isSending || isEnding) return;
        
        setInterfaceLock(true); 
        
        msgInput.value = ''; 
        msgInput.focus();
        isUserScrolling = false; 
        snapToBottom();
        
        executeSend(text);
    }

    document.getElementById('sendBtn').onclick = (e) => {
        e.preventDefault();
        sendMessage();
    };

    // Fetch Routine
    async function fetchMessages() {
        if (isEnding) return;
        try {
            const res = await fetch(`/api/chat_handler.php?action=fetch&session_id=${sessionId}&last_id=${lastMsgId}&_t=${Date.now()}`, { cache: 'no-store' });
            const data = await res.json();
            
            if (data.status === 'success' && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    const isMe = msg.sender_type === 'user';
                    const bubble = document.createElement('div');
                    bubble.className = `bubble ${isMe ? 'me' : 'them'}`;
                    
                    let safeMessage = '';
                    if (msg.message.startsWith('[IMAGE]data:image')) {
                        const imgSrc = msg.message.replace('[IMAGE]', '');
                        safeMessage = `<img src="${imgSrc}" onclick="openImageZoom(this.src)" class="cursor-pointer rounded-lg w-full mt-1 mb-1 object-cover max-h-64 shadow-sm hover:opacity-90 transition-opacity" alt="Attached Image">`;
                    } else {
                        const tempDiv = document.createElement('div');
                        tempDiv.textContent = msg.message;
                        safeMessage = tempDiv.innerHTML;
                    }
                    
                    bubble.innerHTML = `${safeMessage} <span class="msg-time">${msg.time}</span>`;
                    chatBox.appendChild(bubble);
                    lastMsgId = Math.max(lastMsgId, msg.id);
                });
                snapToBottom();
            } else if (data.status === 'error' && data.message === "Chat session inactive or denied.") {
                autoCutChat();
            }
        } catch (e) { console.error("Sync Error:", e); }
    }

    // Timer Logic
    const pricePerMin = <?= $price_per_minute ?>;
    const walletBalance = <?= $wallet_balance ?>;
    let maxSeconds = (pricePerMin > 0) ? Math.floor((walletBalance / pricePerMin) * 60) : 0;
    let secondsElapsed = 0;
    const timerDisplay = document.getElementById('timeLeftStr');
    const walletTimerDiv = document.getElementById('walletTimer');

    const sessionTimer = setInterval(() => {
        if (isEnding) return;
        secondsElapsed++;
        let secondsLeft = maxSeconds - secondsElapsed;
        
        if (secondsLeft <= 0) {
            clearInterval(sessionTimer);
            timerDisplay.innerText = "00:00";
            autoCutChat(); 
            return;
        }

        let m = Math.floor(secondsLeft / 60);
        let s = secondsLeft % 60;
        timerDisplay.innerText = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;

        if (secondsLeft <= 60 && !walletTimerDiv.classList.contains('timer-warning')) {
            walletTimerDiv.classList.add('timer-warning');
            walletTimerDiv.classList.remove('text-gray-500', 'dark:text-gray-400');
        }
    }, 1000);

    // End Chat Handler
    async function autoCutChat() {
        if (isEnding && sessionEndedCleanly) return;
        isEnding = true;
        sessionEndedCleanly = true;
        
        window.removeEventListener('popstate', handleHardwareBack);
        clearInterval(checkInterval);
        clearInterval(sessionTimer);
        closeKeyboard();
        
        msgInput.value = '';
        if(document.getElementById('sendBtn')) document.getElementById('sendBtn').disabled = true;

        try {
            await fetch(`/user/end_chat.php?session_id=${sessionId}&ajax=1`, { cache: 'no-store' });
        } catch(e) {}

        document.getElementById('ratingModal').classList.remove('hidden');
    }

    // Modal Control Functions (Globally Accessible)
    function openEndModal() { endModal.classList.remove('hidden-modal'); }
    function closeEndModal() { endModal.classList.add('hidden-modal'); }
    function confirmEndChat() { closeEndModal(); autoCutChat(); }

    /* Star Rating UX Triggers */
    const stars = document.querySelectorAll('#starContainer i');
    const ratingInput = document.getElementById('selectedRating');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            let val = this.getAttribute('data-val');
            ratingInput.value = val;
            stars.forEach(s => {
                if (s.getAttribute('data-val') <= val) {
                    s.classList.remove('text-gray-300');
                    s.classList.add('text-yellow-400');
                } else {
                    s.classList.remove('text-yellow-400');
                    s.classList.add('text-gray-300');
                }
            });
        });
    });

    document.getElementById("submitReviewBtn").onclick = async function() {
        const rating = ratingInput.value;
        const text = document.getElementById('reviewText').value;
        
        if (rating == 0) {
            alert('Please select a star rating before submitting.');
            return;
        }

        const btn = document.getElementById('submitReviewBtn');
        btn.innerText = "Submitting...";
        btn.disabled = true;

        const formData = new FormData();
        formData.append('chat_session_id', sessionId); 
        formData.append('astro_id', ASTRO_ID);
        formData.append('rating', rating);
        formData.append('review', text);

        try {
            const response = await fetch('/user/submit_review.php', { method: 'POST', body: formData });
            const result = await response.text();
            
            if(result.trim() === "SUCCESS") {
                document.getElementById('ratingModal').innerHTML = `
                    <div class="p-4">
                        <i class="fas fa-check-circle text-5xl text-emerald-500 mb-4"></i>
                        <h2 class="text-2xl font-black text-emerald-500 mb-2">Thank You!</h2>
                        <p class="text-gray-500 dark:text-gray-400">Your feedback has been successfully recorded.</p>
                        <button onclick="window.location.href='/index.php'" class="mt-8 w-full py-4 bg-gray-900 text-white font-bold rounded-xl shadow-lg">Back to Home</button>
                    </div>`;
            } else {
                alert("Error: " + result);
                btn.innerText = "Submit Review";
                btn.disabled = false;
            }
        } catch (e) {
            alert("Network Error. Please try again.");
            btn.innerText = "Submit Review";
            btn.disabled = false;
        }
    };

    setupNavigationLock();
    checkInterval = setInterval(fetchMessages, 2000);
    fetchMessages();
    </script>
<?php endif; ?>

</body>
</html>