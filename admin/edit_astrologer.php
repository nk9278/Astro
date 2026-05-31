<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: /admin/astrologers.php');
    exit;
}

$astro_id = intval($_GET['id']);
$message = '';
$message_type = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $name_hi = trim($_POST['name_hi'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $about = trim($_POST['about'] ?? '');
    $about_hi = trim($_POST['about_hi'] ?? '');
    $experience_years = intval($_POST['experience_years'] ?? 0);
    $skills = trim($_POST['skills'] ?? '');
    $languages = trim($_POST['languages'] ?? '');
    $base_price_per_minute = floatval($_POST['base_price_per_minute'] ?? 0);
    $price_per_minute = floatval($_POST['price_per_minute'] ?? 0);

    // Calculate Offer Percentage
    $offer_percent = 0;
    if ($base_price_per_minute > $price_per_minute && $base_price_per_minute > 0) {
        $offer_percent = round(100 * (1 - $price_per_minute / $base_price_per_minute));
    }

    if (empty($name) || empty($phone) || empty($base_price_per_minute)) {
        $message = "Please fill all mandatory fields (Name, Phone, Price).";
        $message_type = "error";
    } else {
        $sql = "UPDATE users SET astrologer_name=?, astrologer_name_hi=?, phone=?, about=?, about_hi=?, experience_years=?, skills=?, languages=?, price_per_minute=?, base_price_per_minute=?, offer_percent=? ";
        $params = [$name, $name_hi, $phone, $about, $about_hi, $experience_years, $skills, $languages, $price_per_minute, $base_price_per_minute, $offer_percent];

        // Handle Profile Photo Upload
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
            $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/profiles/';
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

            $ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($ext, $allowed_types)) {
                $photo_name = 'profile_' . $astro_id . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_dir . $photo_name)) {
                    $sql .= ", profile_photo=?";
                    $params[] = $photo_name;
                }
            } else {
                $message = "Invalid image format for profile photo.";
                $message_type = "error";
            }
        }

        $sql .= " WHERE id=? AND role_id=1";
        $params[] = $astro_id;

        if ($message_type !== "error") {
            $stmt = $conn->prepare($sql);
            if ($stmt->execute($params)) {
                $message = "Astrologer Profile Updated Successfully.";
                $message_type = "success";
            } else {
                $message = "Database update failed.";
                $message_type = "error";
            }
        }
    }
}

// Fetch Current Data
$stmt = $conn->prepare("SELECT * FROM users WHERE id=? AND role_id=1");
$stmt->execute([$astro_id]);
$astro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$astro) {
    echo "Astrologer not found.";
    exit;
}

