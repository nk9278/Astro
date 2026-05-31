<?php
// This script ensures logged-in users have filled out their details
if (isset($_SESSION['userid']) && $_SESSION['role'] == 2) {
    // We check if 'name' is empty as a proxy for an incomplete profile
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['userid']]);
    $checkUser = $stmt->fetch();

    // If name is empty and we aren't already on the details page, redirect
    if (empty($checkUser['name']) && basename($_SERVER['PHP_SELF']) != 'details.php') {
        header("Location: /user/details.php");
        exit;
    }
}
?>