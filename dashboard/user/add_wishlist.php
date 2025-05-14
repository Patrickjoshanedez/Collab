<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    exit('Unauthorized');
}

$userId = $_SESSION['user_id'];
$productId = $_POST['product_id'] ?? null;

if ($productId) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO wishlists (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$userId, $productId]);
    echo 'Success';
} else {
    http_response_code(400); // Bad Request
    echo 'Invalid product ID';
}
?>

<?php if (isset($_SESSION['user_id'])): ?>
<script>
document.getElementById('addToWishlist').addEventListener('click', function() {
    const productId = <?= json_encode($id) ?>;

    fetch('add_wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `product_id=${productId}`
    })
    .then(response => {
        if (response.ok) {
            alert('Product added to your wishlist!');
        } else {
            alert('Failed to add product to wishlist.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});
</script>
<?php endif; ?>