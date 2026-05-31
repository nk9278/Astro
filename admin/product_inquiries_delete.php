<?php
session_start();
require '../db.php';

// Admin authentication check
if (!isset($_SESSION['userid']) || $_SESSION['role'] != 3) {
    header('Location: /auth/login.php');
    exit;
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    
    // Secure delete using prepared statement
    $stmt = $conn->prepare("DELETE FROM product_inquiries WHERE id = ?");
    
    if ($stmt->execute([$del_id])) {
        // Redirect back with success message
        header('Location: product_inquiries.php?msg=deleted');
        exit;
    } else {
        die('Failed to delete inquiry.');
    }
} else {
    die('Invalid request.');
}
?>