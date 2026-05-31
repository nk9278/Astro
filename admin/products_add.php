<?php
session_start();
require '../db.php';

// Security Check: Role 3 for Admin
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /auth/login.php');
    exit;
}

// Include Admin Header
include '../admin/admin_header.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? 'Astro Shop');

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = time() . '_' . md5($fileName) . '.' . $fileExtension;
            $uploadFileDir = '../uploads/products/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }
            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, category) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $description, $price, $newFileName, $category])) {
                    $message = "Success: Product successfully listed in " . $category . ".";
                } else {
                    $message = "Error: Database connection issue.";
                }
            } else {
                $message = "Error: Could not save the image file.";
            }
        } else {
            $message = "Invalid: Only JPG, PNG & GIF are allowed.";
        }
    } else {
        $message = "Required: Please select a product image.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Product | Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
  
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;900&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>

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

    /* Dark Mode Styles */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .bg-white { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme .text-gray-900 { color: #ffffff !important; }
    body.dark-theme .text-gray-700 { color: #d1d5db !important; }
    body.dark-theme .text-gray-500 { color: #9ca3af !important; }
    body.dark-theme .bg-gray-50 { background: #374151 !important; border-color: #4b5563 !important; }
    
    body.dark-theme input[type="text"], body.dark-theme input[type="number"], 
    body.dark-theme textarea, body.dark-theme select, body.dark-theme input[type="file"] { 
        background: #374151 !important; color: #ffffff !important; border-color: #4b5563 !important; 
    }
    body.dark-theme input:focus, body.dark-theme textarea:focus, body.dark-theme select:focus { border-color: #ef4444 !important; }
    
    body.dark-theme input[type=file]::file-selector-button { background: #4b5563 !important; color: #f9fafb !important; }
    body.dark-theme input[type=file]::file-selector-button:hover { background: #6b7280 !important; }
  </style>
</head>
<body class="pb-12">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="max-w-[850px] mx-auto px-4 sm:px-6 mt-8">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Add New Item</h1>
            <p class="text-gray-500 font-medium mt-1">Create a new entry for your shop inventory.</p>
        </div>
        <a href="/admin/products_list.php" class="inline-flex items-center justify-center gap-2 bg-white border border-gray-200 px-5 py-2.5 rounded-xl text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors shadow-sm dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">
            <i data-lucide="list" class="w-4 h-4"></i> View Inventory
        </a>
    </div>

    <!-- Alert Banner -->
    <?php if($message): ?>
        <div class="status-banner mb-6 p-4 rounded-2xl text-sm font-bold flex items-center gap-3 border <?= stripos($message,'Success')!==false ? 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/30 dark:border-emerald-800' : 'bg-red-50 text-red-600 border-red-100 dark:bg-red-900/30 dark:border-red-800' ?>">
            <i data-lucide="<?= stripos($message,'Success')!==false ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5 shrink-0"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white border border-gray-200 rounded-3xl p-6 sm:p-8 shadow-sm">
        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Target Shop Section</label>
                    <select name="category" class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium" required>
                        <option value="Astro Shop">Astro Shop (Gemstones, Malas)</option>
                        <option value="Vastu Shop">Vastu Shop (Pyramids, Feng Shui)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Product Title</label>
                    <input name="name" type="text" class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium" placeholder="e.g. Energized Gomti Chakra" required />
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Price (INR)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">₹</span>
                        <input name="price" type="number" step="0.01" class="w-full pl-9 pr-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium" placeholder="0.00" required />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Product Image</label>
                    <input id="image_input" name="image" type="file" accept="image/*" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium text-gray-500" required />
                </div>

                <div class="md:col-span-2 hidden" id="preview_area">
                    <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-2xl border border-dashed border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                        <div class="w-20 h-20 rounded-xl bg-white border border-gray-100 flex items-center justify-center overflow-hidden shrink-0 dark:bg-gray-700 dark:border-gray-600">
                            <img id="img_preview" class="w-full h-full object-contain p-1" src="#" alt="Preview">
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">Image Preview</p>
                            <p class="text-xs font-medium text-gray-500">This is how your item will look in the shop list.</p>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Detailed Description</label>
                    <textarea name="description" rows="5" class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium resize-none" placeholder="Describe the spiritual benefits and usage instructions..." required></textarea>
                </div>

                <div class="md:col-span-2 pt-2">
                    <button type="submit" class="w-full py-5 bg-gray-900 text-white rounded-xl font-black text-lg hover:bg-black transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2 dark:bg-red-600 dark:hover:bg-red-700">
                        <i data-lucide="upload-cloud" class="w-5 h-5"></i> Publish to Shop
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();

    // Image Preview Logic
    const imageInput = document.getElementById('image_input');
    const previewArea = document.getElementById('preview_area');
    const imgPreview = document.getElementById('img_preview');

    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgPreview.src = e.target.result;
                previewArea.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        } else {
            previewArea.classList.add('hidden');
        }
    });
</script>

<?php include '../admin/footer_admin.php'; ?>
</body>
</html>