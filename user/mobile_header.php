<?php
/**
 * Mobile Header - Fortune Path Way
 * Includes Sidebar Navigation, Session Logic, Submenus & Custom Localization
 * Enforces Hardened Security & Core PHP Standards
 */

// --- 1. BULLETPROOF LOCALIZATION (Bypass Method) ---
$mobile_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
$mobile_translations = [
    'hi' => [
        'Guest' => 'अतिथि',
        'Welcome,' => 'स्वागत है,',
        'Our Services' => 'हमारी सेवाएं',
        'Astrology' => 'ज्योतिष',
        'Vastu' => 'वास्तु',
        'Numerology' => 'अंक ज्योतिष',
        'Spiritual Store' => 'आध्यात्मिक स्टोर',
        'Account' => 'अकाउंट',
        'My Profile' => 'मेरी प्रोफ़ाइल',
        'My Orders' => 'मेरे ऑर्डर',
        'Call History' => 'कॉल हिस्ट्री',
        'Wallet & Payments' => 'वॉलेट और भुगतान',
        'Help Support' => 'सहायता और समर्थन',
        'Legal Policies' => 'कानूनी नीतियां',
        'Terms & Conditions' => 'नियम और शर्तें',
        'Privacy Policy' => 'गोपनीयता नीति',
        'Refund & Cancellation' => 'धनवापसी और रद्दीकरण',
        'Shipping & Returns' => 'शिपिंग और वापसी',
        'Disclaimer' => 'अस्वीकरण',
        'Logout' => 'लॉग आउट',
        'Login / Sign Up' => 'लॉग इन / साइन अप',
        'Become an Astrologer?' => 'ज्योतिषी बनें?' 
    ]
];

function __m($key) {
    global $mobile_translations, $mobile_lang;
    return isset($mobile_translations[$mobile_lang][$key]) ? $mobile_translations[$mobile_lang][$key] : $key;
}

// 2. High-Security Session & Database Logic
$username = __m('Guest');
$isLoggedIn = false;

