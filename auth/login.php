<?php
// ======================================
// NEVER EXPIRE SESSION + AUTO REDIRECT
// ======================================
ini_set('session.gc_maxlifetime', 315360000); 
session_set_cookie_params(315360000);          

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
            'Invalid email or password.' => 'अमान्य ईमेल या पासवर्ड।',
            'Passwords do not match!' => 'पासवर्ड मेल नहीं खाते!',
            'Email already registered!' => 'ईमेल पहले से ही पंजीकृत है!',
            'Please enter a valid 10-digit mobile number.' => 'कृपया एक वैध 10-अंकीय मोबाइल नंबर दर्ज करें।',
            'Incorrect OTP Entered. Please try again.' => 'गलत ओटीपी दर्ज किया गया। कृपया पुनः प्रयास करें।',
            'Mobile' => 'मोबाइल',
            'Email' => 'ईमेल',
            'Phone Number' => 'फ़ोन नंबर',
            'Enter mobile number' => 'मोबाइल नंबर दर्ज करें',
            'Get OTP →' => 'ओटीपी प्राप्त करें →',
            'Email Address' => 'ईमेल पता',
            'Password' => 'पासवर्ड',
            'Login →' => 'लॉगिन →',
            "Don't have an account?" => "क्या आपके पास अकाउंट नहीं है?",
            'Sign Up' => 'साइन अप करें',
            'Create Password' => 'पासवर्ड बनाएं',
            'Confirm Password' => 'पासवर्ड की पुष्टि करें',
            'Create Account' => 'अकाउंट बनाएं',
            'Already have an account?' => 'क्या पहले से अकाउंट है?',
            'Login' => 'लॉगिन करें',
            'OTP Sent to :' => 'इस पर ओटीपी भेजा गया:',
            'Enter 4-digit Code' => '4-अंकीय कोड दर्ज करें',
            'Verify & Login' => 'सत्यापित करें और लॉगिन करें',
            'Change Mobile Number' => 'मोबाइल नंबर बदलें',
            'Secure Portal' => 'सुरक्षित पोर्टल',
            'Unlock Your' => 'अपना अनलॉक करें',
            'Cosmic Path.' => 'ब्रह्मांडीय पथ।',
            'Enter your mobile number or email to instantly access your dashboard and consult with experts.' => 'अपने डैशबोर्ड तक तुरंत पहुंचने और विशेषज्ञों से परामर्श करने के लिए अपना मोबाइल नंबर या ईमेल दर्ज करें।',
            'Continue with Mobile' => 'मोबाइल के साथ जारी रखें',
            'Verify OTP' => 'ओटीपी सत्यापित करें',
            'Secure Login' => 'सुरक्षित लॉगिन',
            'Join Fortune Path' => 'फॉर्च्यून पाथ से जुड़ें',
            'Entered wrong number? Change here.' => 'गलत नंबर दर्ज किया? यहां बदलें।'
        ]
    ];
    function __($key) {
        global $translations, $current_lang;
        return isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : $key;
    }
}

// ==========================================
// STORE REFERRAL CODE IN SESSION IF PRESENT
// ==========================================
if (isset($_GET['ref']) && $_GET['ref'] !== "") {
    $_SESSION['ref_code'] = trim($_GET['ref']);
}

// Redirect already logged-in users
if (isset($_SESSION['userid']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 2) {
        $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['userid']]);
        $check = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($check['name'])) {
            header("Location: /user/details.php");
            exit;
        }
    }
    redirectBasedOnRole($_SESSION['role']);
}

$error = '';
$step = 'enter_phone'; // Default step for OTP
$active_tab = 'phone'; // Default main tab (phone vs email)
$email_mode = 'login'; // Default sub-mode for email (login vs register)

function redirectBasedOnRole($role_id) {
    switch ($role_id) {
        case 3: header("Location: /admin/dashboard.php"); break;
        case 1: header("Location: /astrologer/dashboard.php"); break;
        case 2:
        default: header("Location: /index.php");
    }
    exit;
}

