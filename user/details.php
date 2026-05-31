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
            'Complete Your Profile' => 'अपनी प्रोफ़ाइल पूरी करें',
            'Edit Details' => 'विवरण संपादित करें',
            'All fields except email are compulsory to proceed.' => 'आगे बढ़ने के लिए ईमेल को छोड़कर सभी फ़ील्ड अनिवार्य हैं।',
            'This email is already registered to another account.' => 'यह ईमेल पहले से ही किसी अन्य खाते में पंजीकृत है।',
            'Something went wrong. Please try again.' => 'कुछ गलत हो गया। कृपया पुन: प्रयास करें।',
            'Complete Profile' => 'प्रोफ़ाइल पूरी करें',
            'Please provide your details to access the dashboard.' => 'डैशबोर्ड तक पहुंचने के लिए कृपया अपना विवरण प्रदान करें।',
            'Name' => 'नाम',
            'Email' => 'ईमेल',
            '(Optional)' => '(वैकल्पिक)',
            'Enter email' => 'ईमेल दर्ज करें',
            'Gender' => 'लिंग',
            'Male' => 'पुरुष',
            'Female' => 'महिला',
            'Date of Birth' => 'जन्म तिथि',
            'Time of Birth' => 'जन्म का समय',
            'City of Birth' => 'जन्म का शहर',
            'Save & Continue' => 'सहेजें और जारी रखें',
            'Profile Setup' => 'प्रोफ़ाइल सेटअप',
            'Step 1' => 'चरण 1',
            'Final' => 'अंतिम',
            'Step.' => 'चरण।',
            'Refine Your' => 'अपनी पहचान',
            'Identity.' => 'सुधारें।',
            'Ensure your birth details are accurate for precise astrological predictions and personalized insights.' => 'सटीक ज्योतिषीय भविष्यवाणियों और व्यक्तिगत अंतर्दृष्टि के लिए सुनिश्चित करें कि आपका जन्म विवरण सटीक है।',
            'Full Name' => 'पूरा नाम',
            'Enter your name' => 'अपना नाम दर्ज करें',
            'Email Address' => 'ईमेल पता',
            'Enter your email address' => 'अपना ईमेल पता दर्ज करें',
            'e.g. Mumbai' => 'उदा. मुंबई',
            'Complete Setup' => 'सेटअप पूर्ण करें',
            'Save Changes' => 'परिवर्तन सहेजें',
            'Cancel' => 'रद्द करें'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// 1. Force login if session not found & enforce ID security
if (!isset($_SESSION['userid'])) {
    header('Location: /login.php');
    exit;
}

$error = '';
$userId = (int)$_SESSION['userid'];

