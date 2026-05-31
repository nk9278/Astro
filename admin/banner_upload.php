<?php
session_start();
require '../db.php';

// Admin authentication check
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /auth/login.php');
    exit;
}

$message = "";
$banner = null;

// Handle banner upload (UNCHANGED)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $main_text   = trim($_POST['main_text'] ?? '');
    $sub_text    = trim($_POST['sub_text'] ?? '');
    $button_text = trim($_POST['button_text'] ?? 'Call Now');
    $link        = 'astrologer/list.php'; // fixed

    if (!empty($main_text) && !empty($sub_text)) {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath   = $_FILES['image']['tmp_name'];
            $fileName      = basename($_FILES['image']['name']);
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed       = ['jpg','jpeg','png','gif'];

            if (in_array($fileExtension, $allowed)) {
                $newFileName   = time() . '_' . md5($fileName) . '.' . $fileExtension;
                $uploadDir     = '../uploads/banners/';
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
                $dest          = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest)) {
                    $stmt = $conn->prepare("INSERT INTO banners (image, main_text, sub_text, button_text, link) VALUES (?, ?, ?, ?, ?)");
                    if ($stmt->execute([$newFileName, $main_text, $sub_text, $button_text, $link])) {
                        $message = "Banner uploaded successfully.";
                        $banner  = [
                            'image'       => $newFileName,
                            'main_text'   => $main_text,
                            'sub_text'    => $sub_text,
                            'button_text' => $button_text,
                            'link'        => $link
                        ];
                    } else {
                        $message = "DB error while saving banner.";
                    }
                } else {
                    $message = "Image file upload failed.";
                }
            } else {
                $message = "Allowed file types: jpg, jpeg, png, gif.";
            }
        } else {
            $message = "Banner image is required.";
        }
    } else {
        $message = "Please fill all required fields.";
    }
} else {
    // Fetch latest banner for preview (UNCHANGED)
    $stmtPreview = $conn->query("SELECT * FROM banners ORDER BY id DESC LIMIT 1");
    $banner      = $stmtPreview->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Upload Banner - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
  
  <!-- Tailwind & Fonts -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;900&display=swap" rel="stylesheet" />

  <style>
    body { font-family: 'Space Grotesk', sans-serif; background: #fdfdfd; color: #1a1a1a; transition: background 0.3s ease; }
    
    .status-banner { animation: fadeInDown 0.4s ease-out; }
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* Custom File Input Styling */
    input[type=file]::file-selector-button {
      border: none;
      background: #f3f4f6;
      padding: 8px 16px;
      border-radius: 8px;
      color: #374151;
      cursor: pointer;
      font-weight: 700;
      margin-right: 12px;
      transition: all 0.2s;
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
    
    body.dark-theme input[type="text"], body.dark-theme input[type="file"] { 
        background: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; 
    }
    body.dark-theme input:focus { border-color: #ef4444 !important; }
    
    body.dark-theme input[type=file]::file-selector-button { background: #4b5563 !important; color: #f9fafb !important; }
    body.dark-theme input[type=file]::file-selector-button:hover { background: #6b7280 !important; }

    /* Dark Mode specific for the preview card to match user side promo card */
    body.dark-theme .promo-card { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme .promo-card .p-title { color: #f9fafb !important; }
    body.dark-theme .promo-card .p-sub { color: #9ca3af !important; }
    body.dark-theme .promo-card .p-img { background: #374151 !important; border-color: #4b5563 !important; }
    body.dark-theme .promo-btn { background: #dc2626 !important; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.2) !important; color: white !important;}
    body.dark-theme .promo-btn:hover { background: #b91c1c !important; }
  </style>
</head>
<body class="pb-12">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<?php include '../admin/admin_header.php'; ?>

<div class="max-w-[600px] mx-auto px-4 sm:px-6 mt-8">
    
    <a href="/admin/dashboard.php" class="text-sm font-bold text-gray-500 hover:text-red-600 transition-colors mb-4 inline-block">&larr; Back to Dashboard</a>
    
    <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-8">Upload a Banner</h1>

    <!-- Alert Banner -->
    <?php if($message): ?>
        <div class="status-banner mb-6 p-4 rounded-2xl text-sm font-bold text-center border <?= stripos($message,'success')!==false ? 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/30 dark:border-emerald-800' : 'bg-red-50 text-red-600 border-red-100 dark:bg-red-900/30 dark:border-red-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Upload Form Card -->
    <div class="bg-white border border-gray-200 rounded-3xl p-6 sm:p-8 shadow-sm mb-10">
        <form method="POST" enctype="multipart/form-data" novalidate class="space-y-5">
            
            <div>
                <label for="main_text" class="block text-sm font-bold text-gray-700 mb-2">Main Text</label>
                <input id="main_text" name="main_text" type="text" required placeholder="e.g. Get Any Question Chat" 
                       class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium" />
            </div>

            <div>
                <label for="sub_text" class="block text-sm font-bold text-gray-700 mb-2">Sub Text</label>
                <input id="sub_text" name="sub_text" type="text" required placeholder="e.g. with ₹1 per minute" 
                       class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium" />
            </div>

            <div>
                <label for="button_text" class="block text-sm font-bold text-gray-700 mb-2">Button Text</label>
                <input id="button_text" name="button_text" type="text" placeholder="e.g. Call Now" value="Call Now" 
                       class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium" />
            </div>

            <div>
                <label for="image" class="block text-sm font-bold text-gray-700 mb-2">Banner Image (jpg, jpeg, png, gif)</label>
                <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.gif,image/*" required 
                       class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium text-gray-500" />
                
                <!-- Live Preview Container -->
                <div id="preview" class="mt-4 flex gap-3 flex-wrap" hidden></div>
            </div>

            <button type="submit" class="w-full mt-2 bg-gray-900 text-white py-4 rounded-xl font-black text-lg hover:bg-black transition-all shadow-lg active:scale-95 dark:bg-red-600 dark:hover:bg-red-700">
                Upload Banner
            </button>
        </form>
    </div>

    <!-- Live Banner Preview Section -->
    <h3 class="text-xl font-black text-gray-900 mb-4 text-center">Live App Preview</h3>

    <?php if($banner && !empty($banner['image'])): ?>
        
        <!-- Updated Promo Card Design matching the actual app -->
        <div class="promo-card bg-white rounded-[1.5rem] p-4 sm:p-5 flex items-center justify-between border border-gray-100 shadow-[0_4px_20px_rgba(0,0,0,0.04)] hover:shadow-[0_8px_25px_rgba(220,38,38,0.15)] transition-all duration-300 cursor-pointer group mb-8" 
             onclick="window.location.href='<?= htmlspecialchars($banner['link'], ENT_QUOTES, 'UTF-8') ?>';" 
             role="button" 
             tabindex="0" 
             onkeypress="if(event.key==='Enter'){ window.location.href='<?= htmlspecialchars($banner['link'], ENT_QUOTES, 'UTF-8') ?>'; }"
             aria-label="Open promotion: <?= htmlspecialchars($banner['main_text'], ENT_QUOTES, 'UTF-8') ?>">
            
            <div class="flex-1 pr-4">
                <h3 class="p-title text-base sm:text-lg font-black text-gray-900 leading-tight mb-1.5 tracking-tight">
                    <?= htmlspecialchars($banner['main_text']) ?>
                </h3>
                <p class="p-sub text-xs sm:text-sm font-medium text-gray-500 mb-4 leading-snug">
                    <?= htmlspecialchars($banner['sub_text']) ?>
                </p>
                <button type="button" class="promo-btn bg-red-600 text-white px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-red-600/20 group-hover:bg-red-700 transition-all active:scale-95">
                    <?= htmlspecialchars($banner['button_text']) ?>
                </button>
            </div>
            
            <div class="p-img w-[90px] h-[90px] sm:w-[110px] sm:h-[110px] shrink-0 rounded-[1.25rem] overflow-hidden bg-gray-50 border border-gray-100 shadow-inner">
                <img src="../uploads/banners/<?= htmlspecialchars($banner['image']) ?>" alt="Banner" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
            </div>
        </div>

    <?php else: ?>
        <div class="text-center text-gray-500 font-bold bg-gray-50 rounded-2xl py-8 border border-gray-200 border-dashed dark:bg-gray-800 dark:border-gray-700 mb-8">
            Preview will appear after uploading your first banner.
        </div>
    <?php endif; ?>

</div>

<?php include '../admin/footer_admin.php'; ?>

<script>
  // Client-side image preview
  const fileInput = document.getElementById('image');
  const preview   = document.getElementById('preview');
  fileInput?.addEventListener('change', () => {
    preview.innerHTML = '';
    const f = fileInput.files && fileInput.files[0];
    if (!f) { preview.hidden = true; return; }
    const url = URL.createObjectURL(f);
    const img = document.createElement('img');
    img.src = url; 
    img.alt = 'Selected image preview'; 
    img.className = 'w-24 h-24 object-cover rounded-xl border-2 border-red-500 shadow-md';
    preview.appendChild(img);
    preview.hidden = false;
  });
</script>
</body>
</html>