// ==========================================
// EMAIL LOGIN LOGIC
// ==========================================
if (isset($_POST['login_email'])) {
    $active_tab = 'email';
    $email_mode = 'login';
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    
    $stmt = $conn->prepare("SELECT id, role_id, password, name FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $login_success = false;

        if (password_verify($pass, $user['password'])) {
            $login_success = true;
        } 
        elseif ($user['password'] === md5($pass)) {
            $login_success = true;
            $new_hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $update_stmt->execute([$new_hashed_pass, $user['id']]);
        }

        if ($login_success) {
            $_SESSION['userid'] = $user['id'];
            $_SESSION['role']   = $user['role_id'];
            
            if ($user['role_id'] == 2 && empty($user['name'])) {
                header("Location: /user/details.php");
                exit;
            }
            redirectBasedOnRole($user['role_id']);
        } else {
            $error = __('Invalid email or password.');
        }
    } else {
        $error = __('Invalid email or password.');
    }
}

// ==========================================
// EMAIL REGISTER LOGIC
// ==========================================
if (isset($_POST['register_email'])) {
    $active_tab = 'email';
    $email_mode = 'register';
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $conf  = $_POST['conf_password'] ?? '';

    if ($pass !== $conf) {
        $error = __('Passwords do not match!');
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->execute([$email]);
        
        if ($check->rowCount() > 0) {
            $error = __('Email already registered!');
        } else {
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            $q = $conn->prepare("INSERT INTO users (email, password, role_id, wallet_balance) VALUES (?, ?, 2, 0)");
            $q->execute([$email, $hashed_pass]);
            
            $newUserId = $conn->lastInsertId();
            
            $newReferralCode = 'REF' . str_pad($newUserId, 6, '0', STR_PAD_LEFT);
            $conn->prepare("UPDATE users SET referral_code=? WHERE id=?")->execute([$newReferralCode, $newUserId]);
            
            $settings = $conn->query("SELECT registration_bonus_amount, referral_bonus_amount FROM admin_settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);
            $registrationBonus = floatval($settings['registration_bonus_amount'] ?? 0);
            $referralBonus     = floatval($settings['referral_bonus_amount'] ?? 0);

            $referrer = null;
            if (!empty($_SESSION['ref_code'])) {
                $stmtRef = $conn->prepare("SELECT id FROM users WHERE referral_code=? LIMIT 1");
                $stmtRef->execute([$_SESSION['ref_code']]);
                $referrer = $stmtRef->fetch(PDO::FETCH_ASSOC);
            }

            if ($referrer) {
                if ($referralBonus > 0) {
                    $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id=?")->execute([$referralBonus, $newUserId]);
                    $conn->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description) VALUES (?, 'referral_bonus', ?, 'Referral bonus')")->execute([$newUserId, $referralBonus]);
                    
                    $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id=?")->execute([$referralBonus, $referrer['id']]);
                    $conn->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description) VALUES (?, 'referral_bonus', ?, 'Referral bonus')")->execute([$referrer['id'], $referralBonus]);
                }
                $conn->prepare("UPDATE users SET referred_by=? WHERE id=?")->execute([$referrer['id'], $newUserId]);
            } else if ($registrationBonus > 0) {
                $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id=?")->execute([$registrationBonus, $newUserId]);
            }
            
            $_SESSION['userid'] = $newUserId;
            $_SESSION['role']   = 2;
            unset($_SESSION['ref_code']);
            
            header("Location: /user/details.php");
            exit;
        }
    }
}

