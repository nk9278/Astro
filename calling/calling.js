/**
 * WebRTC Astrology Calling System
 * Backend: PHP Signaling & FCM
 * Media: Coturn TURN/STUN Server
 */

// 1. Get parameters from URL (e.g., ?call_id=500&role=user&token=DEVICE_TOKEN)
const params = new URLSearchParams(window.location.search);
const callId = params.get('call_id') || "123"; 
const userRole = params.get('role') || "user"; // 'user' or 'astrologer'
const deviceToken = params.get('token') || ""; // For FCM notification

// 2. WebRTC Configuration with your Coturn Server
const rtcConfig = {
    iceServers: [
        {
            urls: [
                'stun:turn.2ndcode.com:3478',
                'turn:turn.2ndcode.com:3478'
            ],
            username: 'webrtc2026',
            credential: 'nitya2026'
        }
    ],
    iceCandidatePoolSize: 10,
};

let peerConnection;
let localStream;
let lastSeenSignalId = 0;

const updateStatus = (text) => document.getElementById('statusText').innerText = text;

// 3. Initialize Camera and Microphone
async function startMedia() {
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        document.getElementById('localVideo').srcObject = localStream;
        updateStatus("Media initialized. Ready to call.");
    } catch (err) {
        console.error("Media Access Denied:", err);
        updateStatus("Error: Camera/Mic access required.");
    }
}

// 4. Create RTCPeerConnection
function setupPeerConnection() {
    peerConnection = new RTCPeerConnection(rtcConfig);

    // Add local tracks to the connection
    localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

    // Handle incoming remote stream
    peerConnection.ontrack = (event) => {
        document.getElementById('remoteVideo').srcObject = event.streams[0];
        updateStatus("Call Connected");
        document.getElementById('endCallBtn').style.display = "inline-block";
    };

    // Handle ICE candidates
    peerConnection.onicecandidate = (event) => {
        if (event.candidate) {
            pushSignal('candidate', event.candidate);
        }
    };
}

// 5. Send Signal to Database (save_signal.php)
async function pushSignal(type, payload) {
    const data = new URLSearchParams();
    data.append('call_id', callId);
    data.append('sender', userRole);
    data.append('type', type);
    data.append('payload', JSON.stringify(payload));

    await fetch('../signaling/save_signal.php', { method: 'POST', body: data });
}

// 6. Poll for new Signals (get_signals.php)
function startSignalPolling() {
    setInterval(async () => {
        const resp = await fetch(`../signaling/get_signals.php?call_id=${callId}&since_id=${lastSeenSignalId}`);
        const result = await resp.json();

        for (const signal of result.signals) {
            lastSeenSignalId = signal.id;
            if (signal.sender === userRole) continue; // Skip own signals

            const data = JSON.parse(signal.payload);

            if (signal.type === 'offer') await onOfferReceived(data);
            else if (signal.type === 'answer') await onAnswerReceived(data);
            else if (signal.type === 'candidate') await onCandidateReceived(data);
        }
    }, 3000);
}

// Signaling Handlers
async function onOfferReceived(offer) {
    updateStatus("Incoming call...");
    setupPeerConnection();
    await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
    const answer = await peerConnection.createAnswer();
    await peerConnection.setLocalDescription(answer);
    pushSignal('answer', answer);
}

async function onAnswerReceived(answer) {
    await peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
}

async function onCandidateReceived(candidate) {
    try {
        await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
    } catch (e) {
        console.warn("ICE Candidate failed:", e);
    }
}

// 7. Handle Call Trigger with FCM
document.getElementById('startCallBtn').addEventListener('click', async () => {
    document.getElementById('startCallBtn').style.display = "none";
    updateStatus("Calling...");
    
    setupPeerConnection();
    const offer = await peerConnection.createOffer();
    await peerConnection.setLocalDescription(offer);
    await pushSignal('offer', offer);

    // Trigger FCM via your PHP script
    if (deviceToken) {
        const fcmPayload = new URLSearchParams();
        fcmPayload.append('token', deviceToken);
        fcmPayload.append('call_id', callId);
        fcmPayload.append('user_email', 'User_Identity');
        // Construct accept URL for the astrologer
        const acceptUrl = window.location.href.split('?')[0] + `?call_id=${callId}&role=astrologer`;
        fcmPayload.append('accept_url', acceptUrl);

        fetch('../send_fcm_v1.php', { method: 'POST', body: fcmPayload });
    }
});

// Auto-start on load
startMedia().then(() => startSignalPolling());