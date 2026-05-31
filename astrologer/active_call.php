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
            'Ongoing Consultation' => 'चल रहा परामर्श',
            'Live Consultation' => 'लाइव परामर्श',
            'Client' => 'क्लाइंट',
            'Birth Date' => 'जन्म तिथि',
            'Birth Time' => 'जन्म समय',
            'Location' => 'स्थान',
            'Connecting to secure line...' => 'सुरक्षित लाइन से जुड़ रहे हैं...',
            'Encrypted Connection Active' => 'एन्क्रिप्टेड कनेक्शन सक्रिय',
            'Connecting...' => 'जुड़ रहे हैं...',
            'Normal' => 'सामान्य',
            'Loud' => 'तेज़',
            'Mute' => 'म्यूट',
            'Unmute' => 'अनम्यूट'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

$astro_id = $_SESSION['userid'] ?? 0;
$call_id  = intval($_GET['call_id'] ?? 0);

if ($call_id <= 0) die("Invalid call ID.");

$stmt = $conn->prepare("SELECT user_id FROM call_sessions WHERE id=?");
$stmt->execute([$call_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$user_id = $row['user_id'] ?? 0;

$stmt = $conn->prepare("SELECT name, gender, dob, tob, city FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0"/>
    <title><?= __('Ongoing Consultation') ?></title>
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
        body.dark-theme .bg-gray-50 { background-color: #374151 !important; }
        body.dark-theme .text-gray-900, body.dark-theme .text-gray-800 { color: #ffffff !important; }
        body.dark-theme .text-gray-500, body.dark-theme .text-gray-400 { color: #d1d5db !important; }
    </style>
</head>
<body class="flex flex-col items-center p-4">

    <div class="w-full max-w-md text-center mt-6 mb-4">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold uppercase tracking-wider mb-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 status-pulse"></span> <?= __('Live Consultation') ?>
        </div>
        <h2 id="timer" class="text-4xl font-mono font-bold text-gray-800">00:00</h2>
    </div>

    <div class="w-full max-w-md glass-card rounded-[32px] shadow-xl border border-white p-6 mb-8">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 bg-blue-500 rounded-2xl flex items-center justify-center text-white text-2xl shadow-lg">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($user['name'] ?? __('Client'), ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-gray-500 text-sm italic"><?= htmlspecialchars($user['gender'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="bg-gray-50 p-3 rounded-xl">
                <p class="text-gray-400 font-bold text-[10px] uppercase"><?= __('Birth Date') ?></p>
                <p class="text-gray-800 font-semibold"><?= htmlspecialchars($user['dob'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="bg-gray-50 p-3 rounded-xl">
                <p class="text-gray-400 font-bold text-[10px] uppercase"><?= __('Birth Time') ?></p>
                <p class="text-gray-800 font-semibold"><?= htmlspecialchars($user['tob'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <div class="bg-gray-50 p-3 rounded-xl col-span-2">
                <p class="text-gray-400 font-bold text-[10px] uppercase"><?= __('Location') ?></p>
                <p class="text-gray-800 font-semibold"><?= htmlspecialchars($user['city'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    </div>

    <p id="statusTxt" class="text-gray-400 text-sm font-medium"><?= __('Connecting to secure line...') ?></p>

    <div class="fixed bottom-10 left-0 right-0 px-6 max-w-md mx-auto">
        <div class="bg-gray-900/95 backdrop-blur-xl rounded-[40px] p-6 flex items-center justify-between shadow-2xl border border-white/10">
            <button id="speakerBtn" class="control-btn w-14 h-14 rounded-full bg-gray-800 flex flex-col items-center justify-center text-white gap-1 border border-gray-700">
                <i id="speakerIcon" class="fas fa-volume-low text-lg"></i>
                <span class="text-[9px] font-bold uppercase tracking-tighter"><?= __('Normal') ?></span>
            </button>
            <button id="muteBtn" class="control-btn w-14 h-14 rounded-full bg-gray-800 flex flex-col items-center justify-center text-white gap-1 border border-gray-700 opacity-50 cursor-not-allowed">
                <i id="muteIcon" class="fas fa-microphone text-lg"></i>
                <span id="muteLabel" class="text-[9px] font-bold uppercase tracking-tighter"><?= __('Mute') ?></span>
            </button>
            <button id="endBtn" class="control-btn w-24 h-14 rounded-[24px] bg-red-500 hover:bg-red-600 flex items-center justify-center text-white shadow-lg shadow-red-900/40">
                <i class="fas fa-phone-slash text-2xl"></i>
            </button>
        </div>
    </div>

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }

(function(){
    const CALL_ID = <?= $call_id ?>;
    const SAVE_SIGNAL = "/signaling/save_signal.php";
    const GET_SIGNAL  = "/signaling/get_signals.php";
    const CHECK_ALIVE = "/check_call_alive.php";

    const rtcConfig = {
        iceServers: [{ urls: ['stun:turn.2ndcode.com:3478', 'turn:turn.2ndcode.com:3478'], username: 'webrtc2026', credential: 'nitya2026' }],
        iceCandidatePoolSize: 10,
    };

    let pc=null, localStream=null, pending=[], offerHandled=false, lastId=0;
    let seconds=0, pollTimer=null, timerInt=null;
    window.isCallEnding = false;

    const statusTxt = document.getElementById("statusTxt");
    const muteBtn = document.getElementById("muteBtn");
    const speakerBtn = document.getElementById("speakerBtn");
    const endBtn = document.getElementById("endBtn");
    const remoteAudio = document.createElement("audio");
    remoteAudio.autoplay = true; remoteAudio.playsInline = true;

    const txtLoud = "<?= __('Loud') ?>";
    const txtNormal = "<?= __('Normal') ?>";
    const txtMute = "<?= __('Mute') ?>";
    const txtUnmute = "<?= __('Unmute') ?>";
    const txtEncrypted = "<?= __('Encrypted Connection Active') ?>";
    const txtConnecting = "<?= __('Connecting...') ?>";

    async function saveSignal(type, payload){
        const f=new URLSearchParams();
        f.append("call_id", CALL_ID); f.append("sender", "astro");
        f.append("type", type); f.append("payload", JSON.stringify(payload));
        await fetch(SAVE_SIGNAL, {method: "POST", body: f});
    }

    async function endCallBackend() {
        const f = new URLSearchParams();
        f.append("call_id", CALL_ID);
        f.append("duration", (seconds/60).toFixed(2));
        await fetch("/astrologer/end_call.php", {method: "POST", body: f}).catch(e => console.error(e));
    }

    // 🔥 THE FIX: Instant Mute and Drop BEFORE Billing Starts
    async function handleDisconnect() {
        if(window.isCallEnding) return;
        window.isCallEnding = true;
        
        statusTxt.innerText = "Call ended by Client. Processing billing...";
        
        // 1. Instantly stop audio, video, and timer.
        endLocal(); 

        // 2. Process billing in the background.
        await endCallBackend(); 
        
        // 3. Redirect after billing completes.
        window.location = "/astrologer/dashboard.php"; 
    }

    async function poll(){
        if(window.isCallEnding) return;
        try {
            // 🔥 THE FIX: Parallelize Polling Requests to cut latency by 50%
            const [aliveRes, signalRes] = await Promise.all([
                fetch(`${CHECK_ALIVE}?call_id=${CALL_ID}&_t=${Date.now()}`).then(r=>r.json()).catch(()=>({alive:true})),
                fetch(`${GET_SIGNAL}?call_id=${CALL_ID}&since_id=${lastId}&_t=${Date.now()}`).then(r=>r.json()).catch(()=>({signals:[]}))
            ]);

            if(!aliveRes.alive){ 
                handleDisconnect();
                return; 
            }

            if(signalRes.signals){
                for(const s of signalRes.signals){
                    if(s.sender==="astro") continue;
                    lastId = Math.max(lastId, parseInt(s.id));
                    
                    if(s.type === "call_end"){ 
                        handleDisconnect();
                        return;
                    }

                    let payload = {};
                    try { payload = JSON.parse(s.payload); } catch(e) { continue; }

                    if(s.type==="offer" && !offerHandled){
                        offerHandled = true;
                        statusTxt.innerText = txtConnecting;
                        await handleOffer(payload);
                    }
                    if((s.type==="candidate" || s.type==="ice") && pc){
                        const c = new RTCIceCandidate(payload);
                        if(!pc.remoteDescription) pending.push(c);
                        else pc.addIceCandidate(c);
                    }
                }
            }
        } catch(e) { console.error(e); }
        // Accelerated polling interval slightly
        pollTimer = setTimeout(poll, 1200);
    }

    async function handleOffer(offer){
        localStream = await navigator.mediaDevices.getUserMedia({audio:true}).catch(()=>null);
        if(localStream){
            muteBtn.style.opacity="1"; muteBtn.style.pointerEvents="auto";
            muteBtn.classList.remove('cursor-not-allowed');
            statusTxt.innerText = txtEncrypted;
        }

        pc = new RTCPeerConnection(rtcConfig);
        
        pc.onconnectionstatechange = () => {
            if (pc.connectionState === 'disconnected' || pc.connectionState === 'failed' || pc.connectionState === 'closed') handleDisconnect();
        };
        pc.oniceconnectionstatechange = () => {
            if (pc.iceConnectionState === 'disconnected' || pc.iceConnectionState === 'failed' || pc.iceConnectionState === 'closed') handleDisconnect();
        };

        if(localStream) localStream.getTracks().forEach(t => pc.addTrack(t, localStream));

        pc.onicecandidate = e => { if(e.candidate) saveSignal("candidate", e.candidate.toJSON()); };
        pc.ontrack = ev => { remoteAudio.srcObject = ev.streams[0]; };

        await pc.setRemoteDescription(new RTCSessionDescription(offer));
        for(const c of pending) pc.addIceCandidate(c);
        pending = [];

        const ans = await pc.createAnswer();
        await pc.setLocalDescription(ans);
        saveSignal("answer", ans);

        if(!timerInt) {
            timerInt = setInterval(() => {
                if(window.isCallEnding) return;
                seconds++;
                let m = Math.floor(seconds/60), s = seconds%60;
                document.getElementById('timer').innerText = `${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
            }, 1000);
        }
    }

    let isLoud = false;
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
        document.getElementById("muteIcon").className = isMuted ? "fas fa-microphone-slash text-lg text-red-400" : "fas fa-microphone text-lg";
        document.getElementById("muteLabel").innerText = isMuted ? txtUnmute : txtMute;
        muteBtn.classList.toggle('bg-gray-700', isMuted);
    };

    endBtn.onclick = async () => {
        if(window.isCallEnding) return;
        window.isCallEnding = true;
        endBtn.style.pointerEvents = "none";
        statusTxt.innerText = "Ending session. Processing billing...";

        // 1. Instantly cut local media
        endLocal();

        const formData = new URLSearchParams();
        formData.append("call_id", CALL_ID);
        formData.append("sender", "astro");
        formData.append("type", "call_end");
        formData.append("payload", '{"action":"end"}');

        fetch(SAVE_SIGNAL, { method: "POST", body: formData }).catch(()=>{});
        if (navigator.sendBeacon) navigator.sendBeacon(SAVE_SIGNAL, formData);

        // 2. Process billing in the background
        await endCallBackend();
        
        // 3. Redirect
        window.location="/astrologer/dashboard.php";
    };

    function endLocal(){
        clearTimeout(pollTimer); clearInterval(timerInt);
        if(pc) {
            pc.onconnectionstatechange = null;
            pc.oniceconnectionstatechange = null;
            pc.close();
        }
        if(localStream) localStream.getTracks().forEach(t => t.stop());
    }

    poll();
})();
</script>
</body>
</html>