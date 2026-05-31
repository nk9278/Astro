<?php
// Enforce Core PHP & Hardened Security Standards

/* ============================
   BULLETPROOF LOCALIZATION (Bypass Method)
============================ */
$ah_lang = isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'hi' : 'en';
$ah_translations = [
    'en' => [],
    'hi' => [
        'Wallet' => 'वॉलेट',
        'Commission History' => 'कमीशन हिस्ट्री',
        'Logout' => 'लॉग आउट',
        'Incoming Call' => 'आ रही कॉल',
        'Accept' => 'स्वीकार करें',
        'Reject' => 'अस्वीकार करें'
    ]
];

if (!function_exists('__ah')) {
    function __ah($key) {
        global $ah_translations, $ah_lang;
        return isset($ah_translations[$ah_lang][$key]) ? $ah_translations[$ah_lang][$key] : $key;
    }
}

$astroName = "Astrologer";
if (isset($_SESSION['userid'])) {
    require_once __DIR__ . '/../db.php';
    try {
        $stmt = $conn->prepare("SELECT astrologer_name FROM users WHERE id=? LIMIT 1");
        $stmt->execute([$_SESSION['userid']]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($u && !empty($u['astrologer_name'])) {
            $astroName = htmlspecialchars($u['astrologer_name'], ENT_QUOTES, 'UTF-8');
        }
    } catch (PDOException $e) {
        error_log("Database Error in Astrologer Header: " . $e->getMessage());
    }
}
?>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
.mobile-header  { z-index: 999990 !important; }
#sideMenu       { z-index: 999995 !important; }
html, body { overflow-x: hidden; }

/* Dark Mode Core Overrides - Dark Gray Theme */
body.dark-theme { background: #121212 !important; color: #e5e7eb !important; }
body.dark-theme .mobile-header { background: #1f2937 !important; border-bottom: 1px solid #374151 !important; box-shadow: none !important; }
body.dark-theme #sideMenu { background: #1f2937 !important; box-shadow: 2px 0 10px rgba(0,0,0,0.5) !important; }
body.dark-theme #sideMenu > div, 
body.dark-theme #sideMenu a, 
body.dark-theme .mobile-header span { color: #e5e7eb !important; }
body.dark-theme #sideMenu li { border-color: #374151 !important; }

body.dark-theme #langToggleBtnAstro, body.dark-theme #themeToggleAstro { background: #374151 !important; border-color: #4b5563 !important; color: #f3f4f6 !important; }
body.dark-theme #langToggleBtnAstro span, body.dark-theme #themeToggleAstro span { color: #f3f4f6 !important; }
</style>

<div class="mobile-header" style="
  position: fixed; top: 0; left: 0;
  width: 100%; height: 60px;
  display: flex; align-items: center;
  background: #ffffff;
  padding: 0 15px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  z-index: 1001;">
    
    <div id="openMenu" style="
        width:40px;height:40px;
        display:flex;justify-content:center;
        align-items:center;cursor:pointer;">
        <img src="/assets/images/FP Logo.png" style="width:100%;height:100%;object-fit:contain; border-radius:50%;" alt="Menu">
    </div>

    <div style="flex:1;"></div>

    <div style="display:flex; justify-content: flex-end; align-items: center; gap: 12px;">

        <button id="langToggleBtnAstro" style="background:#f3f4f6; border:1px solid #e5e7eb; padding:6px 10px; border-radius:8px; cursor:pointer; display:flex; align-items:center; gap:6px; color:#4b5563; font-weight:700; font-size:13px; transition: all 0.2s;">
            <span class="material-icons" style="font-size:16px;">language</span>
            <span id="langTextAstro"><?= isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'hi' ? 'HI' : 'EN' ?></span>
        </button>

        <button id="themeToggleAstro" style="background:#f3f4f6; border:1px solid #e5e7eb; width:34px; height:34px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#4b5563; transition: all 0.2s;">
            <span class="material-icons" id="themeIconAstro" style="font-size:18px;">dark_mode</span>
        </button>

    </div>
</div>

<div style="height:60px;"></div>

<div id="overlay" style="
  position: fixed; top:0; left:0;
  width:100%; height:100vh;
  background: rgba(0,0,0,0.35);
  display:none; z-index:1001;"></div>

<div id="sideMenu" style="
  position: fixed; top: 0; left: -270px;
  width: 270px; height: 100vh;
  background: #ffffff;
  box-shadow: 2px 0 8px rgba(0,0,0,0.25);
  transition: left 0.3s ease;
  padding: 22px;
  box-sizing: border-box;
  z-index: 999995 !important;
  overflow-y:auto;">

    <div style="margin-bottom:20px;">
        <img src="/assets/images/FP Logo.png"
             style="width:45px;height:45px;object-fit:contain;border-radius:999px;" alt="Logo">
    </div>

    <div style="font-size:19px;font-weight:700;color:#222;margin-bottom:25px;text-align:left;">
        <?= $astroName ?>
    </div>

    <ul style="list-style:none;padding:0;margin:0;text-align:left;">
        <li style="margin-bottom:22px;">
            <a href="../astrologer/wallet.php" style="color:#333;font-weight:600;font-size:16px;text-decoration:none;"><?= __ah('Wallet') ?></a>
        </li>
        <li style="margin-bottom:22px;">
            <a href="../astrologer/commission_history.php" style="color:#333;font-weight:600;font-size:16px;text-decoration:none;"><?= __ah('Commission History') ?></a>
        </li>
        <li style="margin-top:35px; padding-top:20px; border-top:1px solid #e5e5e5;">
            <a href="/logout.php" style="color:#d00; font-weight:600; font-size:17px; text-decoration:none;"><?= __ah('Logout') ?></a>
        </li>
    </ul>

</div>

<script>
if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-theme');
}

document.addEventListener("DOMContentLoaded", function () {
    // --- THEME TOGGLE LOGIC ---
    const themeToggle = document.getElementById("themeToggleAstro");
    const themeIcon = document.getElementById("themeIconAstro");

    if (document.body.classList.contains('dark-theme')) {
        themeIcon.textContent = 'light_mode';
    }

    themeToggle.addEventListener("click", () => {
        document.body.classList.toggle('dark-theme');
        const isDark = document.body.classList.contains('dark-theme');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        themeIcon.textContent = isDark ? 'light_mode' : 'dark_mode';
    });

    // --- CUSTOM LANGUAGE TOGGLE LOGIC ---
    const langBtn = document.getElementById("langToggleBtnAstro");
    const langText = document.getElementById("langTextAstro");

    langBtn.addEventListener("click", () => {
        let newLang = langText.textContent === "EN" ? "hi" : "en";
        document.cookie = `lang=${newLang}; max-age=31536000; path=/`;
        location.reload(); 
    });

    // --- MENU LOGIC ---
    const openMenu = document.getElementById("openMenu");
    const sideMenu = document.getElementById("sideMenu");
    const overlay = document.getElementById("overlay");

    function openSide() {
        sideMenu.style.left = "0";
        overlay.style.display = "block";
        document.body.style.overflow = "hidden";
    }

    function closeSide() {
        sideMenu.style.left = "-270px";
        overlay.style.display = "none";
        document.body.style.overflow = "auto";
    }

    openMenu.addEventListener("click", openSide);
    overlay.addEventListener("click", closeSide);
});
</script>

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
                    block: 'center' 
                });
            }, 300);
        });
    });
});
</script>