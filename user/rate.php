<?php
session_start();
require '../db.php';

$astro_id = intval($_POST['astro_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);

if ($astro_id && $rating >= 1 && $rating <= 5) {
    // Insert or update ratings table, or update profile_rating, rating_count
    // Provide your rating update logic here using prepared statements securely.
}

header('Location: /astrologer/profile.php?id=' . $astro_id);
exit;
?>