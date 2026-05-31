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
            'Incoming Calls' => 'आने वाली कॉल',
            'No new incoming calls.' => 'कोई नई आने वाली कॉल नहीं है।',
            'From:' => 'से:',
            'Started:' => 'शुरू हुआ:',
            'Accept' => 'स्वीकार करें',
            'Reject' => 'अस्वीकार करें'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 1) {
    header('Location: /login.php');
    exit;
}

$astro_id = $_SESSION['userid'];

// Fetch all requested calls
$stmt = $conn->prepare("SELECT cs.id, u.email, cs.start_time 
                        FROM call_sessions cs 
                        JOIN users u ON cs.user_id=u.id 
                        WHERE cs.astrologer_id=? AND cs.status='requested' 
                        ORDER BY cs.start_time DESC");
$stmt->execute([$astro_id]);
$calls = $stmt->fetchAll(PDO::FETCH_ASSOC);

$callsExist = count($calls) > 0;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?= __('Incoming Calls') ?> - Fortune Parth</title>

<style>
body {
  font-family: Arial, sans-serif;
  padding: 20px;
  background: #fafafa;
  max-width: 480px;
  margin: auto;
  color: #222;
  transition: background 0.3s ease;
}
h2 { text-align: center; margin-bottom: 20px; }
.call-card {
  background: #fff;
  padding: 15px 20px;
  border-radius: 10px;
  margin-bottom: 15px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
.btn {
  display: inline-block;
  background: #222;
  color: #fff;
  padding: 10px 20px;
  margin-top: 10px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 700;
}
.btn:hover { background: #444; }

/* =========================================
   ENHANCED DARK MODE STYLES
========================================= */
body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
body.dark-theme .call-card { background: #1f2937 !important; border-color: #374151 !important; }
body.dark-theme .btn { background: #374151 !important; color: #ffffff !important; border: 1px solid #4b5563 !important; }
body.dark-theme .btn:hover { background: #4b5563 !important; }
body.dark-theme h2 { color: #ffffff !important; }
</style>
</head>
<body>

<h2><?= __('Incoming Calls') ?></h2>

<!-- Hidden ringtone -->
<audio id="incomingTone" src="/assets/audio/ringtone.mp3" loop preload="auto" style="display:none;"></audio>


<?php if (!$callsExist): ?>

<p style="text-align:center;"><?= __('No new incoming calls.') ?></p>

<script>
// Apply dark theme initially if set
if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-theme');
}

document.addEventListener("DOMContentLoaded", () => {
    const tone = document.getElementById("incomingTone");
    tone.pause();
    tone.currentTime = 0;
});

// ⭐ Refresh only if no calls
setInterval(() => {
    window.location.reload();
}, 3000);
</script>

<?php else: ?>

<?php foreach ($calls as $call): ?>
  <div class="call-card">
    <div><strong><?= __('From:') ?></strong> <?= htmlspecialchars($call['email'], ENT_QUOTES, 'UTF-8') ?></div>
    <div><strong><?= __('Started:') ?></strong> <?= htmlspecialchars($call['start_time'], ENT_QUOTES, 'UTF-8') ?></div>

    <a href="/astrologer/accept_call.php?call_id=<?= intval($call['id']) ?>" class="btn"><?= __('Accept') ?></a>
    <a href="/astrologer/reject_call.php?call_id=<?= intval($call['id']) ?>" class="btn" style="background:#b30000;"><?= __('Reject') ?></a>
  </div>
<?php endforeach; ?>

<script>
// Apply dark theme initially if set
if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-theme');
}

async function unlockAudio() {
    try {
        const s = new Audio();
        s.src = "data:audio/wav;base64,UklGRiQAAABXQVZFZm10IBAAAAABAAEA...";
        await s.play();
        s.pause();
    } catch(e) {}
}

let played = false;

async function startTone() {
    if (played) return;
    played = true;

    await unlockAudio();

    const tone = document.getElementById("incomingTone");
    tone.currentTime = 0;
    tone.play().catch(e => console.log("Blocked:", e));
}

document.addEventListener("DOMContentLoaded", startTone);

// ⭐ NO REFRESH WHEN CALL EXISTS
</script>

<?php endif; ?>

</body>
</html>