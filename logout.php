<?php
// Destroy never-expire session cookie
setcookie(session_name(), "", time() - 3600, "/");

// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Unset ALL session variables
$_SESSION = [];

// Destroy session completely
session_destroy();

// Redirect to login page
header("Location: index.php");
exit;
?>
