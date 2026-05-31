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
            'User Profile - Fortune Path' => 'उपयोगकर्ता प्रोफ़ाइल - फॉर्च्यून पाथ',
            'Profile' => 'प्रोफ़ाइल',
            'Name' => 'नाम',
            'Email ID' => 'ईमेल आईडी',
            'Gender' => 'लिंग',
            'Date of Birth' => 'जन्म तिथि',
            'Time of Birth' => 'जन्म का समय',
            'City of Birth' => 'जन्म का शहर',
            'Edit Details' => 'विवरण संपादित करें',
            'Account Settings' => 'खाता सेटिंग्स',
            'Your' => 'आपकी',
            'Profile.' => 'प्रोफ़ाइल।',
            'Manage your personal information and spiritual preferences in one place.' => 'अपनी व्यक्तिगत जानकारी और आध्यात्मिक प्राथमिकताओं को एक ही स्थान पर प्रबंधित करें।',
            'Edit Profile Details' => 'प्रोफ़ाइल विवरण संपादित करें',
            'Full Name' => 'पूरा नाम',
            'Email Address' => 'ईमेल पता',
            'Birth Details' => 'जन्म विवरण',
            'Place of Birth' => 'जन्म स्थान',
            'Your data is securely stored and used only for accurate astrological calculations.' => 'आपका डेटा सुरक्षित रूप से संग्रहीत है और केवल सटीक ज्योतिषीय गणनाओं के लिए उपयोग किया जाता है।'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

if (!isset($_SESSION['userid'])) {
    header('Location: /auth/login.php');
    exit;
}

// Hardened Security: Ensure ID is cast as integer
$user_id = (int)$_SESSION['userid'];

$stmt = $conn->prepare("SELECT email, name, gender, dob, tob, city FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if it's the auto-generated dummy email
$is_dummy_email = (strpos($user['email'], '@fortunepathway.com') !== false);
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>" class="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<title><?= __('User Profile - Fortune Path') ?></title>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
<link rel="stylesheet" href="/assets/css/header.css">
<link rel="stylesheet" href="/assets/css/footer.css">

<script>
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "primary": "#000000",
        "background-light": "#ffffff",
        "background-dark": "#0f1623",
      },
      fontFamily: { "display": ["Space Grotesk", "sans-serif"] },
    },
  },
}
</script>

