<?php
require __DIR__ . '/../db.php';

// Banner fetch from database
$stmtBanner = $conn->query("SELECT * FROM banners ORDER BY id DESC LIMIT 1");
$banner = $stmtBanner->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Promo Banner Card</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />

<!-- Tailwind & Fonts -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;900&display=swap" rel="stylesheet" />

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: transparent; margin: 0; }
    
    /* Dark Mode Overrides */
    body.dark-theme .promo-card { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme .promo-title { color: #f9fafb !important; }
    body.dark-theme .promo-sub { color: #9ca3af !important; }
    body.dark-theme .promo-img-wrapper { background: #374151 !important; border-color: #4b5563 !important; }
    body.dark-theme .promo-btn { background: #dc2626 !important; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.2) !important; }
    body.dark-theme .promo-btn:hover { background: #b91c1c !important; }
</style>
</head>
<body>

<script>
    // Automatically apply dark mode if the parent window or local storage has it enabled
    if (localStorage.getItem('theme') === 'dark' || (window.parent && window.parent.document.body.classList.contains('dark-theme'))) {
        document.body.classList.add('dark-theme');
    }
</script>

<div class="max-w-[460px] mx-auto px-4 sm:px-0 my-6">
<?php if ($banner): ?>
    <div class="promo-card bg-white rounded-[1.5rem] p-4 sm:p-5 flex items-center justify-between border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.04)] hover:shadow-[0_8px_25px_rgba(220,38,38,0.15)] transition-all duration-300 cursor-pointer group" 
         onclick="window.location.href='<?= htmlspecialchars($banner['link'], ENT_QUOTES, 'UTF-8') ?>';" 
         role="button" 
         tabindex="0" 
         onkeypress="if(event.key === 'Enter'){ window.location.href='<?= htmlspecialchars($banner['link'], ENT_QUOTES, 'UTF-8') ?>'; }">
        
        <div class="flex-1 pr-4">
            <h3 class="promo-title text-base sm:text-lg font-black text-gray-900 leading-tight mb-1.5 tracking-tight">
                <?= htmlspecialchars($banner['main_text'], ENT_QUOTES, 'UTF-8') ?>
            </h3>
            <p class="promo-sub text-xs sm:text-sm font-medium text-gray-500 mb-4 leading-snug">
                <?= htmlspecialchars($banner['sub_text'], ENT_QUOTES, 'UTF-8') ?>
            </p>
            <button class="promo-btn bg-red-600 text-white px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-red-600/20 group-hover:bg-red-700 transition-all active:scale-95">
                <?= htmlspecialchars($banner['button_text'], ENT_QUOTES, 'UTF-8') ?>
            </button>
        </div>
        
        <div class="promo-img-wrapper w-[90px] h-[90px] sm:w-[110px] sm:h-[110px] shrink-0 rounded-[1.25rem] overflow-hidden bg-gray-50 border border-gray-100 shadow-inner">
            <img src="/uploads/banners/<?= htmlspecialchars($banner['image'], ENT_QUOTES, 'UTF-8') ?>" 
                 alt="Promo Banner" 
                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
        </div>
        
    </div>
<?php endif; ?>
</div>

</body>
</html>