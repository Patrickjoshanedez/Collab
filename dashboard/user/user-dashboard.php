<!-- File: index.php -->
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SurePlus+ Shop</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <?php include '../../includes/db.php'; ?>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">

  <!-- Header -->
  <header class="flex items-center justify-between p-4 shadow-md bg-white dark:bg-gray-800">
    <div class="text-2xl font-bold">SurePlus+</div>
    <nav class="flex space-x-6">
      <a href="#" class="hover:underline">Shop</a>
      <a href="#" class="hover:underline">Stories</a>
      <a href="#" class="hover:underline">About</a>
      <button onclick="toggleDarkMode()" class="ml-4">🌓</button>
    </nav>
  </header>

  <!-- Hero Section -->
  <section class="bg-gray-100 dark:bg-gray-700 py-10 text-center">
    <h1 class="text-4xl font-bold mb-2">Shop Items</h1>
    <p class="text-gray-600 dark:text-gray-300">At a very low price</p>
  </section>

  <!-- Main Content -->
  <main class="flex p-6">
    <!-- Filters -->
    <aside class="w-1/4 pr-6">
      <h2 class="font-semibold mb-4">Filters</h2>
      <div class="space-y-2">
        <label class="block"><input type="checkbox" checked> Furniture</label>
        <label class="block"><input type="checkbox"> Electronics</label>
        <label class="block"><input type="checkbox"> Toys</label>
        <label class="block"><input type="checkbox"> Utensils</label>
        <label class="block"><input type="checkbox"> Pots</label>
        <label class="block"><input type="checkbox"> Bicycle</label>
      </div>
    </aside>

    <!-- Products Grid -->
    <section class="w-3/4">
      <div class="flex justify-between mb-4">
        <div>Showing products</div>
        <select class="border dark:border-gray-600 rounded p-1">
          <option>Popular</option>
          <option>Newest</option>
          <option>Price: Low to High</option>
          <option>Price: High to Low</option>
        </select>
      </div>

      <div class="grid grid-cols-3 gap-6 product-grid">
        <?php
        // Initial load – first 6 products
        $limit = 6;
        $sql   = "SELECT * FROM products ORDER BY id DESC LIMIT :limit";
        $stmt  = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($products) {
            foreach ($products as $row) {
                echo '<div class="bg-gray-200 dark:bg-gray-600 h-48 rounded flex flex-col items-center justify-center p-4">';
                echo '<img src="assets/images/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['name']) . '" class="mb-2 h-24 object-contain">';
                echo '<span class="font-semibold">' . htmlspecialchars($row['name']) . '</span>';
                echo '<span class="mt-1">$' . number_format($row['price'], 2) . '</span>';
                echo '</div>';
            }
        } else {
            echo '<p>No products found.</p>';
        }
        ?>
      </div>

      <div class="flex justify-center mt-8">
        <button id="loadMore" class="border dark:border-gray-600 px-6 py-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700">Load more products</button>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="mt-12 p-6 bg-gray-100 dark:bg-gray-800 text-center">
    <p class="text-sm">© 2025 SurePlus+. All rights reserved.</p>
  </footer>

  <script src="../js/script.js"></script>
</body>
</html>