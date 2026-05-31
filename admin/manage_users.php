<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /login.php');
    exit;
}

$debug = (isset($_GET['debug']) && $_GET['debug'] === '1');
ini_set('display_errors', $debug ? '1' : '0');
error_reporting($debug ? E_ALL : (E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING));

$message = '';
$msgType = '';

// Handle Suspend, Activate, and Delete Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];

    try {
        if ($action === 'suspend') {
            $stmt = $conn->prepare("UPDATE users SET status = 'suspended' WHERE id = ? AND role_id = 2");
            $stmt->execute([$user_id]);
            $message = "User account suspended successfully.";
            $msgType = 'success';
        } elseif ($action === 'activate') {
            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ? AND role_id = 2");
            $stmt->execute([$user_id]);
            $message = "User account activated successfully.";
            $msgType = 'success';
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role_id = 2");
            $stmt->execute([$user_id]);
            $message = "User permanently deleted.";
            $msgType = 'error'; 
        }
    } catch (PDOException $e) {
        $message = "Action failed: " . $e->getMessage();
        $msgType = 'error';
    }
}

// Handle Filters
$search_email = trim($_GET['search_email'] ?? '');
$search_phone = trim($_GET['search_phone'] ?? '');

$sql = "SELECT * FROM users WHERE role_id = 2";
$params = [];

if ($search_email !== '') {
    $sql .= " AND email LIKE ?";
    $params[] = "%$search_email%";
}
if ($search_phone !== '') {
    $sql .= " AND phone LIKE ?";
    $params[] = "%$search_phone%";
}

$sql .= " ORDER BY id DESC";

// Safe Fetch Users
$users = [];
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Database Error: " . $e->getMessage();
    $msgType = 'error';
}

include '../admin/admin_header.php';
?>

<?php include '../assets/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Users - Admin Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
<link rel="stylesheet" href="/assets/css/header.css" />
<link rel="stylesheet" href="/assets/css/footer.css" />

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;900&display=swap" rel="stylesheet" />

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #f4f7f6; color: #1a1a1a; transition: background 0.3s ease; }
    .glass-card {
        background: #ffffff; border: 1px solid #eaeaea; border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.3s ease;
    }
    
    /* Input field styling */
    .filter-input {
        width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; 
        outline: none; transition: all 0.2s; font-size: 0.875rem;
    }
    .filter-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }

    /* Dark Mode Overrides */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .glass-card { background: #1f2937 !important; border-color: #374151 !important; color: #f9fafb !important; }
    body.dark-theme .text-gray-900, body.dark-theme .text-gray-800 { color: #f9fafb !important; }
    body.dark-theme .text-gray-600, body.dark-theme .text-gray-500 { color: #9ca3af !important; }
    body.dark-theme table thead { background: #374151 !important; color: #f9fafb !important;}
    body.dark-theme table tbody tr { border-color: #4b5563 !important; }
    body.dark-theme table tbody tr:hover { background: #374151 !important; }
    
    body.dark-theme .filter-input { background: #374151 !important; border-color: #4b5563 !important; color: #fff !important; }
    body.dark-theme .filter-input:focus { border-color: #60a5fa !important; box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2); }
</style>
</head>
<body class="bg-gray-50">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-24">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight">Manage Users</h1>
            <p class="text-sm text-gray-500 mt-1">Search, suspend, or delete standard user accounts.</p>
        </div>
        <div>
            <a href="/admin/dashboard.php" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm rounded-md font-medium transition dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700 dark:text-gray-200">
                &larr; Back to Dashboard
            </a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="mb-6 p-4 rounded-md text-sm font-medium <?= $msgType === 'success' ? 'bg-green-50 text-green-800 border-green-200' : 'bg-red-50 text-red-800 border-red-200' ?> border">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="glass-card p-4 mb-6">
        <form method="GET" action="" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="w-full md:w-1/3">
                <label class="block text-xs font-semibold text-gray-600 mb-1 dark:text-gray-400 uppercase tracking-wide">Email Address</label>
                <input type="text" name="search_email" value="<?= htmlspecialchars($search_email) ?>" placeholder="Search email..." class="filter-input">
            </div>
            <div class="w-full md:w-1/3">
                <label class="block text-xs font-semibold text-gray-600 mb-1 dark:text-gray-400 uppercase tracking-wide">Phone Number</label>
                <input type="text" name="search_phone" value="<?= htmlspecialchars($search_phone) ?>" placeholder="Search phone..." class="filter-input">
            </div>
            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition w-full md:w-auto">
                    Filter
                </button>
                <?php if (!empty($search_email) || !empty($search_phone)): ?>
                    <a href="/admin/manage_users.php" class="px-5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-md transition text-center w-full md:w-auto dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600 dark:text-gray-200">
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">User Details</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined Date</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">#<?= htmlspecialchars($user['id']) ?></td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($user['name'] ?? 'N/A') ?></div>
                                    <div class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($user['email'] ?? 'No Email') ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($user['phone'] ?? 'No Phone') ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                    $current_status = $user['status'] ?? 'active';
                                    if ($current_status === 'suspended'): 
                                    ?>
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800">
                                            Suspended
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800">
                                            Active
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'Unknown' ?>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <form method="POST" class="inline-block">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <?php if ($current_status === 'suspended'): ?>
                                            <input type="hidden" name="action" value="activate">
                                            <button type="submit" class="px-3 py-1.5 bg-white border border-green-300 text-green-700 hover:bg-green-50 rounded text-xs font-medium transition dark:bg-transparent dark:border-green-600 dark:text-green-400 dark:hover:bg-green-900/30">
                                                Activate
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="action" value="suspend">
                                            <button type="submit" class="px-3 py-1.5 bg-white border border-yellow-300 text-yellow-700 hover:bg-yellow-50 rounded text-xs font-medium transition dark:bg-transparent dark:border-yellow-600 dark:text-yellow-400 dark:hover:bg-yellow-900/30" onclick="return confirm('Are you sure you want to suspend this user?');">
                                                Suspend
                                            </button>
                                        <?php endif; ?>
                                    </form>

                                    <form method="POST" class="inline-block">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="px-3 py-1.5 bg-white border border-red-300 text-red-700 hover:bg-red-50 rounded text-xs font-medium transition dark:bg-transparent dark:border-red-600 dark:text-red-400 dark:hover:bg-red-900/30" onclick="return confirm('WARNING: Are you sure you want to permanently delete this user? This cannot be undone.');">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center">
                                <p class="text-gray-500 text-sm">No users found.</p>
                                <?php if (!empty($search_email) || !empty($search_phone)): ?>
                                    <p class="text-gray-400 text-xs mt-1">Try adjusting your filters.</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../admin/footer_admin.php'; ?>
</body>
</html>