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
            'Invalid astrologer profile' => 'अमान्य ज्योतिषी प्रोफ़ाइल',
            'Astrologer not found' => 'ज्योतिषी नहीं मिला',
            '/minute' => '/मिनट',
            'Call Now' => 'अभी कॉल करें',
            'Creating call...' => 'कॉल बना रहे हैं...'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// Ensure only logged-in users (role=2) can access this page
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 2) {
    header('Location: /login.php');
    exit;
}

$astro_id = intval($_GET['astro_id'] ?? 0);
if ($astro_id <= 0) {
    die("<p style='text-align:center;color:red;'>" . __('Invalid astrologer profile') . "</p>");
}

// Fetch astrologer details
$stmt = $conn->prepare("SELECT id, name, price_per_minute FROM users WHERE id=? AND role_id=1");
$stmt->execute([$astro_id]);
$astro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$astro) {
    die("<p style='text-align:center;color:red;'>" . __('Astrologer not found') . "</p>");
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($astro['name'], ENT_QUOTES, 'UTF-8') ?> - Fortune Parth</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
body {
  font-family: Arial, sans-serif;
  background: #fafafa;
  text-align: center;
  padding: 30px;
  color: #222;
  transition: background 0.3s ease;
}
h2 { font-size: 1.4rem; margin-bottom: 10px; }
p { font-size: 1rem; margin-bottom: 20px; }
button {
  background: #222; color: #fff; border: none;
  padding: 14px 20px; border-radius: 10px;
  font-size: 1rem; font-weight: 700;
  cursor: pointer; transition: background 0.3s;
}
button:hover { background: #444; }
.loader {
  border: 5px solid #eee; border-top: 5px solid #222;
  border-radius: 50%; width: 45px; height: 45px;
  animation: spin 1s linear infinite; margin: 20px auto;
}
@keyframes spin { 100% { transform: rotate(360deg); } }

/* =========================================
   ENHANCED DARK MODE STYLES
========================================= */
body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
body.dark-theme button { background: #374151 !important; color: #ffffff !important; border: 1px solid #4b5563 !important; }
body.dark-theme button:hover { background: #4b5563 !important; }
body.dark-theme .loader { border: 5px solid #374151; border-top: 5px solid #f9fafb; }
</style>
</head>
<body>

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<h2><?= htmlspecialchars($astro['name'], ENT_QUOTES, 'UTF-8') ?></h2>
<p>₹<?= htmlspecialchars($astro['price_per_minute'], ENT_QUOTES, 'UTF-8') ?><?= __('/minute') ?></p>
<button id="callBtn">📞 <?= __('Call Now') ?></button>
<div id="status"></div>

<script>
const astroId = <?= $astro['id'] ?>;
const txtCreating = "<?= __('Creating call...') ?>";

document.getElementById('callBtn').onclick = async () => {
  document.getElementById('status').innerHTML = `<div class='loader'></div><p>${txtCreating}</p>`;

  try {
    const formData = new FormData();
    formData.append('astro_id', astroId);

    const res = await fetch('/user/request_call.php', { method: 'POST', body: formData });
    const data = await res.json();

    console.log("Call request response:", data);

    if (data.status === 'success' && data.call_id) {
      // ✅ Proper redirect with both IDs
      window.location.href = `/user/call_waiting.php?astro_id=${astroId}&call_id=${data.call_id}`;
    } else {
      // ❌ Show backend error (like Unauthorized or Low Balance)
      document.getElementById('status').innerHTML = `<p style='color:red;'>${data.message}</p>`;
    }
  } catch (err) {
    document.getElementById('status').innerHTML = `<p style='color:red;'>Error: ${err}</p>`;
  }
};
</script>

</body>
</html>