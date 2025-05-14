<?php
session_start();
header('Content-Type: application/json');
include '../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$review_text = trim($_POST['review_text'] ?? '');

if ($rating < 1 || $rating > 5 || empty($review_text)) {
    echo json_encode(['success' => false, 'error' => 'Rating and review cannot be empty.']);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO reviews (user_id, product_id, rating, review_text, review_date) 
    VALUES (?, ?, ?, ?, NOW())
");
if ($stmt->execute([$user_id, $product_id, $rating, $review_text])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}