// 2. Fetch current data to check for completion and fetch user role
$stmt = $conn->prepare("SELECT email, name, gender, dob, tob, city, role_id FROM users WHERE id=?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Securely assign the role
$role = isset($user['role_id']) ? (int)$user['role_id'] : 0;

// Determine if it's the dummy email
$is_dummy_email = (strpos($user['email'], '@fortunepathway.com') !== false);

// 3. Determine if this is a first-time setup or an edit
$is_incomplete = empty($user['name']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $input_email = trim($_POST['email'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $tob = $_POST['tob'] ?? '';
    $city = trim($_POST['city'] ?? '');

    if (!$name || !$gender || !$dob || !$tob || !$city) {
        $error = __('All fields except email are compulsory to proceed.');
    } else {
        // Email Logic: Keep original if left blank
        $final_email = $user['email']; 
        
        if ($input_email !== '' && $input_email !== $user['email']) {
            // Check if the new email already exists for someone else
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkStmt->execute([$input_email, $userId]);
            
            if ($checkStmt->rowCount() > 0) {
                $error = __('This email is already registered to another account.');
            } else {
                $final_email = $input_email;
            }
        }

        if (empty($error)) {
            $updateStmt = $conn->prepare("UPDATE users SET name=?, email=?, gender=?, dob=?, tob=?, city=? WHERE id=?");
            if ($updateStmt->execute([$name, $final_email, $gender, $dob, $tob, $city, $userId])) {
                header("Location: /user/profile.php");
                exit;
            } else {
                $error = __('Something went wrong. Please try again.');
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
<title><?= $is_incomplete ? __('Complete Your Profile') : __('Edit Details') ?> - Fortune Path</title>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
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
          "background-dark": "#0f1623"
      },
      fontFamily: { "display": ["Space Grotesk", "sans-serif"] },
    },
  },
}
</script>

<style>
  body { min-height: 100dvh; font-family: 'Space Grotesk', sans-serif; transition: background 0.3s ease; }
  html, body { touch-action: manipulation; }
  @media (min-width: 1024px) {
    .desktop-container { max-width: 80rem; margin: 0 auto; padding: 0 3rem; }
    .edit-card { background: #ffffff; border: 1px solid #f0f0f0; border-radius: 3rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.02); transition: all 0.3s ease; }
    .web-input { width: 100%; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 1rem; padding: 1rem 1.25rem; transition: all 0.3s ease; }
    .web-input:focus { background: #ffffff; border-color: #000; outline: none; box-shadow: 0 0 0 4px rgba(0,0,0,0.05); }
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-in { animation: slideUp 0.6s ease forwards; }
  }

  /* =========================================
     ENHANCED DARK MODE STYLES
  ========================================= */
  body.dark-theme { background-color: #121212 !important; color: #f9fafb !important; }
  body.dark-theme .bg-gray-50 { background-color: #121212 !important; }
  
  /* Mobile Dark Styles */
  body.dark-theme .bg-white { background-color: #1f2937 !important; border-color: #374151 !important; color: #f9fafb !important; }
  body.dark-theme .text-gray-900, body.dark-theme .text-black, body.dark-theme h2, body.dark-theme h1 { color: #f9fafb !important; }
  body.dark-theme .text-gray-700, body.dark-theme .text-gray-600 { color: #d1d5db !important; }
  body.dark-theme .text-gray-500, body.dark-theme .text-gray-400 { color: #9ca3af !important; }
  body.dark-theme .text-gray-300 { color: #6b7280 !important; }
  
  body.dark-theme .shadow-subtle { box-shadow: 0 4px 15px rgba(0,0,0,0.5) !important; }

  body.dark-theme input:not([type="radio"]) { background-color: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; }
  body.dark-theme input:focus { border-color: #10b981 !important; }
  body.dark-theme input::placeholder { color: #9ca3af !important; }
  body.dark-theme input[type="date"]::-webkit-calendar-picker-indicator,
  body.dark-theme input[type="time"]::-webkit-calendar-picker-indicator { filter: invert(1); }

  body.dark-theme button.bg-\[\#222222\] { background-color: #10b981 !important; color: #ffffff !important; border: none !important; }
  body.dark-theme button.bg-\[\#222222\]:hover { background-color: #059669 !important; }

  /* Desktop Dark Styles */
  body.dark-theme .edit-card { background-color: #1f2937 !important; border-color: #374151 !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5) !important; }
  body.dark-theme .web-input { background-color: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; }
  body.dark-theme .web-input:focus { background-color: #374151 !important; border-color: #10b981 !important; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2) !important; }
  
  body.dark-theme .bg-black { background-color: #10b981 !important; color: #ffffff !important; border: none !important; }
  body.dark-theme .bg-black:hover { background-color: #059669 !important; }
  
  body.dark-theme .bg-gray-100 { background-color: #374151 !important; color: #d1d5db !important; }
  body.dark-theme .bg-gray-100:hover { background-color: #4b5563 !important; color: #ffffff !important; }

  body.dark-theme .bg-gradient-to-b.from-gray-50.to-white { background: #121212 !important; }
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
    <?php 
        switch ($role) {
            case 1: include '../astrologer/header.php'; break;
            case 3: include '../admin/header.php'; break;
            default: include '../user/mobile_header.php'; break;
        }
    ?>

    <div class="w-full flex justify-center items-start bg-background-light dark:bg-background-dark min-h-screen px-2">
      <div class="w-full max-w-md mt-6 pb-24"> <h2 class="text-2xl font-bold text-center mb-2"><?= $is_incomplete ? __('Complete Profile') : __('Edit Details') ?></h2>
        <?php if($is_incomplete): ?>
            <p class="text-center text-gray-500 text-sm mb-6"><?= __('Please provide your details to access the dashboard.') ?></p>
        <?php endif; ?>

        <div class="bg-white shadow-subtle rounded-xl w-full p-6 mb-10">
          <form method="POST" autocomplete="off" class="flex flex-col gap-5">
            <div class="flex items-center justify-between gap-6">
              <label class="text-base font-semibold w-1/2"><?= __('Name') ?></label>
              <input type="text" name="name" required value="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="text-base font-medium w-1/2 rounded border border-gray-300 px-3 py-2"/>
            </div>
            
            <div class="flex items-center justify-between gap-6">
              <label class="text-base font-semibold w-1/2"><?= __('Email') ?> <span class="text-[10px] text-gray-400 font-normal block"><?= __('(Optional)') ?></span></label>
              <input type="email" name="email" value="<?= $is_dummy_email ? '' : htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= __('Enter email') ?>" class="text-base font-medium w-1/2 rounded border border-gray-300 px-3 py-2"/>
            </div>

            <div class="flex items-center justify-between gap-6">
              <label class="text-base font-semibold w-1/2"><?= __('Gender') ?></label>
              <div class="w-1/2 flex gap-3 justify-end">
                <label class="flex items-center cursor-pointer">
                  <input type="radio" name="gender" value="Male" <?= (($user['gender']??'')=='Male'?'checked':'')?> class="mr-1 accent-black" required> <?= __('Male') ?>
                </label>
                <label class="flex items-center cursor-pointer">
                  <input type="radio" name="gender" value="Female" <?= (($user['gender']??'')=='Female'?'checked':'')?> class="mr-1 accent-black" required> <?= __('Female') ?>
                </label>
              </div>
            </div>
            <div class="flex items-center justify-between gap-6">
              <label class="text-base font-semibold w-1/2"><?= __('Date of Birth') ?></label>
              <input type="date" name="dob" required value="<?= htmlspecialchars($user['dob'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="text-base font-medium w-1/2 rounded border border-gray-300 px-3 py-2"/>
            </div>
            <div class="flex items-center justify-between gap-6">
              <label class="text-base font-semibold w-1/2"><?= __('Time of Birth') ?></label>
              <input type="time" name="tob" required value="<?= htmlspecialchars($user['tob'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="text-base font-medium w-1/2 rounded border border-gray-300 px-3 py-2"/>
            </div>
            <div class="flex items-center justify-between gap-6">
              <label class="text-base font-semibold w-1/2"><?= __('City of Birth') ?></label>
              <input type="text" name="city" required value="<?= htmlspecialchars($user['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="text-base font-medium w-1/2 rounded border border-gray-300 px-3 py-2"/>
            </div>
            
            <?php if($error): ?>
              <div class="text-center text-red-600 text-sm font-bold"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div class="flex justify-center pt-2">
              <button type="submit" class="w-full bg-[#222222] text-white rounded-lg h-12 font-bold shadow-lg hover:bg-black transition-colors">
                  <?= $is_incomplete ? __('Save & Continue') : __('Update Details') ?>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <?php 
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobi') !== false) {
            switch ($role) {
              case 1: include '../astrologer/footer_astrologer.php'; break;
              case 3: include '../admin/footer_admin.php'; break;
              default: include '../user/footer_user.php'; break;
            }
        }
    ?>
</div>

<div class="hidden lg:block">
    <?php 
        switch ($role) {
            case 1: include '../astrologer/web_header.php'; break;
            case 3: include '../admin/web_header.php'; break;
            default: include '../user/web_header.php'; break;
        }
    ?>

    <main class="py-20 bg-gradient-to-b from-gray-50 to-white min-h-[85vh]">
        <div class="desktop-container">
            <div class="grid grid-cols-12 gap-16 items-start">
                
                <div class="col-span-5 animate-in">
                    <nav class="flex mb-8 text-xs font-bold uppercase tracking-widest text-gray-400 gap-3">
                        <span class="hover:text-black transition cursor-pointer"><?= __('Profile Setup') ?></span>
                        <span>/</span>
                        <span class="text-black"><?= $is_incomplete ? __('Step 1') : __('Edit Details') ?></span>
                    </nav>
                    <h1 class="text-6xl font-black text-gray-900 leading-tight mb-6">
                        <?= $is_incomplete ? __('Final') . '<br><span class="text-gray-400">' . __('Step.') . '</span>' : __('Refine Your') . '<br><span class="text-gray-400">' . __('Identity.') . '</span>' ?>
                    </h1>
                    <p class="text-lg text-gray-500 max-w-sm leading-relaxed">
                        <?= __('Ensure your birth details are accurate for precise astrological predictions and personalized insights.') ?>
                    </p>
                </div>

                <div class="col-span-7 animate-in" style="animation-delay: 0.2s;">
                    <div class="edit-card p-12">
                        <form method="POST" autocomplete="off" class="space-y-8">
                            
                            <div class="grid grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1"><?= __('Full Name') ?></label>
                                    <input type="text" name="name" required value="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="web-input" placeholder="<?= __('Enter your name') ?>">
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1 flex items-center justify-between">
                                        <?= __('Email Address') ?> <span class="text-[10px] text-gray-300 normal-case"><?= __('(Optional)') ?></span>
                                    </label>
                                    <input type="email" name="email" value="<?= $is_dummy_email ? '' : htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="web-input" placeholder="<?= __('Enter your email address') ?>">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1"><?= __('Date of Birth') ?></label>
                                    <input type="date" name="dob" required value="<?= htmlspecialchars($user['dob'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="web-input">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1"><?= __('Time of Birth') ?></label>
                                    <input type="time" name="tob" required value="<?= htmlspecialchars($user['tob'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="web-input">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1"><?= __('Gender') ?></label>
                                    <div class="flex gap-6 h-[58px] items-center px-6 bg-gray-50 rounded-2xl border border-gray-100">
                                        <label class="flex items-center gap-2 cursor-pointer font-bold text-gray-700">
                                            <input type="radio" name="gender" value="Male" <?= (($user['gender']??'')=='Male'?'checked':'')?> class="w-5 h-5 accent-black" required> <?= __('Male') ?>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer font-bold text-gray-700">
                                            <input type="radio" name="gender" value="Female" <?= (($user['gender']??'')=='Female'?'checked':'')?> class="w-5 h-5 accent-black" required> <?= __('Female') ?>
                                        </label>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1"><?= __('City of Birth') ?></label>
                                    <input type="text" name="city" required value="<?= htmlspecialchars($user['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="web-input" placeholder="<?= __('e.g. Mumbai') ?>">
                                </div>
                            </div>

                            <?php if($error): ?>
                                <div class="p-4 bg-red-50 text-red-600 rounded-xl text-center font-bold border border-red-100"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                            <?php endif; ?>

                            <div class="pt-6 flex gap-4">
                                <button type="submit" class="flex-1 py-5 bg-black text-white rounded-2xl font-bold text-xl hover:bg-gray-800 transition shadow-xl">
                                    <?= $is_incomplete ? __('Complete Setup') : __('Save Changes') ?>
                                </button>
                                
                                <?php if(!$is_incomplete): ?>
                                    <a href="profile.php" class="px-10 py-5 bg-gray-100 text-gray-600 rounded-2xl font-bold text-xl hover:bg-gray-200 transition text-center flex items-center justify-center">
                                        <?= __('Cancel') ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php 
        switch ($role) {
            case 1: include '../astrologer/web_footer.php'; break;
            case 3: include '../admin/web_footer.php'; break;
            default: include '../user/web_footer.php'; break;
        }
    ?>
</div>

</body>
</html>