// ==========================================
// STEP 1: SEND OTP LOGIC
// ==========================================
if (isset($_POST['send_otp'])) {
    $active_tab = 'phone';
    $phone = trim($_POST['phone'] ?? '');
    
    if (preg_match('/^[0-9]{10}$/', $phone)) {
        $_SESSION['phone'] = $phone;
        $_SESSION['OTP'] = rand(1111, 9999); 
        
        $API = "4ae1e2b07ecf6c799b91ed45e95278b8"; 
        $OTP = $_SESSION['OTP']; 
        $PHONE = $_SESSION['phone']; 
        
        $URL = "https://sms.renflair.in/V1.php?API=" . urlencode($API) . "&PHONE=" . urlencode($PHONE) . "&OTP=" . urlencode($OTP); 
        
        $curl = curl_init(); 
        curl_setopt($curl, CURLOPT_URL, $URL); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        
        $resp = curl_exec($curl); 
        
        if($resp === false) {
            $error = "cURL Error: " . curl_error($curl);
        } else {
            $resp_lower = strtolower($resp);
            if(strpos($resp_lower, 'error') !== false || strpos($resp_lower, 'fail') !== false || strpos($resp_lower, 'invalid') !== false) {
                 $error = "SMS Provider Error: " . htmlspecialchars($resp, ENT_QUOTES, 'UTF-8');
            } else {
                $step = 'verify_otp';
            }
        }
        curl_close($curl); 
        
    } else {
        $error = __('Please enter a valid 10-digit mobile number.');
    }
}

