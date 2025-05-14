<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

try {
    // Include the database connection
    require '../../includes/db.php';

    // Enable PDO exceptions for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'You must be logged in to submit a review.']);
        exit;
    }

    // Retrieve and validate input
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $rating = isset($_POST['rating']) ? floatval($_POST['rating']) : 0.0; // Cast to float for DECIMAL(2,1)
    $review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

    // Validate input
    if ($product_id <= 0 || $rating < 1 || $rating > 5 || empty($review_text)) {
        echo json_encode(['success' => false, 'error' => 'Invalid input.']);
        exit;
    }

    // Insert the review into the database (no duplicate check)
    $stmt = $pdo->prepare("
        INSERT INTO reviews (product_id, user_id, rating, review_text, review_date)
        VALUES (:product_id, :user_id, :rating, :review_text, NOW())
    ");
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':rating', $rating); // Default is PARAM_STR; float is acceptable
    $stmt->bindParam(':review_text', $review_text, PDO::PARAM_STR);
    $stmt->execute();

    // Return success response
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Log the error for debugging (in production, log this instead of displaying it)
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . htmlspecialchars($e->getMessage())]);
}
exit;