if (isset($_SESSION['userid'])) {
    require_once __DIR__ . '/../db.php'; 
    
    try {
        $stmt = $conn->prepare("SELECT name FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['userid']]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($u) {
            // --- STRICT SERVER-SIDE PROFILE LOCK ---
            if (isset($_SESSION['role']) && $_SESSION['role'] == 2) {
                $current_page = basename($_SERVER['PHP_SELF']);
                if (empty($u['name']) && $current_page !== 'details.php') {
                    header("Location: /user/details.php");
                    exit;
                }
            }
            // ---------------------------------------
            // Escaping output to prevent XSS (High Security Standard)
            $username = !empty($u['name']) ? htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') : __m('Guest');
            $isLoggedIn = true;
        }
    } catch (PDOException $e) {
        $username = __m('Guest'); 
        error_log("Database Error in Mobile Header: " . $e->getMessage());
    }
} 
?>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
    /* Dark Mode Core Overrides */
    body.dark-theme { background: #121212 !important; color: #e5e7eb !important; }
    body.dark-theme .mobile-header { background: #1f2937 !important; border-bottom: 1px solid #374151 !important; box-shadow: none !important; }
    body.dark-theme #sideMenu { background: #1f2937 !important; box-shadow: 2px 0 10px rgba(0,0,0,0.5) !important; }
    body.dark-theme #sideMenu > div { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme .nav-item, 
    body.dark-theme #sideMenu div, 
    body.dark-theme .mobile-header span { color: #e5e7eb !important; }
    body.dark-theme .nav-item:active { background: #374151 !important; }
    
    body.dark-theme #langToggleBtn, body.dark-theme #themeToggleUser { background: #374151 !important; border-color: #4b5563 !important; color: #f3f4f6 !important; }
    body.dark-theme #langToggleBtn span, body.dark-theme #themeToggleUser span { color: #f3f4f6 !important; }
    
    body.dark-theme .dark-text-override { color: #e5e7eb !important; }
    body.dark-theme .dark-bg-override { background: #374151 !important; color: #ffffff !important; }
    
    /* Submenu Dark Mode Overrides */
    body.dark-theme .sub-nav-item { color: #d1d5db !important; }
    body.dark-theme .sub-nav-item:active { background: #374151 !important; color: #ffffff !important; }
    body.dark-theme #legalSubmenu { background: #1f2937 !important; border-left-color: #4b5563 !important; }

    /* Reduced Font Sizes & Spacing */
    .nav-heading {
        padding: 0 20px; 
        font-size: 10px; 
        font-weight: 700; 
        color: #9ca3af; 
        text-transform: uppercase; 
        letter-spacing: 1px; 
        margin-bottom: 4px; 
    }
    
    .nav-divider {
        margin: 10px 20px; 
        border-top: 1px solid #f3f4f6;
    }

    .nav-item {
        display: flex; align-items: center; 
        padding: 8px 20px; 
        color: #374151; text-decoration: none; 
        font-size: 14px; 
        font-weight: 500; gap: 12px; transition: background 0.2s;
    }
    .nav-item:active { background: #f3f4f6; }
    .nav-item .material-icons { font-size: 18px; color: #9ca3af; } 

    /* Submenu Styles */
    .sub-nav-item {
        display: block; 
        padding: 8px 20px; 
        color: #4b5563; text-decoration: none; 
        font-size: 13px; 
        font-weight: 500; transition: all 0.2s;
    }
    .sub-nav-item:active { color: #111827; background: #f3f4f6; }
</style>

<div class="mobile-header lg:hidden" style="
    position: fixed; top: 0; left: 0;
    width: 100%; height: 60px;
    display: flex; align-items: center;
    background: #ffffff;
    padding: 0 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 1001;">
    
    <div id="openMenu" style="cursor:pointer; display:flex; align-items:center; width:40px; height:40px;">
        <img src="/assets/images/FP Logo.png" style="width:100%; height:100%; object-fit:contain; border-radius:50%;" alt="Menu">
    </div>

    <div style="flex:1;"></div>

    <div style="display:flex; justify-content: flex-end; align-items: center; gap: 12px;">
        <button id="langToggleBtn" style="background:#f3f4f6; border:1px solid #e5e7eb; padding:6px 10px; border-radius:8px; cursor:pointer; display:flex; align-items:center; gap:6px; color:#4b5563; font-weight:700; font-size:13px; transition: all 0.2s;">
            <span class="material-icons" style="font-size:16px;">language</span>
            <span id="langText"><?= isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'HI' : 'EN' ?></span>
        </button>

        <button id="themeToggleUser" style="background:#f3f4f6; border:1px solid #e5e7eb; width:34px; height:34px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#4b5563; transition: all 0.2s;">
            <span class="material-icons" id="themeIconUser" style="font-size:18px;">dark_mode</span>
        </button>
    </div>
</div>

<div class="lg:hidden" style="height:60px;"></div>

<div id="sideOverlay" style="
    position: fixed; top:0; left:0;
    width:100%; height:100vh;
    background: rgba(0,0,0,0.5);
    display:none; z-index:1002;
    backdrop-filter: blur(2px);"></div>

<div id="sideMenu" style="
    position: fixed; top: 0; left: -280px;
    width: 280px; height: 100vh;
    background: #ffffff;
    box-shadow: 5px 0 15px rgba(0,0,0,0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index:1003;
    overflow-y:auto;
    display: flex;
    flex-direction: column;">

    <div style="padding: 20px 20px; background: #f9fafb; border-bottom: 1px solid #f3f4f6;">
        <img src="/assets/images/FP Logo.png" style="width:45px; height:45px; border-radius:50%; margin-bottom:10px; border: 2px solid #e5e7eb;" alt="Logo">
        <div style="font-size:12px; color:#6b7280; font-weight:500;"><?= __m('Welcome,') ?></div>
        <div style="font-size:18px; font-weight:800; color:#111827;"><?= $username ?></div>
    </div>

    <div style="padding: 10px 0; flex-grow: 1;">
        <nav style="display:flex; flex-direction:column;">
            
            <p class="nav-heading"><?= __m('Our Services') ?></p>
            
            <a href="/services/astrology.php" class="nav-item">
                <span class="material-icons">auto_awesome</span> <?= __m('Astrology') ?>
            </a>
            <a href="/services/vastu.php" class="nav-item">
                <span class="material-icons">home</span> <?= __m('Vastu') ?>
            </a>
            <a href="/services/numerology.php" class="nav-item">
                <span class="material-icons">pin</span> <?= __m('Numerology') ?>
            </a>
            <a href="/products/list.php" class="nav-item dark-text-override" style="color: #374151; font-weight: 600;">
                <span class="material-icons">shopping_bag</span> <?= __m('Spiritual Store') ?>
            </a>

            <?php if ($isLoggedIn): ?>
                <div class="nav-divider"></div>

                <p class="nav-heading"><?= __m('Account') ?></p>

                <a href="/user/profile.php" class="nav-item">
                    <span class="material-icons">person_outline</span> <?= __m('My Profile') ?>
                </a>
                
                <a href="/user/orders.php" class="nav-item">
                    <span class="material-icons">local_shipping</span> <?= __m('My Orders') ?>
                </a>

                <a href="/user/call_history.php" class="nav-item">
                    <span class="material-icons">history</span> <?= __m('Call History') ?>
                </a>
                <a href="/user/wallet.php" class="nav-item">
                    <span class="material-icons">payments</span> <?= __m('Wallet & Payments') ?>
                </a>
                <a href="/user/support.php" class="nav-item">
                    <span class="material-icons">help_outline</span> <?= __m('Help Support') ?>
                </a>
                <a href="/logout.php" class="nav-item">
                    <span class="material-icons" style="color: #ef4444;">logout</span> <span style="color: #ef4444; font-weight: 600;"><?= __m('Logout') ?></span>
                </a>
            <?php endif; ?>

            <div class="nav-divider"></div>
            
            <p class="nav-heading"><?= __m('Legal Policies') ?></p>

            <div class="nav-item" id="legalMenuToggle" style="cursor: pointer; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="material-icons">gavel</span> <?= __m('Terms & Conditions') ?>
                </div>
                <span class="material-icons" id="legalMenuIcon" style="transition: transform 0.3s; font-size: 20px;">expand_more</span>
            </div>

            <div id="legalSubmenu" style="display: none; background: #f9fafb; border-left: 2px solid #e5e7eb; margin-left: 25px; padding: 5px 0;">
                <a href="/assets/t%26c/terms.php" class="sub-nav-item"><?= __m('Terms & Conditions') ?></a>
                <a href="/assets/t%26c/policy.php" class="sub-nav-item"><?= __m('Privacy Policy') ?></a>
                <a href="/assets/t%26c/Refund-Cancellation-Policy.php" class="sub-nav-item"><?= __m('Refund & Cancellation') ?></a>
                <a href="/assets/t%26c/shipping.php" class="sub-nav-item"><?= __m('Shipping & Returns') ?></a>
                <a href="/assets/t%26c/disclaimer.php" class="sub-nav-item"><?= __m('Disclaimer') ?></a>
            </div>

            <div class="nav-divider"></div>

            <a href="/astrologer/join.php" class="nav-item dark-text-override" style="color: #059669; font-weight: 600;">
                <span class="material-icons">stars</span> <?= __m('Become an Astrologer?') ?>
            </a>

        </nav>
    </div>

    <?php if (!$isLoggedIn): ?>
    <div style="padding: 15px 20px; border-top: 1px solid #f3f4f6;">
        <a href="/auth/login.php" class="dark-bg-override" style="display:flex; align-items:center; justify-content:center; background:#374151; color:#fff; padding:10px; border-radius:8px; font-weight:600; font-size: 14px; text-decoration:none; box-shadow: 0 4px 6px -1px rgba(55, 65, 81, 0.2);">
            <?= __m('Login / Sign Up') ?>
        </a>
    </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
    // Apply on initial load for both custom CSS and Tailwind CSS
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-theme');
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    document.addEventListener("DOMContentLoaded", function () {
        // Theme Toggle Logic
        const themeToggle = document.getElementById("themeToggleUser");
        const themeIcon = document.getElementById("themeIconUser");

        if (document.body.classList.contains('dark-theme')) {
            themeIcon.textContent = 'light_mode';
        }

        themeToggle.addEventListener("click", () => {
            // Toggle both the body class (Custom CSS) and html class (Tailwind)
            document.body.classList.toggle('dark-theme');
            document.documentElement.classList.toggle('dark');

            const isDark = document.body.classList.contains('dark-theme');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            themeIcon.textContent = isDark ? 'light_mode' : 'dark_mode';
        });

        // Language Toggle Logic
        const langBtn = document.getElementById("langToggleBtn");
        const langText = document.getElementById("langText");

        langBtn.addEventListener("click", () => {
            let newLang = langText.textContent === "EN" ? "hi" : "en";
            document.cookie = `lang=${newLang}; max-age=31536000; path=/`;
            location.reload(); 
        });

        // Sidebar Navigation Logic
        const openBtn = document.getElementById("openMenu");
        const sideMenu = document.getElementById("sideMenu");
        const overlay = document.getElementById("sideOverlay");

        function toggleMenu(isOpen) {
            if (isOpen) {
                sideMenu.style.left = "0";
                overlay.style.display = "block";
                document.body.style.overflow = "hidden"; 
            } else {
                sideMenu.style.left = "-280px";
                overlay.style.display = "none";
                document.body.style.overflow = "auto";
            }
        }

        if (openBtn) openBtn.addEventListener("click", () => toggleMenu(true));
        if (overlay) overlay.addEventListener("click", () => toggleMenu(false));

        // Submenu Accordion Logic
        const legalToggle = document.getElementById("legalMenuToggle");
        const legalSubmenu = document.getElementById("legalSubmenu");
        const legalIcon = document.getElementById("legalMenuIcon");

        if (legalToggle) {
            legalToggle.addEventListener("click", () => {
                const isHidden = legalSubmenu.style.display === "none";
                legalSubmenu.style.display = isHidden ? "block" : "none";
                legalIcon.style.transform = isHidden ? "rotate(180deg)" : "rotate(0deg)";
            });
        }
    });
</script>

<style>
    /* Ensures the input doesn't hide behind the 60px fixed header when scrolled to the top */
    input, textarea, select {
        scroll-margin-top: 80px; 
    }
</style>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Select all input fields, text areas, and dropdowns
    const formElements = document.querySelectorAll('input, textarea, select');
    
    formElements.forEach((element) => {
        element.addEventListener('focus', function() {
            // A 300ms delay gives the mobile OS keyboard enough time to pop up 
            // before calculating the new scroll position.
            setTimeout(() => {
                this.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' // Pushes the element higher up the screen
                });
            }, 300);
        });
    });
});
</script>