<?php
session_start();
include '../../includes/db.php';

$id = $_GET['id'] ?? 0;
if (!$id || !is_numeric($id)) {
    die('Invalid product ID.');
}

// Fetch product details
$stmt = $pdo->prepare("SELECT name, description, price, category, image FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die('Product not found.');
}

// Calculate average rating and total reviews
$stmt = $pdo->prepare("
    SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews 
    FROM reviews 
    WHERE product_id = ?
");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$avg_rating = $data['avg_rating'] !== null ? round($data['avg_rating'], 1) : 0;
$total_reviews = $data['total_reviews'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Product Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">

<header class="bg-white shadow p-4 flex justify-between items-center">
    <a href="user-dashboard.php" class="text-blue-600 font-semibold hover:underline">← Dashboard</a>
    <h1 class="text-xl font-bold">Product Detail</h1>
</header>

<main class="p-6">
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg overflow-hidden flex flex-col md:flex-row">
        <!-- Product Image and Details (Left) -->
        <div class="md:w-1/2 p-6">
            <img src="/Collab/assets/images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-64 object-cover rounded-lg">
            <h2 class="mt-4 text-2xl font-semibold"><?= htmlspecialchars($product['name']) ?></h2>
            <p class="mt-2 text-sm text-gray-600"><?= htmlspecialchars($product['category']) ?></p>
            <p class="mt-4 text-gray-700"><?= htmlspecialchars($product['description']) ?></p>
            <p class="mt-4 text-xl font-bold">₱<?= number_format($product['price'], 2) ?></p>

            <!-- Average Rating -->
            <div class="flex items-center mt-4">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?php if ($i <= floor($avg_rating)): ?>
                        <span class="text-yellow-500 text-xl">&#9733;</span>
                    <?php else: ?>
                        <span class="text-gray-300 text-xl">&#9733;</span>
                    <?php endif; ?>
                <?php endfor; ?>
                <span class="ml-2 text-gray-800"><?= $avg_rating ?> / 5</span>
                <span class="ml-4 text-sm text-gray-600">(<?= $total_reviews ?> reviews)</span>
            </div>

            <!-- Wishlist Button -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <button id="addToWishlist" class="mt-4 px-4 py-2 bg-red-600 text-white font-semibold rounded hover:bg-red-700">
                    Add to Wishlist
                </button>
            <?php else: ?>
                <p class="mt-4 text-gray-600">Please <a href="login.php" class="text-blue-500 underline">log in</a> to add this product to your wishlist.</p>
            <?php endif; ?>
        </div>

        <!-- Review Submission Form (Right, shown only if logged in) -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="md:w-1/2 p-6 bg-gray-50 border-l border-gray-200">
            <h3 class="text-xl font-semibold mb-4">Write a Review</h3>
            <form id="reviewForm" method="post" action="submit_review.php">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($id) ?>">
                <div class="mb-4">
                    <label for="rating" class="block text-sm font-medium text-gray-700">Rating:</label>
                    <select id="rating" name="rating" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select a rating</option>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Very Good</option>
                        <option value="3">3 - Good</option>
                        <option value="2">2 - Fair</option>
                        <option value="1">1 - Poor</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="review_text" class="block text-sm font-medium text-gray-700">Comment:</label>
                    <textarea id="review_text" name="review_text" rows="4" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Your review"></textarea>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Submit Review</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- Customer Reviews Section -->
    <section class="max-w-4xl mx-auto mt-10 p-6 bg-white rounded-xl shadow">
        <h3 class="text-xl font-semibold mb-4 text-indigo-700">Customer Reviews</h3>
        <div id="reviewList" class="space-y-4">
            <!-- Reviews will be dynamically loaded here -->
        </div>
    </section>
</main>

<script>
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const rating = form.rating.value.trim();
    const reviewText = form.review_text.value.trim();
    if (!rating || !reviewText) {
        alert('Please fill out both rating and review.');
        return;
    }
    fetch('submit_review.php', {
        method: 'POST',
        body: new FormData(form)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Your review has been submitted successfully!');
            form.reset();
            loadReviews(); // Refresh the review list
        } else {
            alert(data.error || 'Failed to submit review.');
        }
    });
});

// Function to load and display the reviews via AJAX
function loadReviews() {
    fetch('get_reviews.php?product_id=<?= $id ?>')
        .then(res => res.text())
        .then(html => {
            document.getElementById('reviewList').innerHTML = html;
        });
}

// Load reviews on page load
window.addEventListener('DOMContentLoaded', loadReviews);
</script>

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

</body>
</html>
