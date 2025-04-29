// File: fetch_products.php
<?php
include 'includes/db.php';

if (isset($_POST['page'])) {
    $page   = (int)$_POST['page'];
    $limit  = 6;
    $offset = ($page - 1) * $limit;

    $sql    = "SELECT * FROM products ORDER BY id DESC LIMIT $offset, $limit";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Output each product card
            echo '<div class="bg-gray-200 dark:bg-gray-600 h-48 rounded flex flex-col items-center justify-center p-4">';
            echo '<img src="assets/images/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '" class="mb-2 h-24 object-contain">';
            echo '<span class="font-semibold">' . htmlspecialchars($row['name']) . '</span>';
            echo '<span class="mt-1">$' . number_format($row['price'], 2) . '</span>';
            echo '</div>';
        }
    }
}
?>