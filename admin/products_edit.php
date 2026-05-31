<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /auth/login.php');
    exit;
}

include '../admin/admin_header.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { die('Invalid product ID.'); }

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) { die('Product not found.'); }

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);

    if ($name == '' || $description == '' || $price <= 0) {
        $message = "Please fill all fields correctly.";
    } else {
        $imageName = $product['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = basename($_FILES['image']['name']);
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedfileExtensions)) {
                $newFileName = time() . '_' . md5($fileName) . '.' . $fileExtension;
                $uploadFileDir = '../uploads/products/';
                if (!is_dir($uploadFileDir)) { mkdir($uploadFileDir, 0777, true); }
                $dest_path = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    if ($imageName && file_exists($uploadFileDir . $imageName)) { @unlink($uploadFileDir . $imageName); }
                    $imageName = $newFileName;
                } else {
                    $message = "Error uploading new image.";
                }
            } else {
                $message = "Invalid image type. Allowed types: jpg, jpeg, png, gif.";
            }
        }
        if (!$message) {
            $stmtUpdate = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ? WHERE id = ?");
            if ($stmtUpdate->execute([$name, $description, $price, $imageName, $id])) {
                $message = "Success: Product updated successfully.";
                $stmt->execute([$id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $message = "Error updating product.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Edit Product - Admin</title>
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
    body.dark-theme textarea, body.dark-theme input[type="file"] { 
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

<div class="max-w-[850px] mx-auto px-4 sm:px-6 mt-8">
    
    <a href="products_list.php" class="text-sm font-bold text-gray-500 hover:text-red-600 transition-colors mb-4 inline-block">&larr; Back to Product List</a>
    
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Edit Product</h1>
            <p class="text-gray-500 font-medium mt-1">Update details for <?= htmlspecialchars($product['name']) ?></p>
        </div>
    </div>

    <!-- Alert Banner -->
    <?php if($message): ?>
        <div class="status-banner mb-6 p-4 rounded-2xl text-sm font-bold flex items-center gap-3 border <?= stripos($message,'Success')!==false ? 'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/30 dark:border-emerald-800' : 'bg-red-50 text-red-600 border-red-100 dark:bg-red-900/30 dark:border-red-800' ?>">
            <i data-lucide="<?= stripos($message,'Success')!==false ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5 shrink-0"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white border border-gray-200 rounded-3xl p-6 sm:p-8 shadow-sm">
        <form method="POST" enctype="multipart/form-data" class="space-y-6" novalidate>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Product Name</label>
                    <input id="name" name="name" type="text" class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium" required value="<?= htmlspecialchars($product['name']) ?>" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="4" class="w-full px-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium resize-none" required><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Price (INR)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">₹</span>
                        <input id="price" name="price" type="number" step="0.01" class="w-full pl-9 pr-5 py-4 rounded-xl bg-gray-50 border border-gray-200 text-sm outline-none focus:border-red-500 transition-all font-medium" required value="<?= htmlspecialchars($product['price']) ?>" />
                    </div>
                </div>

                <!-- Image Section -->
                <div class="md:col-span-2 flex flex-col sm:flex-row items-center gap-6 mt-4 p-5 bg-gray-50 rounded-2xl border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="shrink-0 w-28 h-28 rounded-2xl overflow-hidden border border-gray-200 bg-white flex items-center justify-center dark:border-gray-600 dark:bg-gray-700">
                        <?php if (!empty($product['image']) && file_exists('../uploads/products/' . $product['image'])): ?>
                            <img id="img_preview" class="w-full h-full object-contain p-2" src="../uploads/products/<?= htmlspecialchars($product['image']) ?>" alt="Product Image">
                        <?php else: ?>
                            <span class="text-xs text-gray-400 font-bold">No Image</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 text-center sm:text-left w-full">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Change Image (optional)</label>
                        <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.gif,image/*" class="w-full text-sm text-gray-500 cursor-pointer mb-2" />
                        <p class="text-xs font-medium text-gray-400">Selecting a new image will replace the current one.</p>
                    </div>
                </div>

                <div class="md:col-span-2 pt-4">
                    <button type="submit" class="w-full py-5 bg-gray-900 text-white rounded-xl font-black text-lg hover:bg-black transition-all shadow-lg active:scale-95 dark:bg-red-600 dark:hover:bg-red-700">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();

    // Client-side new image preview
    const input = document.getElementById('image');
    const previewImg = document.getElementById('img_preview');
    input?.addEventListener('change', () => {
        const f = input.files && input.files[0];
        if (!f) return;
        const url = URL.createObjectURL(f);
        if(previewImg) {
            previewImg.src = url;
        } else {
            // if no image existed previously, we'd need to create one, but HTML structure always has img_preview now
        }
    });
</script>

<?php include '../admin/footer_admin.php'; ?>
</body>
</html>