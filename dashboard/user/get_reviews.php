<?php
include '../../includes/db.php';

$product_id = intval($_GET['product_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT r.rating, r.review_text, r.review_date, u.Name AS username
    FROM reviews r
    JOIN users u ON r.user_id = u.User_ID
    WHERE r.product_id = ?
    ORDER BY r.review_date DESC
");
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($reviews as $review): ?>
    <div class="p-4 mb-4 bg-gray-100 rounded">
        <div class="text-sm text-gray-600">
            <?= date('Y-m-d', strtotime($review['review_date'])) ?>
        </div>
        <div class="text-lg font-semibold">
            <?= htmlspecialchars($review['username'], ENT_QUOTES) ?>
        </div>
        <div class="flex">
            <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                <span class="text-yellow-500">★</span>
            <?php endfor; ?>
            <?php for ($i = $review['rating']; $i < 5; $i++): ?>
                <span class="text-gray-300">★</span>
            <?php endfor; ?>
        </div>
        <p class="mt-2">
            <?= nl2br(htmlspecialchars($review['review_text'], ENT_QUOTES)) ?>
        </p>
    </div>
<?php endforeach; ?>