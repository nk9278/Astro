<?php

function addTransaction($conn, $user_id, $related_user_id, $type, $amount, $description, $icon='payments') {

    try {
        $stmt = $conn->prepare("
            INSERT INTO wallet_transactions
            (user_id, related_user_id, type, amount, description, icon, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $result = $stmt->execute([
            $user_id,
            $related_user_id,
            $type,
            $amount,
            $description,
            $icon
        ]);

        if (!$result) {
            error_log("❌ addTransaction FAILED — SQL: " . json_encode($stmt->errorInfo()));
        } else {
            error_log("✅ Transaction Added: user=$user_id, type=$type, amt=$amount");
        }

        return $result;

    } catch (Exception $e) {
        error_log("❌ addTransaction Exception: " . $e->getMessage());
        return false;
    }
}
?>