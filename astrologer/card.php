<?php
// Assume $astro array available from your existing PHP code

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
            'years of experience' => 'साल का अनुभव',
            'Call' => 'कॉल'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// Prepare variables
$photo = $astro['profile_photo'] ? '/assets/images/profiles/' . $astro['profile_photo'] : '/assets/images/default-user.png';
$rating = $astro['profile_rating'] ?? 4.0;
$rating_count = $astro['rating_count'] ?? 0;

$status_color = $astro['online_status'] ? '#21ba4b' : '#db2525';
$status_text = $astro['online_status'] ? __('Online') : __('Offline');

$minutes_label = $astro['online_status'] ? __('Online') : __('Offline');
$minutes_color = $astro['online_status'] ? '#21ba4b' : '#db2525';

$offer = isset($astro['offer_percent']) && $astro['offer_percent'] > 0 ? $astro['offer_percent'].'% OFF' : '';
$show_offer = $offer ? true : false;

$basic_price = $astro['base_price_per_minute'] ?? ($astro['price_per_minute'] ?? 0);
$offer_price = $astro['price_per_minute'] ?? 0;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
<title>Astrologer Profile Card</title>

<style>
  body {
    font-family: Arial, sans-serif;
    background-color: #fafafa;
    color: #222222;
    padding: 15px;
    margin: 0;
    transition: background 0.3s ease;
  }
  .astro-display-card {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px 20px 16px 12px;
    border: 1px solid #eee;
    border-radius: 18px;
    background: #fff;
    max-width: 480px;
    box-shadow: 0 1px 8px rgba(90,16,236,0.07);
    position: relative;
    margin: 20px auto;
  }
  .offer-badge {
    position: absolute;
    top: 7px;
    left: 7px;
    background: #b02676;
    color: #fff;
    font-weight: 700;
    font-size: 0.95rem;
    padding: 2px 12px 2px 10px;
    border-radius: 11px;
  }
  .astro-photo {
    width: 80px;
    height: 110px;
    object-fit: cover;
    border-radius: 14px;
    box-shadow: 0 2px 8px #dde2fa;
  }
  .astro-info {
    flex: 1;
  }
  .astro-name-status {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 2px;
  }
  .astro-name {
    font-size: 1.13rem;
    font-weight: 700;
    letter-spacing: 0.2px;
    color: #222222;
  }
  .status-badge {
    border-radius: 11px;
    font-size: 0.99rem;
    padding: 2px 11px;
    color: <?= $minutes_color ?>;
    background-color: <?= $astro['online_status'] ? '#e2fbe9' : '#fde9e9' ?>;
  }
  .astro-skills {
    font-size: 0.99rem;
    color: #444444;
    margin-bottom: 3px;
  }
  .astro-experience-languages {
    font-size: 0.94rem;
    color: #666666;
  }
  .astro-prices {
    font-size: 0.95rem;
    margin-top: 2px;
    color: #333333;
  }
  .price-base {
    text-decoration: line-through;
    color: #b02734;
    font-weight: 600;
    font-size: 0.97rem;
  }
  .price-offer {
    color: #21ba4b;
    font-weight: 700;
    margin-left: 5px;
    font-size: 1.12rem;
  }
  .astro-rating-call {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 1rem;
    margin-top: 6px;
  }
  .rating-badge {
    background: #37223b;
    color: white;
    padding: 2px 11px 2px 9px;
    border-radius: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
  }
  .rating-star {
    font-size: 1.1rem;
    margin-right: 4px;
  }
  .rating-count {
    font-size: 0.91rem;
    color: #ffc;
    font-weight: 500;
    margin-left: 5px;
  }

  .offline-btn {
      background:#cccccc;
      color:#666 !important;
      padding:6px 14px;
      border-radius:8px;
      font-weight:700;
      border:none;
      cursor:not-allowed;
      font-size:0.9rem;
  }
  .call-btn {
      background:#222222;
      color:white;
      border:none;
      font-size:0.98rem;
      cursor:pointer;
      margin-left:10px;
      padding:6px 14px;
      border-radius:8px;
      font-weight:700;
  }

  /* =========================================
     ENHANCED DARK MODE STYLES
  ========================================= */
  body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
  body.dark-theme .astro-display-card { background: #1f2937 !important; border-color: #374151 !important; }
  body.dark-theme .astro-name { color: #ffffff !important; }
  body.dark-theme .astro-skills { color: #d1d5db !important; }
  body.dark-theme .astro-experience-languages { color: #d1d5db !important; }
  body.dark-theme .astro-prices { color: #ffffff !important; }
  
  /* Buttons */
  body.dark-theme .call-btn { background: #374151 !important; color: #ffffff !important; border: 1px solid #4b5563 !important; }
  body.dark-theme .call-btn:hover { background: #4b5563 !important; }
  body.dark-theme .offline-btn { background: #374151 !important; color: #9ca3af !important; border: 1px solid #4b5563 !important; }
</style>
</head>

<body>

<div class="astro-display-card">

    <?php if($show_offer): ?>
      <span class="offer-badge"><?= htmlspecialchars($offer, ENT_QUOTES, 'UTF-8') ?></span>
    <?php endif ?>

    <img src="<?= htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') ?>" class="astro-photo" alt="Astrologer" />

    <div class="astro-info">

        <div class="astro-name-status">
            <div class="astro-name"><?= htmlspecialchars($astro['astrologer_name'], ENT_QUOTES, 'UTF-8') ?></div>
            <span class="status-badge"><?= htmlspecialchars($minutes_label, ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <div class="astro-skills">
            <?= htmlspecialchars($astro['skills'], ENT_QUOTES, 'UTF-8') ?> <span style="color:#b399ff;font-size:1.12rem;">★</span>
        </div>

        <div class="astro-experience-languages">
            <?= htmlspecialchars($astro['experience_years'], ENT_QUOTES, 'UTF-8') ?> <?= __('years of experience') ?> <br>
            <?= htmlspecialchars($astro['languages'], ENT_QUOTES, 'UTF-8') ?>
        </div>

        <div class="astro-prices">
            <span class="price-base"><?= $basic_price ? '₹' . number_format((float)$basic_price, 1) . '/min' : '' ?></span>
            <span class="price-offer">₹<?= number_format((float)$offer_price, 1) ?>/min</span>
        </div>

        <div class="astro-rating-call">
            <span class="rating-badge">
                <span class="rating-star">★</span> <?= htmlspecialchars($rating, ENT_QUOTES, 'UTF-8') ?>
                <span class="rating-count"><?= intval($rating_count) ?>+</span>
            </span>

            <!-- 🌟 OFFLINE = Show Grey Button | ONLINE = Show Call Button -->
            <?php if ($astro['online_status'] == 1): ?>
                <!-- ONLINE ⇒ SHOW CALL BUTTON -->
                <form id="callForm_<?= intval($astro['id']) ?>" class="call-form" method="POST" action="/user/request_call.php">
                    <input type="hidden" name="astro_id" value="<?= intval($astro['id']) ?>">
                    <button type="button" class="call-btn" onclick="startCall(<?= intval($astro['id']) ?>)"><?= __('Call') ?></button>
                </form>
            <?php else: ?>
                <!-- OFFLINE ⇒ SHOW DISABLED BUTTON -->
                <button class="offline-btn" disabled><?= __('Offline') ?></button>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
// Apply dark theme initially if set
if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-theme');
}

function startCall(astro_id) {
  const form = document.getElementById('callForm_' + astro_id);
  const formData = new FormData(form);

  fetch(form.action, {
    method: 'POST',
    body: formData,
    credentials: 'include'
  })
  .then(response => response.text())
  .then(data => {
    if (data.startsWith('Error') || data.startsWith('Not')) {
        alert(data);
    } else {
        window.location.href = '/call/active_call.php?call_id=' + data.trim();
    }
  })
  .catch(err => alert('Network error: ' + err));
}
</script>

</body>
</html>