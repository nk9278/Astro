<!-- admin/footer_admin.php -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<div class="footer mobile-footer font-space">

  <a href="/admin/dashboard.php" class="footer-item">
    <i class="fa fa-gauge"></i>
    <span class="footer-text">Dashboard</span>
  </a>

  <!-- ⭐ WALLET MENU -->
  <a href="/admin/wallet.php" class="footer-item">
    <i class="fa fa-wallet"></i>
    <span class="footer-text">Wallet</span>
  </a>

  <a href="/admin/astrologers.php" class="footer-item">
    <i class="fa fa-star"></i>
    <span class="footer-text">Astrologers</span>
  </a>

  <a href="/admin/reports.php" class="footer-item">
    <i class="fa fa-file"></i>
    <span class="footer-text">Reports</span>
  </a>

  <!-- ⭐ SETTINGS REMOVED — UPLOAD ADDED -->
  <a href="/admin/upload.php" class="footer-item">
    <i class="fa fa-upload"></i>
    <span class="footer-text">Upload</span>
  </a>

</div>

<style>
/* Font Integration */
.font-space {
    font-family: 'Space Grotesk', sans-serif;
}

/* Safe area for modern mobile devices (iPhones with home indicator) */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .mobile-footer {
        padding-bottom: calc(0.6rem + env(safe-area-inset-bottom)) !important;
    }
}

.mobile-footer {
    display: flex;
    justify-content: space-around;
    align-items: center;
    position: fixed;
    left: 0;
    bottom: 0;
    width: 100%;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(12px);
    padding: 0.8rem 0 0.6rem 0;
    border-top: 1px solid #f3f4f6;
    z-index: 999;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.03);
    transition: all 0.3s ease;
}

.footer-item {
    flex: 1;
    text-align: center;
    color: #9ca3af; /* Gray-400 */
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    transition: all 0.2s ease;
    padding: 0 4px;
    min-width: 60px;
}

.footer-item i {
    font-size: 22px;
    transition: transform 0.2s ease, color 0.2s ease;
}

.footer-text {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.02em;
}

/* ACTIVE + HOVER EFFECT (Red Theme) */
.footer-item.active i,
.footer-item:hover i {
    color: #dc2626; /* Tailwind Red-600 */
    transform: translateY(-2px);
}

.footer-item.active .footer-text,
.footer-item:hover .footer-text {
    color: #dc2626;
}

/* =========================================
   ENHANCED DARK MODE STYLES
========================================= */
body.dark-theme .mobile-footer {
    background: rgba(31, 41, 55, 0.95) !important; /* Gray-800 */
    border-top-color: #374151 !important; /* Gray-700 */
}

body.dark-theme .footer-item {
    color: #6b7280 !important; /* Gray-500 */
}

body.dark-theme .footer-item.active i,
body.dark-theme .footer-item:hover i {
    color: #ef4444 !important; /* Tailwind Red-500 */
}

body.dark-theme .footer-item.active .footer-text,
body.dark-theme .footer-item:hover .footer-text {
    color: #ef4444 !important;
}
</style>

<script>
    // Automatically highlight the active tab based on the current URL
    document.addEventListener('DOMContentLoaded', () => {
        const currentPath = window.location.pathname;
        const footerItems = document.querySelectorAll('.footer-item');
        
        footerItems.forEach(item => {
            const href = item.getAttribute('href');
            // Check if current URL matches the href link
            if (currentPath.includes(href) && href !== '/') {
                item.classList.add('active');
            }
        });
    });
</script>