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
            'Consultation — Audio Call' => 'परामर्श — ऑडियो कॉल',
            'Live Call' => 'लाइव कॉल',
            'Astrologer' => 'ज्योतिषी',
            'Secure Audio Connection' => 'सुरक्षित ऑडियो कनेक्शन',
            'Initializing hardware...' => 'हार्डवेयर प्रारंभ हो रहा है...',
            'Normal' => 'सामान्य',
            'Loud' => 'तेज़',
            'Mute' => 'म्यूट',
            'Unmute' => 'अनम्यूट',
            'Connecting...' => 'जुड़ रहे हैं...',
            'Connected' => 'जुड़ा हुआ'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 2) {
    header("Location: /login.php");
    exit;
}

$astro_id = intval($_GET['astro_id'] ?? 0);
$call_id  = intval($_GET['call_id'] ?? 0);
if ($astro_id <= 0 || $call_id <= 0) die("Invalid call link.");

$stmt = $conn->prepare("SELECT name, price_per_minute FROM users WHERE id=? AND role_id=1");
$stmt->execute([$astro_id]);
$astro = $stmt->fetch(PDO::FETCH_ASSOC);
$price_per_min = floatval($astro['price_per_minute'] ?? 0);

$stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id=? AND role_id=2");
$stmt->execute([$_SESSION['userid']]);
$wallet_balance = floatval($stmt->fetchColumn());
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0"/>
    <title><?= __('Consultation — Audio Call') ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%); min-height: 100vh; font-family: sans-serif; transition: background 0.3s ease; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); transition: all 0.3s ease; }
        .control-btn { transition: all 0.2s ease; }
        .control-btn:active { transform: scale(0.9); }
        .status-pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }

        body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
        body.dark-theme .glass-card { background: rgba(31, 41, 55, 0.9) !important; border-color: #374151 !important; }
        body.dark-theme .text-gray-900, body.dark-theme .text-gray-800 { color: #ffffff !important; }
        body.dark-theme .text-gray-500, body.dark-theme .text-gray-400 { color: #d1d5db !important; }
    </style>
</head>
<body class="flex flex-col items-center p-4">

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

    <div class="w-full max-w-md text-center mt-10 mb-6">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider mb-2">
            <span class="w-2 h-2 rounded-full bg-blue-500 status-pulse"></span> <?= __('Live Call') ?>
        </div>
        <h2 id="timer" class="text-5xl font-mono font-bold text-gray-800 tracking-tight">00:00</h2>
    </div>

    <div class="w-full max-w-md glass-card rounded-[40px] shadow-2xl border border-white p-8 text-center mb-10">
        <div class="w-24 h-24 bg-gradient-to-tr from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center text-white text-4xl shadow-lg mx-auto mb-4">
            <i class="fas fa-user-tie"></i>
        </div>
        <h3 class="text-2xl font-black text-gray-900 mb-1"><?= htmlspecialchars($astro['name'] ?? __('Astrologer'), ENT_QUOTES, 'UTF-8') ?></h3>
        <p class="text-gray-500 font-medium mb-4"><?= __('Secure Audio Connection') ?></p>
    </div>

    <p id="statusTxt" class="text-gray-400 text-sm font-medium"><?= __('Initializing hardware...') ?></p>

    <input type="hidden" id="price_per_min" value="<?= $price_per_min ?>">
    <input type="hidden" id="wallet_balance" value="<?= $wallet_balance ?>">

    <div class="fixed bottom-10 left-0 right-0 px-6 max-w-md mx-auto">
        <div class="bg-gray-900/95 backdrop-blur-xl rounded-[40px] p-6 flex items-center justify-between shadow-2xl border border-white/10">
            <button id="speakerBtn" class="control-btn w-14 h-14 rounded-full bg-gray-800 flex flex-col items-center justify-center text-white gap-1 border border-gray-700">
                <i id="speakerIcon" class="fas fa-volume-low text-lg"></i>
                <span class="text-[9px] font-bold uppercase"><?= __('Normal') ?></span>
            </button>
            <button id="muteBtn" class="control-btn w-14 h-14 rounded-full bg-gray-800 flex flex-col items-center justify-center text-white gap-1 border border-gray-700 opacity-50 cursor-not-allowed">
                <i id="muteIcon" class="fas fa-microphone text-lg"></i>
                <span id="muteLabel" class="text-[9px] font-bold uppercase"><?= __('Mute') ?></span>
            </button>
            <button id="endBtn" class="control-btn w-24 h-14 rounded-[24px] bg-red-500 hover:bg-red-600 flex items-center justify-center text-white shadow-lg shadow-red-900/40">
                <i class="fas fa-phone-slash text-2xl"></i>
            </button>
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

<script>
(function(){
    const CALL_ID = <?= intval($call_id) ?>;
    const ASTRO_ID = <?= intval($astro_id) ?>;
    
    const SAVE_SIGNAL = "/signaling/save_signal.php";
    const GET_SIGNAL  = "/signaling/get_signals.php";
    const CHECK_ALIVE = "/check_call_alive.php";

    const txtConnecting = "<?= __('Connecting...') ?>";
    const txtConnected = "<?= __('Connected') ?>";
    const txtLoud = "<?= __('Loud') ?>";
    const txtNormal = "<?= __('Normal') ?>";
    const txtMute = "<?= __('Mute') ?>";
    const txtUnmute = "<?= __('Unmute') ?>";

    let pc=null, localStream=null, pending=[], answerHandled=false, lastId=0;
    let seconds=0, pollTimer=null, timerInt=null;
    let isLoud = false;
    window.isCallEnding = false;

    const pricePerMin = parseFloat(document.getElementById('price_per_min').value || '0');
    const wallet = parseFloat(document.getElementById('wallet_balance').value || '0');
    const statusTxt = document.getElementById("statusTxt");
    const muteBtn = document.getElementById("muteBtn");
    const speakerBtn = document.getElementById("speakerBtn");
    const remoteAudio = document.createElement("audio");
    remoteAudio.autoplay = true; remoteAudio.playsInline = true;

    let maxSeconds = (pricePerMin > 0) ? Math.floor(wallet / pricePerMin * 60) : 0;
    if (maxSeconds > 2) maxSeconds -= 2;

    async function saveSignal(type, payload){
        const f = new URLSearchParams();
        f.append("call_id", CALL_ID); f.append("sender", "user");
        f.append("type", type); f.append("payload", JSON.stringify(payload));
        return fetch(SAVE_SIGNAL, {method: "POST", body: f}); 
    }

    function showModalAndCleanUp() {
        endLocal();
        document.getElementById('ratingModal').classList.remove('hidden');
    }

    async function handleDisconnect() {
        if(window.isCallEnding) return;
        window.isCallEnding = true;
        statusTxt.innerText = "Call Disconnected...";
        showModalAndCleanUp();
    }

    async function pollSignals(){
        if(window.isCallEnding) return;
        try {
            let alive = await fetch(`${CHECK_ALIVE}?call_id=${CALL_ID}&_t=${Date.now()}`).then(r=>r.json()).catch(()=>({alive:true}));
            if (!alive.alive) { 
                handleDisconnect(); 
                return; 
            }

            let data = await fetch(`${GET_SIGNAL}?call_id=${CALL_ID}&since_id=${lastId}&_t=${Date.now()}`).then(r=>r.json()).catch(()=>({signals:[]}));

            if(data.signals){
                for(const s of data.signals){
                    if(s.sender==="user") continue;
                    lastId = Math.max(lastId, parseInt(s.id));
                    
                    if(s.type === "call_end"){ 
                        handleDisconnect(); 
                        return;
                    }

                    let payload = {};
                    try { payload = JSON.parse(s.payload); } catch(e) { continue; }

                    if(s.type==="answer" && !answerHandled){
                        answerHandled=true;
                        statusTxt.innerText = txtConnected;
                        await pc.setRemoteDescription(new RTCSessionDescription(payload));
                        for(const c of pending) pc.addIceCandidate(c);
                        pending=[];
                    }

                    if(s.type==="ice" || s.type==="candidate"){
                        const c = new RTCIceCandidate(payload);
                        if(!pc.remoteDescription) pending.push(c);
                        else pc.addIceCandidate(c);
                    }
                }
            }
        } catch(e) { console.error(e); }
        pollTimer = setTimeout(pollSignals, 1500);
    }

    async function startCall(){
        localStream = await navigator.mediaDevices.getUserMedia({audio:true}).catch(()=>null);
        if(!localStream) { alert("Mic required to make a call"); return; }
        
        statusTxt.innerText = txtConnecting;
        muteBtn.style.opacity="1"; muteBtn.style.pointerEvents="auto";
        muteBtn.classList.remove('cursor-not-allowed');

        if (typeof AndroidBridge !== "undefined") AndroidBridge.setSpeakerphoneOn(false); 

        pc = new RTCPeerConnection({
            iceServers: [{ urls: ['stun:turn.2ndcode.com:3478', 'turn:turn.2ndcode.com:3478'], username: 'webrtc2026', credential: 'nitya2026' }]
        });

        pc.onconnectionstatechange = () => {
            if (pc.connectionState === 'disconnected' || pc.connectionState === 'failed' || pc.connectionState === 'closed') handleDisconnect();
        };
        pc.oniceconnectionstatechange = () => {
            if (pc.iceConnectionState === 'disconnected' || pc.iceConnectionState === 'failed' || pc.iceConnectionState === 'closed') handleDisconnect();
        };

        localStream.getTracks().forEach(t=>pc.addTrack(t,localStream));
        pc.onicecandidate = e => { if(e.candidate) saveSignal("ice",e.candidate.toJSON()); };
        pc.ontrack = ev => { remoteAudio.srcObject = ev.streams[0]; };

        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);
        saveSignal("offer",offer);

        pollSignals();

        timerInt = setInterval(()=>{
            if(window.isCallEnding) return;
            seconds++;
            let m = Math.floor(seconds/60), s = seconds%60;
            document.getElementById("timer").innerText = `${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
            if (maxSeconds > 0 && seconds >= maxSeconds) endCall();
        }, 1000);
    }

    speakerBtn.onclick = () => {
        isLoud = !isLoud;
        const icon = document.getElementById("speakerIcon");
        const label = speakerBtn.querySelector('span');
        
        if (isLoud) {
            speakerBtn.classList.add('bg-blue-600', 'border-blue-400');
            icon.className = "fas fa-volume-up text-lg";
            label.innerText = txtLoud;
            if (typeof AndroidBridge !== "undefined") AndroidBridge.setSpeakerphoneOn(true);
        } else {
            speakerBtn.classList.remove('bg-blue-600', 'border-blue-400');
            icon.className = "fas fa-volume-low text-lg";
            label.innerText = txtNormal;
            if (typeof AndroidBridge !== "undefined") AndroidBridge.setSpeakerphoneOn(false);
        }
    };

    let isMuted = false;
    muteBtn.onclick = () => {
        isMuted = !isMuted;
        localStream?.getAudioTracks().forEach(t => t.enabled = !isMuted);
        document.getElementById("muteIcon").className = isMuted ? "fas fa-microphone-slash text-red-400" : "fas fa-microphone text-lg";
        document.getElementById("muteLabel").innerText = isMuted ? txtUnmute : txtMute;
        muteBtn.classList.toggle('bg-gray-700', isMuted);
    };

    async function endCall(){
        if(window.isCallEnding) return;
        window.isCallEnding = true;

        document.getElementById("endBtn").style.pointerEvents = "none";
        statusTxt.innerText = "Ending session...";

        const formData = new URLSearchParams();
        formData.append("call_id", CALL_ID);
        formData.append("sender", "user");
        formData.append("type", "call_end");
        formData.append("payload", '{"action":"end"}');

        try {
            await fetch(SAVE_SIGNAL, { method: "POST", body: formData });
        } catch(e) {}
        
        if (navigator.sendBeacon) navigator.sendBeacon(SAVE_SIGNAL, formData);

        showModalAndCleanUp();
    }

    function endLocal(){
        clearTimeout(pollTimer); clearInterval(timerInt);
        if(pc) {
            pc.onconnectionstatechange = null;
            pc.oniceconnectionstatechange = null;
            pc.close();
        }
        if(localStream) localStream.getTracks().forEach(t=>t.stop());
    }

    document.getElementById("endBtn").onclick = endCall;
    
    /* Rating Modal Logic */
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
        formData.append('call_id', CALL_ID); 
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

    // Initialize WebRTC
    startCall();
})();
</script>
</body>
</html>