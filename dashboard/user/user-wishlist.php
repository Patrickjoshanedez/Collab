<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Handle removals
if (isset($_POST['remove_wishlist'])) {
    $stmt = $pdo->prepare("DELETE FROM wishlists WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['remove_id'], $userId]);
    header('Location: user-wishlist.php');
    exit;
}

// Fetch wishlist items
$stmt = $pdo->prepare("
    SELECT w.id, p.name, p.category 
    FROM wishlists w 
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
");
$stmt->execute([$userId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debugging: Log the fetched items
error_log(print_r($items, true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Wishlist</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<main class="p-6">
  <div class="bg-white dark:bg-gray-800 p-6 rounded shadow-lg mx-auto max-w-2xl">
    <h2 class="text-xl font-semibold mb-4">Your Wishlist</h2>
    <?php if (empty($items)): ?>
      <p>No items in your wishlist.</p>
    <?php else: ?>
      <ul class="divide-y divide-gray-200 dark:divide-gray-700">
        <?php foreach ($items as $item): ?>
        <li class="flex justify-between items-center py-2">
          <span><?= htmlspecialchars($item['name']) ?> <em class="text-sm text-gray-500">(<?= htmlspecialchars($item['category']) ?>)</em></span>
          <form method="post" onsubmit="return confirm('Remove this item?')">
            <input type="hidden" name="remove_id" value="<?= $item['id'] ?>">
            <button name="remove_wishlist" class="text-red-600">Remove</button>
          </form>
        </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</main>
</body>
</html>