include '../admin/admin_header.php';
include '../assets/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit Astrologer Profile - Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Tailwind & Fonts -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;900&display=swap" rel="stylesheet" />

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #fdfdfd; color: #1a1a1a; transition: background 0.3s ease; }
    
    .status-banner { animation: fadeInDown 0.4s ease-out; }
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* Custom File Input Styling */
    input[type=file]::file-selector-button {
      border: none; background: #f3f4f6; padding: 8px 16px; border-radius: 8px;
      color: #374151; cursor: pointer; font-weight: 700; margin-right: 12px; transition: all 0.2s;
    }
    input[type=file]::file-selector-button:hover { background: #e5e7eb; }

    /* =========================================
       ENHANCED DARK MODE STYLES
    ========================================= */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .bg-white { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme .text-gray-900 { color: #ffffff !important; }
    body.dark-theme .text-gray-700 { color: #d1d5db !important; }
    body.dark-theme .text-gray-500 { color: #9ca3af !important; }
    body.dark-theme .bg-gray-50 { background: #374151 !important; border-color: #4b5563 !important; }
    
    body.dark-theme input[type="text"], body.dark-theme input[type="number"], 
    body.dark-theme input[type="tel"], body.dark-theme textarea, body.dark-theme input[type="file"] { 
        background: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; 
    }
    body.dark-theme input:focus, body.dark-theme textarea:focus { border-color: #ef4444 !important; }
    
    body.dark-theme input[type=file]::file-selector-button { background: #4b5563 !important; color: #f9fafb !important; }
    body.dark-theme input[type=file]::file-selector-button:hover { background: #6b7280 !important; }
</style>
</head>
<body class="pb-12">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="max-w-[1000px] mx-auto px-4 sm:px-6 mt-8">
    
    <a href="/admin/astrologers.php" class="text-sm font-bold text-gray-500 hover:text-red-600 transition-colors mb-4 inline-block">&larr; Back to Astrologers List</a>
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 border-b border-gray-200 pb-6 dark:border-gray-700">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Edit Profile</p>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight"><?= htmlspecialchars($astro['astrologer_name']) ?></h1>
        </div>
    </div>

    <!-- Alert Banner -->
    <?php if ($message): ?>
        <div class="status-banner mb-6 p-4 rounded-2xl text-sm font-bold text-center border <?= $message_type === 'success' ? 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/30 dark:border-emerald-800' : 'bg-red-50 text-red-600 border-red-100 dark:bg-red-900/30 dark:border-red-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <!-- Profile Photo Card -->
        <div class="bg-white border border-gray-200 rounded-3xl p-6 sm:p-8 shadow-sm flex flex-col sm:flex-row items-center gap-6">
            <div class="shrink-0 w-28 h-28 rounded-full overflow-hidden border-4 border-gray-100 dark:border-gray-700 shadow-sm bg-gray-50 dark:bg-gray-800 flex items-center justify-center">
                <img src="<?= htmlspecialchars($astro['profile_photo'] ? '/assets/images/profiles/'.$astro['profile_photo'] : '/assets/images/default-user.png') ?>" 
                     id="photo_preview" class="w-full h-full object-cover">
            </div>
            <div class="flex-1 text-center sm:text-left">
                <label class="block text-base font-black text-gray-900 mb-2">Update Profile Photo</label>
                <input type="file" name="profile_photo" id="profile_photo" accept="image/*" class="w-full text-sm text-gray-500 mb-2 cursor-pointer">
                <p class="text-xs font-medium text-gray-400">Leave empty to keep current photo. (Square images look best)</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-3xl p-6 sm:p-8 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- General Details -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-black text-gray-900 border-b border-gray-100 pb-2 mb-2 dark:border-gray-700">General Information</h3>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Name (English) *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($astro['astrologer_name'] ?? '') ?>" required 
                           class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Name (Hindi)</label>
                    <input type="text" name="name_hi" value="<?= htmlspecialchars($astro['astrologer_name_hi'] ?? '') ?>" 
                           class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Phone Number *</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($astro['phone'] ?? '') ?>" required 
                           class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Experience (Years) *</label>
                    <input type="number" name="experience_years" value="<?= htmlspecialchars($astro['experience_years'] ?? '') ?>" required 
                           class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Skills (Comma separated)</label>
                    <input type="text" name="skills" value="<?= htmlspecialchars($astro['skills'] ?? '') ?>" 
                           class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Languages Spoken</label>
                    <input type="text" name="languages" value="<?= htmlspecialchars($astro['languages'] ?? '') ?>" 
                           class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium">
                </div>

                <!-- Pricing -->
                <div class="md:col-span-2 mt-4">
                    <h3 class="text-lg font-black text-gray-900 border-b border-gray-100 pb-2 mb-2 dark:border-gray-700">Pricing Settings</h3>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Base Price / min (₹) *</label>
                    <input type="number" step="0.01" name="base_price_per_minute" value="<?= htmlspecialchars($astro['base_price_per_minute'] ?? '') ?>" required 
                           class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Offer Price / min (₹) *</label>
                    <input type="number" step="0.01" name="price_per_minute" value="<?= htmlspecialchars($astro['price_per_minute'] ?? '') ?>" required 
                           class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium text-emerald-600 dark:text-emerald-400">
                </div>

                <!-- Bio -->
                <div class="md:col-span-2 mt-4">
                    <h3 class="text-lg font-black text-gray-900 border-b border-gray-100 pb-2 mb-2 dark:border-gray-700">Biography</h3>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">About / Bio (English)</label>
                    <textarea name="about" rows="4" class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium resize-none"><?= htmlspecialchars($astro['about'] ?? '') ?></textarea>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">About / Bio (Hindi)</label>
                    <textarea name="about_hi" rows="4" class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium resize-none"><?= htmlspecialchars($astro['about_hi'] ?? '') ?></textarea>
                </div>

                <!-- Submit Button -->
                <div class="md:col-span-2 mt-6">
                    <button type="submit" class="w-full py-5 bg-gray-900 text-white rounded-xl font-black text-lg hover:bg-black transition-all shadow-lg active:scale-95 dark:bg-red-600 dark:hover:bg-red-700">
                        Save Profile Changes
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<?php include '../admin/footer_admin.php'; ?>

<script>
    // Live Image Preview Script
    const photoInput = document.getElementById('profile_photo');
    const photoPreview = document.getElementById('photo_preview');

    photoInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>

</body>
</html>