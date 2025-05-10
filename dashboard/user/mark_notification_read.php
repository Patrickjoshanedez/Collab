<?php
session_start();
include '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $notifId = intval($_POST['id']); // Ensure the ID is an integer
    $userId = $_SESSION['user_id'];

    // Debugging: Log the incoming data
    error_log("Notification ID: $notifId, User ID: $userId");

    // Check if the notification exists and belongs to the user
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$notifId, $userId]);
    $notification = $stmt->fetch();

    if (!$notification) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Notification not found or unauthorized']);
        exit;
    }

    // Mark the notification as read
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notifId, $userId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Failed to mark notification as read']);
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid request']);
}
?>