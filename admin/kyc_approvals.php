<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /auth/login.php');
    exit;
}

// Handle Approve/Reject Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['astro_id'])) {
    $astro_id = intval($_POST['astro_id']);
    $action = $_POST['action'] === 'approve' ? 'approved' : 'rejected';
    
    $stmt = $conn->prepare("UPDATE users SET approval_status = ? WHERE id = ? AND role_id = 1");
    $stmt->execute([$action, $astro_id]);
    
    header('Location: /admin/kyc_approvals.php?success=1');
    exit;
}

// Fetch Pending Astrologers (Now including new bank fields)
$stmt = $conn->query("SELECT id, astrologer_name, email, phone, aadhaar_card, pan_card, bank_account, bank_name, bank_ifsc, bank_branch, upi_id, aadhaar_file, pan_file FROM users WHERE role_id = 1 AND approval_status = 'pending' ORDER BY id DESC");
$pending_astrologers = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../admin/admin_header.php';
include '../assets/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>KYC Approvals - Fortune Parth Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Tailwind & Fonts -->
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;900&display=swap" rel="stylesheet" />

<style>
    body { font-family: 'Space Grotesk', sans-serif; background: #fdfdfd; color: #1a1a1a; transition: background 0.3s ease; }
    
    .status-banner { animation: fadeInDown 0.4s ease-out; }
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    /* Mobile Responsive Table (Card View) */
    @media (max-width: 1024px) {
        table thead { display: none; }
        table, tbody, tr, td { display: block; width: 100%; }
        tr {
            background: #ffffff; border: 1px solid #f3f4f6; border-radius: 16px;
            padding: 16px; margin-bottom: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }
        td {
            border: none !important; padding: 10px 0 !important; text-align: left !important;
            display: flex; flex-direction: column; gap: 6px; align-items: flex-start !important;
            border-bottom: 1px dashed #e5e7eb !important;
        }
        td:last-child { border-bottom: none !important; }
        td::before {
            content: attr(data-label); font-size: 0.7rem; font-weight: 800;
            text-transform: uppercase; color: #9ca3af; letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .action-btns { width: 100%; display: flex; gap: 10px; }
        .action-btns form { flex: 1; margin: 0; }
        .action-btns button { width: 100%; }
    }

    /* Image Modal (Lightbox) Styles */
    .modal { display: none; position: fixed; z-index: 99999; padding-top: 60px; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.85); backdrop-filter: blur(8px); }
    .modal-content { margin: auto; display: block; max-width: 90%; max-height: 75vh; object-fit: contain; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); transition: transform 0.3s ease; cursor: zoom-in; }
    .modal-content.zoomed { transform: scale(1.6); cursor: zoom-out; }
    .close { position: absolute; top: 20px; right: 40px; color: #f1f1f1; font-size: 40px; font-weight: bold; cursor: pointer; transition: 0.2s; text-shadow: 0 2px 10px rgba(0,0,0,0.5); }
    .close:hover, .close:focus { color: #dc2626; text-decoration: none; }
    .modal-footer { text-align: center; margin-top: 25px; padding-bottom: 40px; }

    /* =========================================
       ENHANCED DARK MODE STYLES
    ========================================= */
    body.dark-theme { background: #121212 !important; color: #f9fafb !important; }
    body.dark-theme .bg-white { background: #1f2937 !important; border-color: #374151 !important; }
    body.dark-theme th { background: #374151 !important; color: #f9fafb !important; border-color: #4b5563 !important; }
    body.dark-theme td { border-color: #4b5563 !important; color: #d1d5db !important; }
    body.dark-theme tr { background: #1f2937 !important; border-color: #374151 !important; }
    
    body.dark-theme .text-gray-900 { color: #ffffff !important; }
    body.dark-theme .text-gray-700 { color: #d1d5db !important; }
    body.dark-theme .text-gray-600 { color: #9ca3af !important; }
    body.dark-theme .text-gray-500 { color: #6b7280 !important; }

    /* Custom doc links dark mode */
    body.dark-theme .doc-link-blue { background: rgba(59, 130, 246, 0.15) !important; color: #60a5fa !important; border: 1px solid rgba(59, 130, 246, 0.3); }
    body.dark-theme .doc-link-blue:hover { background: rgba(59, 130, 246, 0.25) !important; }
    
    body.dark-theme .doc-link-gray { background: rgba(107, 114, 128, 0.15) !important; color: #d1d5db !important; border: 1px solid rgba(107, 114, 128, 0.3); }
    body.dark-theme .doc-link-gray:hover { background: rgba(107, 114, 128, 0.25) !important; }
</style>
</head>
<body class="pb-12">

<script>
    if (localStorage.getItem('theme') === 'dark') { document.body.classList.add('dark-theme'); }
</script>

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 mt-8">
    
    <a href="/admin/dashboard.php" class="text-sm font-bold text-gray-500 hover:text-red-600 transition-colors mb-4 inline-block">&larr; Back to Dashboard</a>
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 border-b border-gray-200 pb-6 dark:border-gray-700">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Pending KYC Approvals</h1>
            <p class="text-gray-500 font-medium mt-1">Review and approve astrologer identity and bank documents.</p>
        </div>
    </div>

    <!-- Alert Banner -->
    <?php if (isset($_GET['success'])): ?>
        <div class="status-banner mb-6 p-4 rounded-2xl text-sm font-bold text-center bg-emerald-50 text-emerald-600 border border-emerald-100 dark:bg-emerald-900/30 dark:border-emerald-800">
            Status Updated Successfully.
        </div>
    <?php endif; ?>

    <!-- Data Table / Cards -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden md:overflow-x-auto">
        <?php if (count($pending_astrologers) > 0): ?>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs w-[200px]">Astrologer Info</th>
                        <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs w-[250px]">Aadhaar Details</th>
                        <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs w-[250px]">PAN Details</th>
                        <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs">Bank & Payout Details</th>
                        <th class="p-4 border-b border-gray-200 bg-gray-50 font-bold text-gray-700 uppercase tracking-wider text-xs text-center w-[150px]">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_astrologers as $astro): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            
                            <!-- Astro Info -->
                            <td data-label="Astrologer Info" class="p-4 border-b border-gray-100">
                                <strong class="text-base font-black text-gray-900 block mb-1"><?= htmlspecialchars($astro['astrologer_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span class="text-xs font-medium text-gray-500 block mb-1">📧 <?= htmlspecialchars($astro['email'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="text-sm font-bold text-gray-700 block">📞 <?= htmlspecialchars($astro['phone'], ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            
                            <!-- Aadhaar Details -->
                            <td data-label="Aadhaar Details" class="p-4 border-b border-gray-100">
                                <div class="mb-2">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Aadhaar Number</span>
                                    <span class="font-bold text-gray-900"><?= htmlspecialchars($astro['aadhaar_card'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <?php if (!empty($astro['aadhaar_file'])): 
                                    $ext = strtolower(pathinfo($astro['aadhaar_file'], PATHINFO_EXTENSION));
                                    $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                                    $file_url = "/assets/KYC/" . htmlspecialchars($astro['aadhaar_file'], ENT_QUOTES, 'UTF-8');
                                ?>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <?php if($is_image): ?>
                                            <button type="button" class="doc-link-blue bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-100 transition-colors flex items-center gap-1 shadow-sm" onclick="openModal('<?= $file_url ?>')">
                                                👁 View Photo
                                            </button>
                                        <?php else: ?>
                                            <a href="<?= $file_url ?>" target="_blank" class="doc-link-blue bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-100 transition-colors flex items-center gap-1 shadow-sm">
                                                👁 View PDF
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= $file_url ?>" download="Aadhaar_<?= $astro['id'] ?>" class="doc-link-gray bg-gray-100 text-gray-700 border border-gray-200 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-gray-200 transition-colors flex items-center gap-1 shadow-sm">
                                            ⬇ DL
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="inline-block mt-1 px-2 py-1 bg-red-50 text-red-600 text-[10px] font-bold rounded uppercase tracking-wider dark:bg-red-900/30 dark:text-red-400">No file uploaded</span>
                                <?php endif; ?>
                            </td>

                            <!-- PAN Details -->
                            <td data-label="PAN Details" class="p-4 border-b border-gray-100">
                                <div class="mb-2">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">PAN Number</span>
                                    <span class="font-bold text-gray-900"><?= htmlspecialchars($astro['pan_card'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <?php if (!empty($astro['pan_file'])): 
                                    $ext = strtolower(pathinfo($astro['pan_file'], PATHINFO_EXTENSION));
                                    $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                                    $file_url = "/assets/KYC/" . htmlspecialchars($astro['pan_file'], ENT_QUOTES, 'UTF-8');
                                ?>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <?php if($is_image): ?>
                                            <button type="button" class="doc-link-blue bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-100 transition-colors flex items-center gap-1 shadow-sm" onclick="openModal('<?= $file_url ?>')">
                                                👁 View Photo
                                            </button>
                                        <?php else: ?>
                                            <a href="<?= $file_url ?>" target="_blank" class="doc-link-blue bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-blue-100 transition-colors flex items-center gap-1 shadow-sm">
                                                👁 View PDF
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= $file_url ?>" download="PAN_<?= $astro['id'] ?>" class="doc-link-gray bg-gray-100 text-gray-700 border border-gray-200 px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-gray-200 transition-colors flex items-center gap-1 shadow-sm">
                                            ⬇ DL
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="inline-block mt-1 px-2 py-1 bg-red-50 text-red-600 text-[10px] font-bold rounded uppercase tracking-wider dark:bg-red-900/30 dark:text-red-400">No file uploaded</span>
                                <?php endif; ?>
                            </td>

                            <!-- Bank Details -->
                            <td data-label="Bank & Payout Details" class="p-4 border-b border-gray-100">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                    <div><span class="text-xs font-bold text-gray-400 uppercase">Bank:</span> <span class="font-medium text-gray-800"><?= htmlspecialchars($astro['bank_name'], ENT_QUOTES, 'UTF-8') ?></span></div>
                                    <div><span class="text-xs font-bold text-gray-400 uppercase">Branch:</span> <span class="font-medium text-gray-800"><?= htmlspecialchars($astro['bank_branch'], ENT_QUOTES, 'UTF-8') ?></span></div>
                                    <div class="sm:col-span-2"><span class="text-xs font-bold text-gray-400 uppercase">A/C No:</span> <span class="font-black text-gray-900 tracking-wider font-mono"><?= htmlspecialchars($astro['bank_account'], ENT_QUOTES, 'UTF-8') ?></span></div>
                                    <div><span class="text-xs font-bold text-gray-400 uppercase">IFSC:</span> <span class="font-bold text-gray-800"><?= htmlspecialchars($astro['bank_ifsc'], ENT_QUOTES, 'UTF-8') ?></span></div>
                                    <div class="sm:col-span-2 mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                                        <span class="text-xs font-bold text-emerald-500 uppercase">UPI ID:</span> <span class="font-bold text-gray-900"><?= htmlspecialchars($astro['upi_id'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td data-label="Action" class="p-4 border-b border-gray-100 md:text-center">
                                <div class="action-btns flex flex-col gap-2">
                                    <form method="POST" class="m-0">
                                        <input type="hidden" name="astro_id" value="<?= $astro['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="w-full bg-emerald-600 text-white px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-emerald-700 transition-all shadow-md active:scale-95 flex items-center justify-center gap-1" onclick="return confirm('Approve this astrologer?');">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    <form method="POST" class="m-0">
                                        <input type="hidden" name="astro_id" value="<?= $astro['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="w-full bg-red-50 text-red-600 border border-red-200 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-red-600 hover:text-white transition-all shadow-sm active:scale-95 flex items-center justify-center gap-1 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white" onclick="return confirm('Reject this astrologer?');">
                                            ✕ Reject
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4 dark:bg-gray-800">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-lg font-black text-gray-900 mb-1">All Caught Up!</h3>
                <p class="text-sm font-medium text-gray-500">There are no pending KYC approvals at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Image Modal (Lightbox) -->
<div id="imageModal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <img class="modal-content" id="modalImage" onclick="toggleZoom(this)">
    <div class="modal-footer">
        <a id="modalDownloadBtn" href="#" download class="inline-flex items-center justify-center gap-2 bg-red-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-red-700 transition-all shadow-lg active:scale-95">
            ⬇ Download Image
        </a>
    </div>
</div>

<script>
    const modal = document.getElementById("imageModal");
    const modalImg = document.getElementById("modalImage");
    const modalDownloadBtn = document.getElementById("modalDownloadBtn");

    function openModal(imageSrc) {
        modal.style.display = "block";
        document.body.style.overflow = "hidden"; // Prevent scrolling
        modalImg.src = imageSrc;
        modalImg.classList.remove("zoomed"); // Reset zoom on open
        
        // Setup download button inside modal
        modalDownloadBtn.href = imageSrc;
        let fileName = imageSrc.substring(imageSrc.lastIndexOf('/') + 1);
        modalDownloadBtn.setAttribute("download", fileName);
    }

    function closeModal() {
        modal.style.display = "none";
        document.body.style.overflow = "auto"; // Enable scrolling
        modalImg.src = "";
    }

    function toggleZoom(imgElement) {
        imgElement.classList.toggle("zoomed");
    }

    // Close modal when clicking outside the image
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
    
    // Close modal on Escape key press
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            closeModal();
        }
    });
</script>

<?php include '../admin/footer_admin.php'; ?>
</body>
</html>