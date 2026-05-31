<?php
require __DIR__ . '/../db.php';
session_start();

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'Online' => 'ऑनलाइन',
            'Offline' => 'ऑफ़लाइन',
            'yrs' => 'वर्ष',
            '/min' => '/मिनट',
            'Call' => 'कॉल'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

$isLoggedIn = isset($_SESSION['userid']);

// Fetch online astrologers
$stmt_online = $conn->prepare("
    SELECT id, astrologer_name, experience_years, profile_photo, online_status,
           profile_rating, rating_count, wait_time, price_per_minute,
           base_price_per_minute, skills, languages
    FROM users
    WHERE role_id = 1 AND online_status = 1
");
$stmt_online->execute();
$online_astrologers = $stmt_online->fetchAll(PDO::FETCH_ASSOC);

// Fetch offline astrologers
$stmt_offline = $conn->prepare("
    SELECT id, astrologer_name, experience_years, profile_photo, online_status,
           profile_rating, rating_count, wait_time, price_per_minute,
           base_price_per_minute, skills, languages
    FROM users
    WHERE role_id = 1 AND online_status = 0
");
$stmt_offline->execute();
$offline_astrologers = $stmt_offline->fetchAll(PDO::FETCH_ASSOC);

shuffle($online_astrologers);
shuffle($offline_astrologers);
$astrologers = array_merge($online_astrologers, $offline_astrologers);
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
/* ---------- 92% WIDTH - SAME AS LIVE ASTRO DESIGN ---------- */
body { transition: background 0.3s ease; margin: 0; font-family: Arial, sans-serif; background: #fafafa; }

.astro-card {
  width: 92%;
  margin: 0 auto 14px auto;
  display: flex;
  background: #fff;
  border: 1px solid #e6e6e6;
  border-radius: 16px;
  padding: 12px;
  position: relative;
  transition: all 0.3s ease;
}

/* IMAGE SAME AS LIVE ASTRO BLOCK */
.astro-left {
  width: 90px;
  min-width: 90px;
}

.astro-left img {
  width: 90px;
  height: 110px;
  border-radius: 14px;
  object-fit: cover;
}

/* STAR RATING SAME */
.astro-rating {
  position: absolute;
  left: 16px;
  bottom: 12px;
  background: #222;
  color: #fff;
  padding: 2px 8px;
  font-size: 10px;
  border-radius: 8px;
}

/* DETAILS SAME ALIGNMENT */
.astro-details {
  flex: 1;
  padding-left: 12px;
}

.astro-name {
  font-size: 16px;
  font-weight: 700;
  margin-bottom: 2px;
  color: #222;
}

.astro-skills,
.astro-exp,
.astro-lang {
  font-size: 12px;
  margin-top: 2px;
  color: #555;
}

/* PRICE SAME AS LIVE BLOCK */
.price-old {
  font-size: 11px;
  text-decoration: line-through;
  color: #d03535;
}

.price-new {
  font-size: 15px;
  font-weight: 700;
  color: #0c9c45;
}

/* STATUS BADGE SAME */
.astro-status {
  position: absolute;
  right: 12px;
  top: 10px;
  font-size: 11px;
  padding: 3px 8px;
  border-radius: 7px;
  background: #ffe7c7;
  font-weight: 600;
  color: #d97706;
}

.astro-status.offline {
  background: #e8e8e8;
  color: #777;
}

/* CALL BUTTON — EXACT SAME SIZE AS LIVE ASTRO */
.call-btn {
  position: absolute;
  right: 12px;
  bottom: 12px;
  background: #000;
  color: #fff;
  padding: 6px 18px;
  font-size: 13px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
}

/* OFFLINE BUTTON SAME */
.call-btn.offline {
  background: #d9d9d9 !important;
  color: #777 !important;
  padding: 3px 10px !important;
  font-size: 11px !important;
  height: 28px !important;
  border-radius: 6px !important;
  min-width: 60px !important;
  cursor: not-allowed;
}

/* =========================================
   ENHANCED DARK MODE STYLES
========================================= */
body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
body.dark-theme .astro-card { background: #1f2937 !important; border-color: #374151 !important; }
body.dark-theme .astro-name { color: #ffffff !important; }
body.dark-theme .astro-skills, 
body.dark-theme .astro-exp, 
body.dark-theme .astro-lang { color: #d1d5db !important; }

body.dark-theme .astro-status { background: rgba(245, 158, 11, 0.2) !important; color: #fcd34d !important; }
body.dark-theme .astro-status.offline { background: #374151 !important; color: #9ca3af !important; }

body.dark-theme .call-btn { background: #374151 !important; border: 1px solid #4b5563 !important; color: #ffffff !important; }
body.dark-theme .call-btn.offline { background: #374151 !important; color: #9ca3af !important; border: none !important; }
</style>
</head>

<body>

<?php include '../user/mobile_header.php'; ?>

<div style="padding-top:14px;">

<?php foreach ($astrologers as $astro): ?>
<?php $online = $astro['online_status'] ? 1 : 0; ?>

<!-- MAIN CARD -->
<div class="astro-card" onclick="window.location.href='/astrologer/profile.php?id=<?= intval($astro['id']) ?>'">

  <!-- STATUS BADGE -->
  <span class="astro-status <?= !$online ? 'offline' : '' ?>">
      <?= $online ? __('Online') : __('Offline') ?>
  </span>

  <!-- IMAGE -->
  <div class="astro-left">
      <img src="<?= htmlspecialchars($astro['profile_photo'] ? '/assets/images/profiles/'.$astro['profile_photo'] : '/assets/images/default-user.png', ENT_QUOTES, 'UTF-8') ?>">
      <div class="astro-rating">★ <?= number_format((float)$astro['profile_rating'], 1) ?></div>
  </div>

  <!-- RIGHT DETAILS -->
  <div class="astro-details">
      <div class="astro-name"><?= htmlspecialchars($astro['astrologer_name'], ENT_QUOTES, 'UTF-8') ?></div>
      <div class="astro-skills"><?= htmlspecialchars($astro['skills'], ENT_QUOTES, 'UTF-8') ?></div>
      <div class="astro-exp"><?= intval($astro['experience_years']) ?> <?= __('yrs') ?></div>
      <div class="astro-lang"><?= htmlspecialchars($astro['languages'], ENT_QUOTES, 'UTF-8') ?></div>

      <div style="margin-top:4px;">
        <?php if ($astro['base_price_per_minute'] != $astro['price_per_minute']): ?>
          <span class="price-old">₹<?= (float)$astro['base_price_per_minute'] ?><?= __('/min') ?></span>
        <?php endif; ?>
        <span class="price-new">₹<?= (float)$astro['price_per_minute'] ?><?= __('/min') ?></span>
      </div>
  </div>

  <!-- CALL BUTTON -->
  <?php if ($online): ?>
      <form id="callForm_<?= intval($astro['id']) ?>" method="POST"
            action="/user/request_call.php" onclick="event.stopPropagation();">
        <input type="hidden" name="astro_id" value="<?= intval($astro['id']) ?>">
        <button type="button" class="call-btn"
                onclick="startCall(<?= intval($astro['id']) ?>)"><?= __('Call') ?></button>
      </form>
  <?php else: ?>
      <button class="call-btn offline" disabled><?= __('Offline') ?></button>
  <?php endif; ?>

</div>

<?php endforeach; ?>
</div>

<!-- SAFE GAP FOR FOOTER -->
<div style="height:95px;"></div>

<?php include '../user/footer_user.php'; ?>

<script>
// Apply dark theme initially if set
if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-theme');
}

const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

async function startCall(id) {
  if (!isLoggedIn) return window.location.href = "/login.php";

  const form = document.getElementById('callForm_' + id);
  const fd = new FormData(form);
  const res = await fetch(form.action, { method: "POST", body: fd });
  const data = await res.json();

  if (data.status === "success") {
    window.location.href =
      `/user/call_waiting.php?astro_id=${id}&call_id=${data.call_id}`;
  } else {
    alert(data.message);
  }
}
</script>

</body>
</html>