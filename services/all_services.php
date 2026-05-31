<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================
   CUSTOM LOCALIZATION SYSTEM 
============================ */
if (!function_exists('__')) {
    $current_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
    $translations = [
        'en' => [],
        'hi' => [
            'All Services' => 'सभी सेवाएं',
            'Astrology' => 'ज्योतिष',
            'Vastu' => 'वास्तु',
            'Numerology' => 'अंक ज्योतिष',
            'Palm Reading' => 'हस्तरेखा',
            'Face Reading' => 'चेहरा पढ़ना',
            'Pooja Rituals' => 'पूजा अनुष्ठान',
            'Shopping' => 'खरीदारी',
            'Report Area' => 'रिपोर्ट क्षेत्र'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

$isLoggedIn = isset($_SESSION['userid']);

// Include header
include '../user/mobile_header.php';

// SERVICES LIST (Translated)
$services = [
  ['name' => __('Astrology'),     'image' => '../assets/services/astrology.png',     'link' => 'astrology.php'],
  ['name' => __('Vastu'),         'image' => '../assets/services/vastu.png',         'link' => 'vastu.php'],
  ['name' => __('Numerology'),    'image' => '../assets/services/numerology.png',    'link' => 'numerology.php'],
  ['name' => __('Palm Reading'),  'image' => '../assets/services/palm_reading.png',  'link' => 'palm_reading.php'],
  ['name' => __('Face Reading'),  'image' => '../assets/services/Face_reading.png',  'link' => 'Face_reading.php'],
  ['name' => __('Pooja Rituals'), 'image' => '../assets/services/pooja.png',         'link' => 'pooja.php'],
  ['name' => __('Shopping'),      'image' => '../assets/services/shopping.jpg',      'link' => '../products/list.php'],
  ['name' => __('Report Area'),   'image' => '../assets/services/ReportArea.png',    'link' => 'Report_area.php'],
];
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang ?? 'en', ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= __('All Services') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk&display=swap" rel="stylesheet"/>
  
  <style>
    body {
      font-family: 'Space Grotesk', sans-serif;
      background: #fff;
      margin: 0;
      padding: 0;
      transition: background 0.3s ease;
    }
    .services-section {
      max-width: 700px;
      margin: 0 auto;
      padding: 24px 12px 0 12px;
    }
    .services-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }
    .service-card {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      background: #fff;
      border-radius: 13px;
      box-shadow: 0 1px 8px rgba(68, 68, 68, 0.08);
      padding: 10px 6px 8px 6px;
      transition: box-shadow 0.15s, transform 0.18s, background 0.3s;
      cursor: pointer;
      text-decoration: none;
    }
    .service-card:hover {
      box-shadow: 0 2px 12px rgba(68, 68, 68, 0.11);
      transform: translateY(-2px) scale(1.043);
    }
    .service-img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 6px;
      background-color: #f8f8f8;
      box-shadow: 0 1px 5px rgba(0,0,0,0.03);
      transition: background 0.3s;
    }

    /* BLUR EFFECT FOR GUESTS */
    .blur-img {
      filter: blur(4px);
      opacity: 0.6;
    }

    .service-name {
      font-size: 0.98rem;
      font-weight: 500;
      color: #222;
      margin-top: 2px;
      margin-bottom: 1px;
      transition: color 0.3s;
    }

    @media (max-width: 600px) {
      .services-section { padding: 16px 6px 0 6px; }
      .services-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
      .service-card { padding: 10px 2px 9px 2px; }
      .service-img { width: 74px; height: 74px; }
      .service-name { font-size: 1.07rem; }
    }

    /* =========================================
       ENHANCED DARK MODE STYLES
    ========================================= */
    body.dark-theme { background: #121212 !important; }
    body.dark-theme .service-card { background: #1f2937 !important; box-shadow: 0 4px 10px rgba(0,0,0,0.5) !important; }
    body.dark-theme .service-card:hover { box-shadow: 0 6px 15px rgba(0,0,0,0.6) !important; }
    body.dark-theme .service-name { color: #f9fafb !important; }
    body.dark-theme .service-img { background-color: #374151 !important; box-shadow: none !important; }
  </style>
</head>

<body>
<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<div class="services-section">
<br>

  <div class="services-grid">
    <?php foreach ($services as $srv): ?>

      <?php if ($isLoggedIn): ?>
      
        <!-- NORMAL VIEW FOR LOGGED IN USERS -->
        <a class="service-card" href="<?= htmlspecialchars($srv['link'], ENT_QUOTES, 'UTF-8') ?>">
          <img class="service-img" src="<?= htmlspecialchars($srv['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($srv['name'], ENT_QUOTES, 'UTF-8') ?>" />
          <div class="service-name"><?= htmlspecialchars($srv['name'], ENT_QUOTES, 'UTF-8') ?></div>
        </a>

      <?php else: ?>

        <!-- GUEST USER VIEW: BLURRED IMAGE + CLICK → LOGIN -->
        <a class="service-card" href="/login.php">
          <img class="service-img blur-img" src="<?= htmlspecialchars($srv['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($srv['name'], ENT_QUOTES, 'UTF-8') ?>" />
          <div class="service-name"><?= htmlspecialchars($srv['name'], ENT_QUOTES, 'UTF-8') ?></div>
        </a>

      <?php endif; ?>

    <?php endforeach; ?>
  </div>

</div>

<?php include '../user/footer_user.php'; ?>

</body>
</html>