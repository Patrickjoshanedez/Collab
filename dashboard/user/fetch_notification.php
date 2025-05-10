<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$isAdmin = ($_SESSION['role'] === 'Admin');

try {
    $sql = "SELECT id, message, is_read, created_at 
            FROM notifications
            WHERE (user_id = :uid OR (admin_only = 1 AND :isAdmin)) AND is_read = 0
            ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uid' => $userId, ':isAdmin' => $isAdmin]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $unreadCount = count($notifications);
    $htmlList = '';
    foreach ($notifications as $notif) {
        $htmlList .= '<li class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 flex justify-between">
                        <span>' . htmlspecialchars($notif['message']) . '</span>
                        <button data-id="' . $notif['id'] . '" class="mark-read text-blue-500 text-sm">Mark as read</button>
                      </li>';
    }

    echo json_encode([
        'unreadCount' => $unreadCount,
        'htmlList' => $htmlList
    ]);
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Failed to fetch notifications']);
}
?>