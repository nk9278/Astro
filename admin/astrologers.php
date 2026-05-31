<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /login.php');
    exit;
}

$search = $_GET['search'] ?? '';
$search_sql = '';
$params = [];

if ($search) {
    $search_sql = "AND astrologer_name LIKE ?";
    $params[] = "%$search%";
}

$update_status = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $astro_id = intval($_POST['astro_id']);
    $commission_percent = intval($_POST['commission_percent']);

    if ($commission_percent >= 0 && $commission_percent <= 100) {
        $stmt_update = $conn->prepare("UPDATE users SET commission_percent=? WHERE id=? AND role_id=1");
        $ok = $stmt_update->execute([$commission_percent, $astro_id]);
        $update_status = $ok ? 'Commission updated successfully.' : 'Update failed.';
    } else {
        $update_status = 'Please enter a value between 0 and 100.';
    }
}

$stmt = $conn->prepare("SELECT id, astrologer_name, experience_years, commission_percent FROM users WHERE role_id=1 $search_sql ORDER BY astrologer_name ASC");
$stmt->execute($params);
$astrologers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../admin/admin_header.php'; ?>
<?php include '../assets/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Astrologers - Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />

<!-- Tailwind & Fonts -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;900&display=swap" rel="stylesheet" />

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #fdfdfd; color: #1a1a1a; }
    
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
        td form { width: 100%; display: flex; gap: 10px; }
        td input[type="number"] { flex: 1; }
        td .action-btn-group { width: 100%; }
        td .action-btn-group a { display: block; text-align: center; }
    }

    /* =========================================
       ENHANCED DARK MODE STYLES
    ========================================= */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .bg-white { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme th { background: #374151 !important; color: #f9fafb !important; border-color: #4b5563 !important; }
    body.dark-theme td { border-color: #4b5563 !important; color: #d1d5db !important; }
    body.dark-theme tr { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme .text-gray-900 { color: #fff !important; }
    body.dark-theme input { background: #374151 !important; color: #fff !important; border-color: #4b5563 !important; }
    body.dark-theme input:focus { border-color: #ef4444 !important; }
</style>
</head>
<body class="pb-12">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 mt-8">
    
    <a href="/admin/dashboard.php" class="text-sm font-bold text-gray-500 hover:text-red-600 transition-colors mb-4 inline-block">&larr; Back to Dashboard</a>
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Manage Astrologers</h1>
            <p class="text-gray-500 font-medium mt-1">Control profiles and platform commission rates.</p>
        </div>
    </div>

    <!-- Alert Banner -->
    <?php if (!is_null($update_status)): ?>
        <div class="status-banner mb-6 p-4 rounded-2xl text-sm font-bold text-center border <?= strpos(strtolower($update_status), 'failed') !== false || strpos(strtolower($update_status), 'please') !== false ? 'bg-red-50 text-red-600 border-red-100 dark:bg-red-900/30 dark:border-red-800' : 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/30 dark:border-emerald-800' ?>">
            <?= htmlspecialchars($update_status) ?>
        </div>
    <?php endif; ?>

    <!-- Search Form -->
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-200 mb-8 flex flex-col sm:flex-row gap-3">
        <form method="get" class="flex-1 flex gap-3 w-full">
            <input type="search" name="search" placeholder="Search astrologers by name..." value="<?= htmlspecialchars($search) ?>" class="flex-1 px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium" />
            <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-red-700 transition-colors shadow-md active:scale-95">Search</button>
        </form>
        <?php if($search !== ''): ?>
            <a href="/admin/astrologers.php" class="bg-gray-100 text-gray-600 px-6 py-3 rounded-xl font-bold hover:bg-gray-200 transition-colors text-center border border-gray-200">Reset</a>
        <?php endif; ?>
    </div>

    <!-- Astrologers Table -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr>
                    <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Astrologer Name</th>
                    <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs text-center">Experience</th>
                    <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs text-center">Commission %</th>
                    <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($astrologers)): ?>
                    <tr>
                        <td colspan="4" class="p-10 text-center text-red-500 font-bold text-lg">No astrologers found matching your search.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($astrologers as $astro): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td data-label="Astrologer Name" class="p-4 border-b border-gray-100 font-black text-gray-900 md:text-base">
                            <?= htmlspecialchars($astro['astrologer_name']) ?>
                        </td>
                        
                        <td data-label="Experience" class="p-4 border-b border-gray-100 text-gray-600 font-medium md:text-center">
                            <?= htmlspecialchars($astro['experience_years']) ?> Years
                        </td>

                        <td data-label="Commission %" class="p-4 border-b border-gray-100 md:text-center">
                            <form method="post" class="m-0 flex items-center md:justify-center gap-2">
                                <input type="hidden" name="astro_id" value="<?= (int)$astro['id'] ?>">
                                <input type="number" name="commission_percent" min="0" max="100" value="<?= htmlspecialchars($astro['commission_percent']) ?>" class="w-20 px-3 py-2 text-center rounded-lg bg-gray-50 border border-gray-200 outline-none focus:border-red-500 font-bold">
                                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-black transition-all shadow-sm active:scale-95 dark:bg-gray-700 dark:hover:bg-gray-600">Save</button>
                            </form>
                        </td>

                        <td data-label="Action" class="p-4 border-b border-gray-100 md:text-center">
                            <div class="action-btn-group">
                                <a href="/admin/edit_astrologer.php?id=<?= (int)$astro['id'] ?>" class="inline-block bg-red-50 text-red-700 border border-red-200 px-5 py-2 rounded-lg text-sm font-bold hover:bg-red-600 hover:text-white transition-all dark:bg-red-900/30 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white">
                                    Edit Profile
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include '../admin/footer_admin.php'; ?>
</body>
</html>