<style>
  /* Mobile Fixes */
  body { min-height: 100dvh; transition: background 0.3s ease; }
  html, body { touch-action: manipulation; }

  /* Desktop Optimization */
  @media (min-width: 1024px) {
    .desktop-container {
        max-width: 80rem; 
        margin: 0 auto;
        padding: 0 3rem;
    }
    .profile-card {
        background: #ffffff;
        border: 1px solid #f0f0f0;
        border-radius: 3rem;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.02);
        transition: all 0.4s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-profile { animation: fadeIn 0.6s ease-out forwards; }
  }

  /* =========================================
     ENHANCED DARK MODE STYLES
  ========================================= */
  body.dark-theme { background-color: #121212 !important; color: #f9fafb !important; }
  
  body.dark-theme .bg-gray-50 { background-color: #121212 !important; }
  body.dark-theme .bg-white { background-color: #1f2937 !important; }
  
  body.dark-theme .text-gray-900, body.dark-theme .text-black, body.dark-theme h2, body.dark-theme h1 { color: #ffffff !important; }
  body.dark-theme .text-gray-700, body.dark-theme .text-gray-600 { color: #d1d5db !important; }
  body.dark-theme .text-gray-500, body.dark-theme .text-gray-400 { color: #9ca3af !important; }
  body.dark-theme .text-gray-300 { color: #6b7280 !important; }
  
  body.dark-theme .bg-gray-100 { background-color: #374151 !important; color: #d1d5db !important; }

  /* Mobile Profile Box */
  body.dark-theme .shadow-subtle { box-shadow: 0 4px 15px rgba(0,0,0,0.5) !important; }
  
  /* Desktop Profile Card */
  body.dark-theme .profile-card { background-color: #1f2937 !important; border-color: #374151 !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5) !important; }
  body.dark-theme .border-gray-50 { border-color: #374151 !important; }
  
  /* Buttons */
  body.dark-theme .bg-\[\#222222\], body.dark-theme .bg-black { background-color: #10b981 !important; color: #ffffff !important; border: none !important; }
  body.dark-theme .bg-\[\#222222\]:hover, body.dark-theme .bg-black:hover { background-color: #059669 !important; }
</style>
</head>
<body class="font-display bg-gray-50 lg:bg-white">

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
        document.documentElement.classList.add('dark');
    }
</script>

<div class="lg:hidden">
    <?php include '../user/mobile_header.php'; ?>
    <div class="w-full flex justify-center items-start bg-background-light dark:bg-background-dark min-h-screen">
      <div class="w-full max-w-md mt-6 mx-2 pb-24"> <!-- Added padding bottom to accommodate mobile footer -->
        <h2 class="text-2xl font-bold text-center mb-6"><?= __('Profile') ?></h2>
        <div class="bg-white dark:bg-background-dark shadow-subtle rounded-xl w-full p-6">
          <div class="flex flex-col gap-5">
            <div class="flex items-center justify-between">
              <span class="text-base font-semibold"><?= __('Name') ?></span>
              <span class="text-base font-medium"><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            
            <?php if (!$is_dummy_email): ?>
            <div class="flex items-center justify-between">
              <span class="text-base font-semibold"><?= __('Email ID') ?></span>
              <span class="text-base font-medium truncate ml-4"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <?php endif; ?>

            <div class="flex items-center justify-between">
              <span class="text-base font-semibold"><?= __('Gender') ?></span>
              <span class="text-base font-medium"><?= htmlspecialchars($user['gender'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-base font-semibold"><?= __('Date of Birth') ?></span>
              <span class="text-base font-medium"><?= htmlspecialchars($user['dob'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-base font-semibold"><?= __('Time of Birth') ?></span>
              <span class="text-base font-medium"><?= htmlspecialchars($user['tob'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-base font-semibold"><?= __('City of Birth') ?></span>
              <span class="text-base font-medium"><?= htmlspecialchars($user['city'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
          </div>
        </div>
        <div class="flex justify-center pt-4">
          <a href="details.php" class="flex min-w-[140px] max-w-[240px] cursor-pointer items-center justify-center rounded-lg h-12 px-5 bg-[#222222] text-white text-base font-bold transition hover:bg-black">
            <?= __('Edit Details') ?>
          </a>
        </div>
      </div>
    </div>
</div>

<div class="hidden lg:block">
    <?php include '../user/web_header.php'; ?>

    <main class="py-20 min-h-[80vh]">
        <div class="desktop-container">
            <div class="grid grid-cols-12 gap-12 items-start">
                
                <div class="col-span-4 space-y-6 animate-profile">
                    <span class="px-4 py-1.5 bg-gray-100 text-gray-500 rounded-full text-xs font-bold uppercase tracking-widest"><?= __('Account Settings') ?></span>
                    <h1 class="text-6xl font-black text-gray-900 leading-tight"><?= __('Your') ?> <br><span class="text-gray-400"><?= __('Profile.') ?></span></h1>
                    <p class="text-lg text-gray-500 max-w-xs"><?= __('Manage your personal information and spiritual preferences in one place.') ?></p>
                    
                    <div class="pt-10">
                        <a href="details.php" class="inline-flex items-center gap-3 px-8 py-4 bg-black text-white rounded-2xl font-bold hover:bg-gray-800 transition shadow-xl">
                            <span class="material-symbols-outlined">edit</span>
                            <?= __('Edit Profile Details') ?>
                        </a>
                    </div>
                </div>

                <div class="col-span-8 animate-profile" style="animation-delay: 0.2s;">
                    <div class="profile-card p-12">
                        <div class="grid grid-cols-2 gap-y-12 gap-x-8">
                            
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest"><?= __('Full Name') ?></p>
                                <p class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>

                            <?php if (!$is_dummy_email): ?>
                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest"><?= __('Email Address') ?></p>
                                <p class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                            <?php endif; ?>

                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest"><?= __('Gender') ?></p>
                                <p class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($user['gender'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>

                            <div class="space-y-1">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest"><?= __('Birth Details') ?></p>
                                <p class="text-2xl font-bold text-gray-900">
                                    <?= htmlspecialchars($user['dob'], ENT_QUOTES, 'UTF-8') ?> <span class="text-gray-300 mx-2">|</span> <?= htmlspecialchars($user['tob'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>

                            <div class="space-y-1 col-span-2">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest"><?= __('Place of Birth') ?></p>
                                <p class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($user['city'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>

                        </div>

                        <div class="mt-16 pt-8 border-t border-gray-50 flex items-center gap-4 text-gray-400">
                            <span class="material-symbols-outlined">verified_user</span>
                            <p class="text-sm font-medium"><?= __('Your data is securely stored and used only for accurate astrological calculations.') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../user/web_footer.php'; ?>
</div>

<?php
// Secure Role-based footer logic preserved
$stmtRole = $conn->prepare("SELECT role_id FROM users WHERE id = ?");
$stmtRole->execute([$user_id]);
$role = (int)$stmtRole->fetchColumn();

// Only show mobile footer for mobile devices
if (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobi') !== false):
    switch ($role) {
      case 1: include '../astrologer/footer_astrologer.php'; break;
      case 3: include '../admin/footer_admin.php'; break;
      default: include '../user/footer_user.php';
    }
endif;
?>

</body>
</html>