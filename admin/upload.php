<?php
// Include Admin Header
include '../admin/admin_header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Upload Assets - Admin</title>

<!-- Tailwind & Fonts -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;900&display=swap" rel="stylesheet" />

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #fdfdfd; color: #1a1a1a; transition: background 0.3s ease; }
    
    /* Prevent scrolling/bouncing on mobile */
    html, body { height: 100%; margin: 0; overflow: hidden; }
    
    .page-scroll-area { height: calc(100vh - 130px); overflow-y: auto; padding-bottom: 30px;}

    /* Dark Mode Styles */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .bg-white { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme .bg-gray-50 { background: #374151 !important; border-color: #4b5563 !important; }
    body.dark-theme .text-gray-900 { color: #ffffff !important; }
    body.dark-theme .text-gray-700 { color: #d1d5db !important; }
    body.dark-theme .text-gray-500 { color: #9ca3af !important; }
    body.dark-theme .text-gray-400 { color: #6b7280 !important; }
</style>
</head>

<body class="pb-12">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="page-scroll-area">
    <div class="max-w-[600px] mx-auto px-4 sm:px-6 mt-8">
        
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Upload Asset</h1>
            <p class="text-gray-500 font-medium mt-1">Upload images or files to the server.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-3xl p-6 sm:p-8 shadow-sm">
            <div class="w-full h-[250px] border-2 border-dashed border-gray-300 rounded-2xl flex flex-col items-center justify-center bg-gray-50 hover:bg-red-50 hover:border-red-400 transition-all cursor-pointer relative group overflow-hidden dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700">
                
                <input type="file" id="uploadInput" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*">
                
                <div id="uploadPlaceholder" class="flex flex-col items-center justify-center pointer-events-none">
                    <div class="w-16 h-16 rounded-full bg-white shadow-sm flex items-center justify-center mb-4 group-hover:scale-110 transition-transform dark:bg-gray-700">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    </div>
                    <p class="text-base font-bold text-gray-700 group-hover:text-red-600 transition-colors">Tap to select or drop file here</p>
                    <p class="text-xs font-medium text-gray-400 mt-1">Supports JPG, PNG, GIF</p>
                </div>

                <img id="previewImage" class="absolute inset-0 w-full h-full object-contain p-2 hidden z-0 bg-white dark:bg-gray-800">
            </div>

            <div class="mt-6 flex gap-3 hidden" id="actionButtons">
                <button type="button" onclick="resetUpload()" class="flex-1 bg-gray-100 text-gray-700 px-4 py-3 rounded-xl font-bold hover:bg-gray-200 transition-colors shadow-sm dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">Cancel</button>
                <button type="button" class="flex-[2] bg-gray-900 text-white px-4 py-3 rounded-xl font-bold hover:bg-black transition-colors shadow-lg dark:bg-red-600 dark:hover:bg-red-700">Upload Now</button>
            </div>
        </div>

    </div>
</div>

<?php include '../admin/footer_admin.php'; ?>

<script>
    const uploadInput = document.getElementById("uploadInput");
    const previewImage = document.getElementById("previewImage");
    const uploadPlaceholder = document.getElementById("uploadPlaceholder");
    const actionButtons = document.getElementById("actionButtons");

    uploadInput.addEventListener("change", function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        previewImage.src = URL.createObjectURL(file);
        previewImage.classList.remove("hidden");
        uploadPlaceholder.classList.add("hidden");
        actionButtons.classList.remove("hidden");
    });

    function resetUpload() {
        uploadInput.value = "";
        previewImage.src = "";
        previewImage.classList.add("hidden");
        uploadPlaceholder.classList.remove("hidden");
        actionButtons.classList.add("hidden");
    }
</script>

</body>
</html>