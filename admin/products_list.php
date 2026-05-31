<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /auth/login.php');
    exit;
}

include '../admin/admin_header.php';

$message = "";

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $stmtImg = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmtImg->execute([$del_id]);
    $img = $stmtImg->fetchColumn();

    if ($img && file_exists("../uploads/products/" . $img)) {
        unlink("../uploads/products/" . $img);
    }

    $stmtDel = $conn->prepare("DELETE FROM products WHERE id = ?");
    if ($stmtDel->execute([$del_id])) {
        $message = "Success: Product deleted successfully.";
    } else {
        $message = "Failed to delete product.";
    }
}

$stmt = $conn->query("SELECT id, name, price, image FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Products - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />

  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;900&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>

  <style>
    body { font-family: 'Space Grotesk', sans-serif; background: #fdfdfd; color: #1a1a1a; transition: background 0.3s ease; }
    
    .status-banner { animation: fadeInDown 0.4s ease-out; }
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* Mobile Responsive Table (Card View) */
    @media (max-width: 768px) {
        table thead { display: none; }
        table, tbody, tr, td { display: block; width: 100%; }
        tr {
            background: #ffffff; border: 1px solid #f3f4f6; border-radius: 16px;
            padding: 16px; margin-bottom: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }
        td {
            border: none !important; padding: 8px 0 !important; text-align: left !important;
            display: flex; flex-direction: column; gap: 4px; align-items: flex-start !important;
        }
        td::before {
            content: attr(data-label); font-size: 0.7rem; font-weight: 800;
            text-transform: uppercase; color: #9ca3af; letter-spacing: 0.05em;
        }
        td[data-label="Image"] { align-items: center !important; }
        .action-btns { width: 100%; display: flex; gap: 10px; }
        .action-btns a { flex: 1; text-align: center; }
    }

    /* Dark Mode Styles */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .bg-white { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme th { background: #374151 !important; color: #f9fafb !important; border-color: #4b5563 !important; }
    body.dark-theme td { border-color: #4b5563 !important; color: #d1d5db !important; }
    body.dark-theme tr { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme .text-gray-900 { color: #ffffff !important; }
    body.dark-theme .text-gray-700 { color: #d1d5db !important; }
  </style>
</head>
<body class="pb-12">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 mt-8">
    
    <a href="/admin/dashboard.php" class="text-sm font-bold text-gray-500 hover:text-red-600 transition-colors mb-4 inline-block">&larr; Back to Dashboard</a>
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Manage Products</h1>
            <p class="text-gray-500 font-medium mt-1">Control your shop inventory and pricing.</p>
        </div>
        <a href="products_add.php" class="inline-flex items-center justify-center gap-2 bg-gray-900 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:bg-black transition-all active:scale-95 dark:bg-red-600 dark:hover:bg-red-700">
            <i data-lucide="plus" class="w-5 h-5"></i> Add New Product
        </a>
    </div>

    <!-- Alert Banner -->
    <?php if ($message): ?>
        <div class="status-banner mb-6 p-4 rounded-2xl text-sm font-bold flex items-center gap-3 border <?= stripos($message,'Success')!==false ? 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/30 dark:border-emerald-800' : 'bg-red-50 text-red-600 border-red-100 dark:bg-red-900/30 dark:border-red-800' ?>">
            <i data-lucide="<?= stripos($message,'Success')!==false ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5 shrink-0"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (count($products) === 0): ?>
        <div class="bg-white border border-gray-200 border-dashed rounded-3xl p-12 text-center shadow-sm">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4 dark:bg-gray-800">
                <i data-lucide="package-open" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h3 class="text-lg font-black text-gray-900 mb-1">No Products Available</h3>
            <p class="text-sm font-medium text-gray-500 mb-6">Your inventory is currently empty.</p>
            <a href="products_add.php" class="inline-block bg-gray-900 text-white px-6 py-3 rounded-xl font-bold shadow-md hover:bg-black transition-all dark:bg-red-600 dark:hover:bg-red-700">Add First Product</a>
        </div>
    <?php else: ?>
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden md:overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs w-[120px] text-center">Image</th>
                        <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Name</th>
                        <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs w-[150px]">Price (₹)</th>
                        <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs w-[200px] text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td data-label="Image" class="p-4 border-b border-gray-100 flex justify-center">
                                <div class="w-16 h-16 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center overflow-hidden shrink-0 dark:bg-gray-700 dark:border-gray-600">
                                    <img src="../uploads/products/<?= htmlspecialchars($p['image']) ?>" alt="Product" class="w-full h-full object-contain p-1">
                                </div>
                            </td>
                            <td data-label="Name" class="p-4 border-b border-gray-100 font-bold text-gray-900 text-base">
                                <?= htmlspecialchars($p['name']) ?>
                            </td>
                            <td data-label="Price (₹)" class="p-4 border-b border-gray-100 font-black text-emerald-600 text-lg dark:text-emerald-400">
                                ₹<?= number_format($p['price'], 2) ?>
                            </td>
                            <td data-label="Actions" class="p-4 border-b border-gray-100 md:text-center">
                                <div class="action-btns flex gap-2 justify-center">
                                    <a href="products_edit.php?id=<?= $p['id'] ?>" class="bg-gray-100 text-gray-700 border border-gray-200 px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-200 transition-colors shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-600 flex items-center justify-center gap-1.5">
                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Edit
                                    </a>
                                    <a href="products_list.php?delete=<?= $p['id'] ?>" onclick="return confirm('Permanently delete this product?');" class="bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded-lg text-sm font-bold hover:bg-red-600 hover:text-white transition-colors shadow-sm dark:bg-red-900/30 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white flex items-center justify-center gap-1.5">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>

<script>
    lucide.createIcons();
</script>

<?php include '../admin/footer_admin.php'; ?>
</body>
</html>