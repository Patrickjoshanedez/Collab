<?php
// filepath: c:\xampp\htdocs\Collab\dashboard\user\user-dashboard-validate.php
session_start();
include '../../includes/db.php';

// Fetch products
$stmt = $pdo->prepare("SELECT id, name, price, category, image FROM products");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch average ratings and review counts for each product
foreach ($products as &$product) {
    $stmt = $pdo->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS review_count FROM reviews WHERE product_id = ?");
    $stmt->execute([$product['id']]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    $product['avg_rating'] = $stats['avg_rating'] ? round($stats['avg_rating'], 1) : 0;
    $product['review_count'] = $stats['review_count'];
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($products);