// ==========================================
// STEP 2: VERIFY OTP & AUTO LOGIN/REGISTER
// ==========================================
if (isset($_POST['verify_otp'])) {
    $active_tab = 'phone';
    $entered_otp = $_POST['otp'] ?? ''; 
    $actual_sent_otp = $_SESSION['OTP'] ?? ''; 
    $phone = $_SESSION['phone'] ?? ''; 
    
    if ((string)$entered_otp === (string)$actual_sent_otp) { 
        
        $stmt = $conn->prepare("SELECT id, role_id, name FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $_SESSION['userid'] = $user['id'];
            $_SESSION['role']   = $user['role_id'];
            
            if ($user['role_id'] == 2 && empty($user['name'])) {
                header("Location: /user/details.php");
                exit;
            }
            unset($_SESSION['OTP'], $_SESSION['phone']);
            redirectBasedOnRole($user['role_id']);
            
        } else {
            $dummy_email = 'user_' . time() . '_' . rand(100,999) . '@fortunepathway.com';
            $dummy_password = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
            
            $q = $conn->prepare("INSERT INTO users (phone, email, password, role_id, wallet_balance) VALUES (?, ?, ?, 2, 0)");
            $q->execute([$phone, $dummy_email, $dummy_password]);
            
            $newUserId = $conn->lastInsertId();
            
            $newReferralCode = 'REF' . str_pad($newUserId, 6, '0', STR_PAD_LEFT);
            $conn->prepare("UPDATE users SET referral_code=? WHERE id=?")->execute([$newReferralCode, $newUserId]);
            
            $settings = $conn->query("SELECT registration_bonus_amount, referral_bonus_amount FROM admin_settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);
            $registrationBonus = floatval($settings['registration_bonus_amount'] ?? 0);
            $referralBonus     = floatval($settings['referral_bonus_amount'] ?? 0);

            $referrer = null;
            if (!empty($_SESSION['ref_code'])) {
                $stmtRef = $conn->prepare("SELECT id FROM users WHERE referral_code=? LIMIT 1");
                $stmtRef->execute([$_SESSION['ref_code']]);
                $referrer = $stmtRef->fetch(PDO::FETCH_ASSOC);
            }

            if ($referrer) {
                if ($referralBonus > 0) {
                    $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id=?")->execute([$referralBonus, $newUserId]);
                    $conn->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description) VALUES (?, 'referral_bonus', ?, 'Referral bonus')")->execute([$newUserId, $referralBonus]);
                    
                    $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id=?")->execute([$referralBonus, $referrer['id']]);
                    $conn->prepare("INSERT INTO wallet_transactions (user_id, type, amount, description) VALUES (?, 'referral_bonus', ?, 'Referral bonus')")->execute([$referrer['id'], $referralBonus]);
                }
                $conn->prepare("UPDATE users SET referred_by=? WHERE id=?")->execute([$referrer['id'], $newUserId]);
            } else if ($registrationBonus > 0) {
                $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id=?")->execute([$registrationBonus, $newUserId]);
            }
            
            $_SESSION['userid'] = $newUserId;
            $_SESSION['role']   = 2;
            
            unset($_SESSION['OTP'], $_SESSION['phone'], $_SESSION['ref_code']);
            
            header("Location: /user/details.php");
            exit;
        }
    } else {
        $error = __('Incorrect OTP Entered. Please try again.'); 
        $step = 'verify_otp';
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($current_lang, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, interactive-widget=resizes-content"/>
    <title>Login / Register | Fortune Path</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #fdfdfd; font-family: 'Space Grotesk', sans-serif; transition: background 0.3s ease; }
        
        .auth-container {
            min-height: 100svh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 20px;
            padding-bottom: 60px; 
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0,0,0,0.05);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            transition: all 0.3s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in { animation: fadeIn 0.4s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .tab-btn { transition: all 0.3s ease; }
        .tab-btn.active { background: #000; color: #fff; transform: scale(1.02); }

        @media (max-width: 1023px) {
            html, body { height: auto; overflow-y: auto; }
        }

        /* =========================================
           ENHANCED DARK MODE STYLES
        ========================================= */
        body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
        body.dark-theme .bg-gray-50 { background-color: #121212 !important; }
        body.dark-theme .auth-card { background: rgba(31, 41, 55, 0.9) !important; border-color: #374151 !important; }
        body.dark-theme .text-gray-900, body.dark-theme .text-black, body.dark-theme h2, body.dark-theme h1 { color: #f9fafb !important; }
        body.dark-theme .text-gray-700, body.dark-theme .text-gray-600 { color: #d1d5db !important; }
        body.dark-theme .text-gray-500, body.dark-theme .text-gray-400 { color: #9ca3af !important; }
        
        body.dark-theme .bg-gray-100 { background-color: #374151 !important; border-color: #4b5563 !important; color: #f9fafb !important; }
        body.dark-theme input { background-color: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; }
        body.dark-theme input:focus { border-color: #10b981 !important; }
        
        body.dark-theme .tab-btn.active { background: #10b981 !important; color: #ffffff !important; }
        body.dark-theme .tab-btn { color: #d1d5db !important; }
        
        body.dark-theme .bg-black { background-color: #10b981 !important; color: #ffffff !important; border: none !important; }
        body.dark-theme .bg-black:hover { background-color: #059669 !important; }
        
        body.dark-theme .border-black { border-color: #10b981 !important; color: #10b981 !important; }
        body.dark-theme .hover\:text-black:hover { color: #10b981 !important; }
    </style>
</head>
<body class="overflow-x-hidden">

<script>
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
    }
</script>

<!-- MOBILE VIEW -->
<div class="block lg:hidden">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/user/mobile_header.php'; ?>
    
    <div class="auth-container mt-16">
        <div class="auth-card w-full max-w-sm mx-auto p-8 rounded-[2.5rem] shadow-xl">
            <div class="text-center mb-6">
                <img src="/assets/images/FP%20Logo.png" class="w-16 h-16 mx-auto mb-4 rounded-full shadow-md" alt="Logo"/>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Fortune Path</h1>
            </div>

            <!-- TAB SELECTOR -->
            <?php if($step === 'enter_phone'): ?>
                <div class="flex bg-gray-100 rounded-2xl p-1.5 mb-6 border border-gray-200">
                    <button type="button" class="tab-btn flex-1 py-3 rounded-xl text-sm font-bold <?= $active_tab == 'phone' ? 'active' : '' ?>" data-tab="phone"><?= __('Mobile') ?></button>
                    <button type="button" class="tab-btn flex-1 py-3 rounded-xl text-sm font-bold <?= $active_tab == 'email' ? 'active' : '' ?>" data-tab="email"><?= __('Email') ?></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-600 text-xs font-bold text-center border border-red-100"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if($step === 'enter_phone'): ?>
                <!-- 1. PHONE NUMBER FORM -->
                <form action="" method="POST" class="form-content phone-form <?= $active_tab == 'email' ? 'hidden' : 'fade-in' ?> space-y-4">
                    <label class="block text-sm font-semibold text-gray-700 ml-1"><?= __('Phone Number') ?></label>
                    <div class="flex">
                        <span class="inline-flex items-center px-4 rounded-l-2xl border border-r-0 border-gray-200 bg-gray-100 text-gray-500 text-sm font-bold">+91</span>
                        <input type="tel" name="phone" placeholder="<?= __('Enter mobile number') ?>" pattern="[0-9]{10}" class="w-full px-5 py-4 rounded-r-2xl bg-gray-50 text-sm outline-none border border-transparent focus:border-black transition-all" required />
                    </div>
                    <button type="submit" name="send_otp" class="w-full py-4 mt-2 bg-black text-white rounded-2xl font-bold text-base shadow-lg active:scale-95 transition-all"><?= __('Get OTP →') ?></button>
                </form>

                <!-- 2. EMAIL LOGIN FORM -->
                <form action="" method="POST" class="form-content email-login-form <?= ($active_tab == 'email' && $email_mode == 'login') ? 'fade-in' : 'hidden' ?> space-y-4">
                    <input type="email" name="email" placeholder="<?= __('Email Address') ?>" class="w-full px-5 py-4 rounded-2xl bg-gray-50 text-sm outline-none border border-transparent focus:border-black transition-all" required />
                    <input type="password" name="password" placeholder="<?= __('Password') ?>" class="w-full px-5 py-4 rounded-2xl bg-gray-50 text-sm outline-none border border-transparent focus:border-black transition-all" required />
                    <button type="submit" name="login_email" class="w-full py-4 bg-black text-white rounded-2xl font-bold text-base shadow-lg active:scale-95 transition-all"><?= __('Login →') ?></button>
                    <p class="text-center text-sm text-gray-500 mt-4"><?= __("Don't have an account?") ?> <button type="button" onclick="toggleEmailMode('register')" class="font-bold text-black border-b border-black"><?= __('Sign Up') ?></button></p>
                </form>

                <!-- 3. EMAIL REGISTER FORM -->
                <form action="" method="POST" class="form-content email-register-form <?= ($active_tab == 'email' && $email_mode == 'register') ? 'fade-in' : 'hidden' ?> space-y-4">
                    <input type="email" name="email" placeholder="<?= __('Email Address') ?>" class="w-full px-5 py-4 rounded-2xl bg-gray-50 text-sm outline-none border border-transparent focus:border-black transition-all" required />
                    <input type="password" name="password" placeholder="<?= __('Create Password') ?>" class="w-full px-5 py-4 rounded-2xl bg-gray-50 text-sm outline-none border border-transparent focus:border-black transition-all" required />
                    <input type="password" name="conf_password" placeholder="<?= __('Confirm Password') ?>" class="w-full px-5 py-4 rounded-2xl bg-gray-50 text-sm outline-none border border-transparent focus:border-black transition-all" required />
                    <button type="submit" name="register_email" class="w-full py-4 bg-black text-white rounded-2xl font-bold text-base shadow-lg active:scale-95 transition-all"><?= __('Create Account') ?></button>
                    <p class="text-center text-sm text-gray-500 mt-4"><?= __('Already have an account?') ?> <button type="button" onclick="toggleEmailMode('login')" class="font-bold text-black border-b border-black"><?= __('Login') ?></button></p>
                </form>
            <?php else: ?>
                <!-- OTP VERIFICATION FORM -->
                <form action="" method="POST" class="form-content fade-in space-y-4">
                    <div class="text-center mb-2">
                        <p class="text-sm text-gray-600 font-semibold"><?= __('OTP Sent to :') ?> <span class="text-black">+91 <?php echo htmlspecialchars($_SESSION['phone'], ENT_QUOTES, 'UTF-8');?></span></p>
                    </div>
                    <input type="number" name="otp" placeholder="<?= __('Enter 4-digit Code') ?>" class="w-full px-5 py-4 text-center tracking-widest rounded-2xl bg-gray-50 text-lg font-bold outline-none border border-transparent focus:border-black transition-all" required />
                    <button type="submit" name="verify_otp" class="w-full py-4 mt-2 bg-black text-white rounded-2xl font-bold text-base shadow-lg active:scale-95 transition-all"><?= __('Verify & Login') ?></button>
                    <div class="text-center mt-4">
                        <a href="/auth/login.php" class="text-sm font-bold text-gray-400 hover:text-black transition-colors"><?= __('Change Mobile Number') ?></a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        <div class="h-32 w-full lg:hidden"></div>
    </div>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/user/footer_user.php'; ?>
</div>

<!-- DESKTOP VIEW -->
<div class="hidden lg:block">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/user/web_header.php'; ?>
    <section class="min-h-[90vh] flex items-center justify-center bg-gray-50 py-12 px-6">
        <div class="max-w-6xl w-full grid grid-cols-2 gap-12 items-center">
            <div class="p-12">
                <div class="inline-block px-4 py-1.5 rounded-full bg-gray-200 text-xs font-bold uppercase tracking-wider mb-6">✦ <?= __('Secure Portal') ?></div>
                <h2 class="text-6xl font-black text-gray-900 leading-[1.1] mb-6"><?= __('Unlock Your') ?> <br><span class="text-gray-400"><?= __('Cosmic Path.') ?></span></h2>
                <p class="text-lg text-gray-600 leading-relaxed max-w-md"><?= __('Enter your mobile number or email to instantly access your dashboard and consult with experts.') ?></p>
            </div>

            <div class="auth-card bg-white p-12 rounded-[3.5rem] shadow-2xl border border-gray-100">
                
                <!-- TAB SELECTOR -->
                <?php if($step === 'enter_phone'): ?>
                    <div class="flex bg-gray-100 rounded-2xl p-1.5 mb-8 border border-gray-200">
                        <button type="button" class="tab-btn flex-1 py-4 rounded-xl font-bold <?= $active_tab == 'phone' ? 'active' : '' ?>" data-tab="phone"><?= __('Continue with Mobile') ?></button>
                        <button type="button" class="tab-btn flex-1 py-4 rounded-xl font-bold <?= $active_tab == 'email' ? 'active' : '' ?>" data-tab="email"><?= __('Email') ?></button>
                    </div>
                <?php else: ?>
                    <h3 class="text-2xl font-bold mb-8"><?= __('Verify OTP') ?></h3>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="mb-4 p-4 rounded-xl bg-red-50 text-red-600 text-sm font-bold text-center border border-red-100"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <?php if($step === 'enter_phone'): ?>
                    <!-- 1. PHONE NUMBER FORM -->
                    <form action="" method="POST" class="form-content phone-form <?= $active_tab == 'email' ? 'hidden' : 'fade-in' ?> space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2"><?= __('Phone Number') ?></label>
                            <div class="flex shadow-sm rounded-2xl">
                                <span class="inline-flex items-center px-5 rounded-l-2xl border border-r-0 border-gray-200 bg-gray-50 text-gray-500 text-base font-bold">+91</span>
                                <input type="tel" name="phone" placeholder="<?= __('Enter mobile number') ?>" pattern="[0-9]{10}" class="w-full px-6 py-5 rounded-r-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required />
                            </div>
                        </div>
                        <button type="submit" name="send_otp" class="w-full py-5 bg-black text-white rounded-2xl font-black text-lg hover:bg-gray-800 transition-all shadow-xl"><?= __('Get OTP →') ?></button>
                    </form>

                    <!-- 2. EMAIL LOGIN FORM -->
                    <form action="" method="POST" class="form-content email-login-form <?= ($active_tab == 'email' && $email_mode == 'login') ? 'fade-in' : 'hidden' ?> space-y-6">
                        <input type="email" name="email" placeholder="<?= __('Email Address') ?>" class="w-full px-6 py-5 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required />
                        <input type="password" name="password" placeholder="<?= __('Password') ?>" class="w-full px-6 py-5 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required />
                        <button type="submit" name="login_email" class="w-full py-5 bg-black text-white rounded-2xl font-black text-lg hover:bg-gray-800 transition-all shadow-xl"><?= __('Secure Login') ?></button>
                        <p class="text-center text-gray-500 mt-4"><?= __("Don't have an account?") ?> <button type="button" onclick="toggleEmailMode('register')" class="font-bold text-black border-b border-black"><?= __('Create Account') ?></button></p>
                    </form>

                    <!-- 3. EMAIL REGISTER FORM -->
                    <form action="" method="POST" class="form-content email-register-form <?= ($active_tab == 'email' && $email_mode == 'register') ? 'fade-in' : 'hidden' ?> space-y-6">
                        <input type="email" name="email" placeholder="<?= __('Email Address') ?>" class="w-full px-6 py-5 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required />
                        <input type="password" name="password" placeholder="<?= __('Create Password') ?>" class="w-full px-6 py-5 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required />
                        <input type="password" name="conf_password" placeholder="<?= __('Confirm Password') ?>" class="w-full px-6 py-5 rounded-2xl bg-gray-50 text-base outline-none border border-gray-200 focus:border-black transition-all" required />
                        <button type="submit" name="register_email" class="w-full py-5 bg-black text-white rounded-2xl font-black text-lg hover:bg-gray-800 transition-all shadow-xl"><?= __('Join Fortune Path') ?></button>
                        <p class="text-center text-gray-500 mt-4"><?= __('Already have an account?') ?> <button type="button" onclick="toggleEmailMode('login')" class="font-bold text-black border-b border-black"><?= __('Log In') ?></button></p>
                    </form>

                <?php else: ?>
                    <!-- OTP VERIFICATION FORM -->
                    <form action="" method="POST" class="form-content fade-in space-y-6">
                        <div class="bg-gray-50 p-4 rounded-2xl text-center border border-gray-100 mb-4">
                            <p class="text-sm text-gray-500 font-bold uppercase tracking-wider"><?= __('OTP Sent to :') ?></p>
                            <p class="text-xl text-black font-black mt-1">+91 <?php echo htmlspecialchars($_SESSION['phone'], ENT_QUOTES, 'UTF-8');?></p>
                        </div>
                        <input type="number" name="otp" placeholder="<?= __('Enter 4-digit Code') ?>" class="w-full px-6 py-5 tracking-widest text-center rounded-2xl bg-gray-50 text-xl font-black outline-none border border-gray-200 focus:border-black transition-all" required />
                        <button type="submit" name="verify_otp" class="w-full py-5 bg-black text-white rounded-2xl font-black text-lg hover:bg-gray-800 transition-all shadow-xl"><?= __('Verify & Login') ?></button>
                        <div class="text-center mt-4">
                            <a href="/auth/login.php" class="text-sm font-bold text-gray-400 hover:text-black transition-colors border-b border-transparent hover:border-black pb-1"><?= __('Entered wrong number? Change here.') ?></a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/user/web_footer.php'; ?>
</div>

<script>
// Logic for switching Top Tabs (Mobile vs Email)
document.addEventListener("DOMContentLoaded", function() {
    const allTabBtns = document.querySelectorAll('.tab-btn');
    
    allTabBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            const mode = btn.getAttribute('data-tab');
            
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove("active"));
            document.querySelectorAll(`.tab-btn[data-tab="${mode}"]`).forEach(b => b.classList.add("active"));
            
            if (mode === 'phone') {
                document.querySelectorAll('.phone-form').forEach(f => {
                    f.classList.remove('hidden');
                    f.classList.add('fade-in');
                });
                document.querySelectorAll('.email-login-form, .email-register-form').forEach(f => f.classList.add('hidden'));
            } else {
                // When switching to email, default to login form
                document.querySelectorAll('.email-login-form').forEach(f => {
                    f.classList.remove('hidden');
                    f.classList.add('fade-in');
                });
                document.querySelectorAll('.phone-form, .email-register-form').forEach(f => f.classList.add('hidden'));
            }
        });
    });
});

// Logic for switching between Email Login and Email Register
function toggleEmailMode(mode) {
    if (mode === 'register') {
        document.querySelectorAll('.email-login-form').forEach(f => f.classList.add('hidden'));
        document.querySelectorAll('.email-register-form').forEach(f => {
            f.classList.remove('hidden');
            f.classList.add('fade-in');
        });
    } else {
        document.querySelectorAll('.email-register-form').forEach(f => f.classList.add('hidden'));
        document.querySelectorAll('.email-login-form').forEach(f => {
            f.classList.remove('hidden');
            f.classList.add('fade-in');
        });
    }
}
</script>
</body>
</html>
