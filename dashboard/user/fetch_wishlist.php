<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    exit('Unauthorized');
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT w.id, p.name 
    FROM wishlists w 
    JOIN products p ON w.product_id = p.id 
    WHERE w.user_id = ?
");
$stmt->execute([$userId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'count' => count($items),
    'items' => $items
]);
?>