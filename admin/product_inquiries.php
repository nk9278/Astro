<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /auth/login.php');
    exit;
}

$message = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $message = "Inquiry deleted successfully.";
}

// Fetch inquiries (mobile-first fields only)
$stmt = $conn->query("
    SELECT 
        pi.id,
        pi.message,
        pi.created_at,
        u.name  AS user_name,
        u.email AS user_email,
        u.phone AS user_phone,
        p.name  AS product_name,
        p.price AS product_price,
        p.image AS product_image
    FROM product_inquiries pi
    JOIN users u   ON pi.user_id = u.id
    JOIN products p ON pi.product_id = p.id
    ORDER BY pi.created_at DESC
");
$inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Header include
include '../admin/admin_header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Product Inquiries - Admin</title>
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no" />

<!-- Tailwind & Fonts -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;900&display=swap" rel="stylesheet" />

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #fdfdfd; color: #1a1a1a; transition: background 0.3s ease; }
    
    .status-banner { animation: fadeInDown 0.4s ease-out; }
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* =========================================
       ENHANCED DARK MODE STYLES
    ========================================= */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .bg-white { background: #1f2937 !important; border-color: #374151 !important; }
    
    body.dark-theme .text-gray-900 { color: #ffffff !important; }
    body.dark-theme .text-gray-800 { color: #f3f4f6 !important; }
    body.dark-theme .text-gray-700 { color: #d1d5db !important; }
    body.dark-theme .text-gray-600 { color: #9ca3af !important; }
    body.dark-theme .text-gray-500 { color: #6b7280 !important; }
    
    body.dark-theme .bg-gray-50 { background: #374151 !important; border-color: #4b5563 !important; }
    body.dark-theme .border-gray-200 { border-color: #374151 !important; }
    body.dark-theme .border-gray-100 { border-color: #4b5563 !important; }
    
    body.dark-theme .border-red-200 { border-color: #7f1d1d !important; }
    body.dark-theme .bg-red-50 { background: rgba(127, 29, 29, 0.2) !important; color: #f87171 !important; }
</style>
<script>
function confirmDelete(id){
  if (confirm('Delete this inquiry permanently?')) {
    window.location.href = 'product_inquiries_delete.php?delete=' + id;
  }
}
</script>
</head>

<body class="pb-12">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 mt-8">
    
    <a href="/admin/dashboard.php" class="text-sm font-bold text-gray-500 hover:text-red-600 transition-colors mb-4 inline-block">&larr; Back to Dashboard</a>
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Product Inquiries</h1>
            <p class="text-gray-500 font-medium mt-1">Manage customer questions and leads.</p>
        </div>
    </div>

    <!-- Alert Banner -->
    <?php if ($message): ?>
        <div class="status-banner mb-6 p-4 rounded-2xl text-sm font-bold text-center bg-emerald-50 text-emerald-600 border border-emerald-100 dark:bg-emerald-900/30 dark:border-emerald-800">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Inquiries Grid -->
    <?php if (empty($inquiries)): ?>
        <div class="bg-white border border-gray-200 border-dashed rounded-3xl p-12 text-center shadow-sm">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-50 mb-4 dark:bg-red-900/20">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            </div>
            <h3 class="text-lg font-black text-gray-900 mb-1">No Inquiries Found</h3>
            <p class="text-sm font-medium text-gray-500">There are no product inquiries available at the moment.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($inquiries as $inq): ?>
                <div class="bg-white border border-gray-200 rounded-3xl p-5 sm:p-6 shadow-[0_4px_15px_rgba(0,0,0,0.03)] hover:shadow-[0_8px_25px_rgba(0,0,0,0.06)] transition-all flex flex-col relative overflow-hidden group">
                    
                    <!-- Top Ribbon -->
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-red-500 to-rose-600 opacity-80 group-hover:opacity-100 transition-opacity"></div>
                    
                    <!-- Product Info Header -->
                    <div class="flex gap-4 items-start mb-4 border-b border-gray-100 pb-4">
                        <div class="w-16 h-16 shrink-0 rounded-2xl bg-gray-50 border border-gray-100 p-2 overflow-hidden flex items-center justify-center">
                            <?php if(!empty($inq['product_image'])): ?>
                                <img src="/uploads/products/<?= htmlspecialchars($inq['product_image']) ?>" class="w-full h-full object-contain">
                            <?php else: ?>
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 overflow-hidden pt-1">
                            <h3 class="font-black text-gray-900 text-lg leading-tight truncate mb-1" title="<?= htmlspecialchars($inq['product_name']) ?>">
                                <?= htmlspecialchars($inq['product_name']) ?>
                            </h3>
                            <div class="text-sm font-black text-emerald-600 dark:text-emerald-400">
                                ₹<?= number_format((float)$inq['product_price'], 2) ?>
                            </div>
                        </div>
                    </div>

                    <!-- User Info Box -->
                    <div class="bg-gray-50 rounded-2xl p-4 mb-4 border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-black text-xs shrink-0 dark:bg-red-900/30 dark:text-red-400">
                                <?= strtoupper(substr($inq['user_name'], 0, 1)) ?>
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest leading-none mb-0.5">Customer</p>
                                <p class="font-bold text-gray-900 text-sm truncate"><?= htmlspecialchars($inq['user_name']) ?></p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1 pl-11">
                            <a href="mailto:<?= htmlspecialchars($inq['user_email']) ?>" class="text-xs font-bold text-blue-600 hover:text-blue-800 truncate dark:text-blue-400">
                                <?= htmlspecialchars($inq['user_email']) ?>
                            </a>
                            <?php if(!empty($inq['user_phone'])): ?>
                                <a href="tel:<?= htmlspecialchars($inq['user_phone']) ?>" class="text-xs font-bold text-gray-600 hover:text-gray-900 truncate">
                                    <?= htmlspecialchars($inq['user_phone']) ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Message Body -->
                    <div class="flex-1 mb-6">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Inquiry Message</p>
                        <div class="text-sm text-gray-700 leading-relaxed font-medium bg-white p-1">
                            <?php if (strlen(trim((string)$inq['message']))): ?>
                                <?= nl2br(htmlspecialchars($inq['message'])) ?>
                            <?php else: ?>
                                <span class="text-gray-400 italic">No additional message provided.</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Footer / Actions -->
                    <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                        <span class="text-[10px] font-bold text-gray-500 uppercase">
                            <?= date('d M Y, h:i A', strtotime($inq['created_at'])) ?>
                        </span>
                        
                        <button onclick="confirmDelete(<?= (int)$inq['id'] ?>)" class="bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded-xl text-xs font-bold hover:bg-red-600 hover:text-white transition-all shadow-sm active:scale-95 flex items-center gap-1.5 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Delete
                        </button>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../admin/footer_admin.php'; ?>
</body>
</html>