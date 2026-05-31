<?php
session_start();
require '../db.php';

if (!isset($_SESSION['userid']) || $_SESSION['role'] != 2) {
    die('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_SESSION['userid'];
    $call_id = (int)($_POST['call_id'] ?? 0);
    $chat_session_id = (int)($_POST['chat_session_id'] ?? 0);
    $astro_id = (int)($_POST['astro_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $review_text = trim(htmlspecialchars($_POST['review'] ?? '', ENT_QUOTES, 'UTF-8'));

    if ($rating < 1 || $rating > 5 || $astro_id <= 0) {
        die("Invalid Data");
    }

    try {
        // [HARDENED SECURITY] Anti-Duplicate Validation for both Calls and Chats
        if ($call_id > 0) {
            $checkStmt = $conn->prepare("SELECT id FROM astrologer_reviews WHERE call_id = ? AND user_id = ?");
            $checkStmt->execute([$call_id, $user_id]);
        } else {
            $checkStmt = $conn->prepare("SELECT id FROM astrologer_reviews WHERE chat_session_id = ? AND user_id = ?");
            $checkStmt->execute([$chat_session_id, $user_id]);
        }

        if ($checkStmt->rowCount() > 0) {
            die("Review already submitted.");
        }

        // 1. Secure Insert with Dynamic Column Fallback Safety Architecture
        try {
            $stmt = $conn->prepare("INSERT INTO astrologer_reviews (call_id, chat_session_id, user_id, astrologer_id, rating, review_text) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$call_id ?: null, $chat_session_id ?: null, $user_id, $astro_id, $rating, $review_text]);
        } catch (PDOException $ex) {
            // Fallback strategy if your local schema strictly relies on call_id column set to 0 for text consultations
            $stmt = $conn->prepare("INSERT INTO astrologer_reviews (call_id, user_id, astrologer_id, rating, review_text) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$call_id ?: 0, $user_id, $astro_id, $rating, $review_text]);
        }

        // 2. Deadlock Prevention: Isolated lightweight aggregate lookup 
        $avgStmt = $conn->prepare("SELECT COALESCE(AVG(rating), 0) as avg_rating, COUNT(id) as total_reviews FROM astrologer_reviews WHERE astrologer_id = ?");
        $avgStmt->execute([$astro_id]);
        $ratingData = $avgStmt->fetch(PDO::FETCH_ASSOC);

        // 3. Independent update operation to bypass transactional locking contention
        $updateRatingStmt = $conn->prepare("UPDATE users SET profile_rating = ?, rating_count = ? WHERE id = ?");
        $updateRatingStmt->execute([round($ratingData['avg_rating'], 1), $ratingData['total_reviews'], $astro_id]);

        echo "SUCCESS";
        
    } catch (PDOException $e) {
        error_log("Review Sync Error: " . $e->getMessage());
        echo "Database Error";
    }
}
?>