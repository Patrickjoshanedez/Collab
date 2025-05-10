<?php
include __DIR__ . '/../../includes/db.php';
include 'admin-dashboard-validate.php';

// Ensure `category` column exists:
// ALTER TABLE products ADD category VARCHAR(50) DEFAULT '';

$uploadDir = realpath(__DIR__ . '/../../assets/images') . DIRECTORY_SEPARATOR;
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle form submissions
if (isset($_POST['add_product']) || isset($_POST['edit_product'])) {
    $name        = $_POST['name'];
    $price       = $_POST['price'];
    $description = $_POST['description'];
    $category    = $_POST['category'];

    // Image upload
    $imageName = null;
    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        if ($file['error'] === UPLOAD_ERR_OK && getimagesize($file['tmp_name'])) {
            $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if (in_array($ext, $allowed)) {
                $newName = uniqid('img_', true) . '.' . $ext;
                $target  = $uploadDir . $newName;
                if (move_uploaded_file($file['tmp_name'], $target)) {
                    $imageName = $newName;
                } else {
                    echo "<p class='text-red-600'>Error uploading image.</p>";
                }
            }
        }
    }

    if (isset($_POST['add_product'])) {
        $stmt = $pdo->prepare(
            "INSERT INTO products (name, price, image, description, category)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$name, $price, $imageName, $description, $category]);
    } else {
        // edit
        $id   = $_POST['product_id'];
        // fetch old image
        $old = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $old->execute([$id]);
        $oldImage = $old->fetchColumn();
        if ($imageName && $oldImage && file_exists($uploadDir . $oldImage)) {
            unlink($uploadDir . $oldImage);
        }
        // if no new upload, keep old
        $imageName = $imageName ?: $oldImage;
        $stmt = $pdo->prepare(
            "UPDATE products
                SET name = ?, price = ?, image = ?, description = ?, category = ?
              WHERE id = ?"
        );
        $stmt->execute([$name, $price, $imageName, $description, $category, $id]);
    }
    header('Location: admin-dashboard.php');
    exit;
}

if (isset($_POST['delete_product'])) {
    $id = $_POST['delete_id'];
    $old = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $old->execute([$id]);
    $oldImage = $old->fetchColumn();
    if ($oldImage && file_exists($uploadDir . $oldImage)) {
        unlink($uploadDir . $oldImage);
    }
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    header('Location: admin-dashboard.php');
    exit;
}

$editMode = false;
if (isset($_GET['edit'])) {
    $editMode    = true;
    $id          = $_GET['edit'];
    $stmt        = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);
}

$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard</title>
  <script>tailwind.config={darkMode:'class'}</script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
  <!-- Header -->
  <header class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 shadow">
    <h1 class="text-2xl font-bold">SurePlus+ Admin</h1>
    <div class="flex items-center space-x-4">
      <button onclick="toggleDarkMode()">🌓</button>
      <a href="../user/user-dashboard.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Go to User Dashboard
      </a>
    </div>
  </header>

  <main class="p-6 flex flex-col md:flex-row gap-6">
    <!-- Form -->
    <aside class="md:w-1/3 bg-gray-100 dark:bg-gray-800 p-6 rounded shadow">
      <h2 class="text-xl font-semibold mb-4"><?= $editMode ? 'Edit' : 'Add' ?> Product</h2>
      <form method="post" enctype="multipart/form-data" class="space-y-4">
        <?php if ($editMode): ?>
          <input type="hidden" name="product_id" value="<?= htmlspecialchars($editProduct['id']) ?>">
        <?php endif; ?>

        <!-- Name -->
        <div>
          <label class="block mb-1">Name</label>
          <input name="name" required
                 value="<?= $editMode ? htmlspecialchars($editProduct['name']) : '' ?>"
                 class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-700">
        </div>
        <!-- Price -->
        <div>
          <label class="block mb-1">Price</label>
          <input type="number" step="0.01" name="price" required
                 value="<?= $editMode ? htmlspecialchars($editProduct['price']) : '' ?>"
                 class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-700">
        </div>
        <!-- Category -->
        <div>
          <label class="block mb-1">Category</label>
          <select name="category" required
                  class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-700">
            <?php foreach(['Furniture','Electronics','Toys','Utensils','Pots','Bicycle'] as $cat): ?>
              <option value="<?= $cat ?>"
                <?= $editMode && $editProduct['category']===$cat ? 'selected' : '' ?>>
                <?= $cat ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- Image -->
        <div>
          <label class="block mb-1">Image <?= $editMode ? '(leave blank to keep)' : '' ?></label>
          <input type="file" name="image" class="w-full">
        </div>
        <!-- Description -->
        <div>
          <label class="block mb-1">Description</label>
          <textarea name="description" rows="4"
                    class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-700"><?= $editMode ? htmlspecialchars($editProduct['description']) : '' ?></textarea>
        </div>
        <!-- Buttons -->
        <div class="flex gap-4">
          <?php if ($editMode): ?>
            <button type="submit" name="edit_product" class="px-4 py-2 bg-blue-600 text-white rounded">Update</button>
            <a href="admin-dashboard.php" class="px-4 py-2 bg-gray-300 rounded">Cancel</a>
          <?php else: ?>
            <button type="submit" name="add_product" class="px-4 py-2 bg-green-600 text-white rounded">Add</button>
          <?php endif; ?>
        </div>
      </form>
    </aside>

    <!-- Table -->
    <section class="md:w-2/3 bg-gray-100 dark:bg-gray-800 p-6 rounded shadow overflow-x-auto">
      <h2 class="text-xl font-semibold mb-4 text-center">Products</h2>
      <table class="min-w-full border-collapse text-center">
        <thead class="bg-gray-200 dark:bg-gray-700">
          <tr>
            <th class="px-3 py-2">ID</th>
            <th class="px-3 py-2">Image</th>
            <th class="px-3 py-2">Name</th>
            <th class="px-3 py-2">Price</th>
            <th class="px-3 py-2">Category</th>
            <th class="px-3 py-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($products as $p): ?>
          <tr class="border-t border-gray-300 dark:border-gray-700">
            <td class="px-3 py-2"><?= $p['id'] ?></td>
            <td class="px-3 py-2">
              <?php if ($p['image']): ?>
                <img src="/Collab/assets/images/<?= htmlspecialchars($p['image']) ?>"
                     class="h-12 w-12 object-contain" alt="">
              <?php endif; ?>
            </td>
            <td class="px-3 py-2"><?= htmlspecialchars($p['name']) ?></td>
            <td class="px-3 py-2">₱<?= number_format($p['price'],2) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($p['category']) ?></td>
            <td class="px-3 py-2 space-x-2">
              <a href="?edit=<?= $p['id'] ?>" class="text-indigo-600">Edit</a>
              <form method="post" class="inline" onsubmit="return confirm('Delete?')">
                <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                <button name="delete_product" class="text-red-600">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach;?>
        </tbody>
      </table>
    </section>
  </main>

  <footer class="p-4 text-center bg-gray-100 dark:bg-gray-800">
    © 2025 SurePlus+
  </footer>

  <script>
    function toggleDarkMode(){
      document.documentElement.classList.toggle('dark');
      localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    }
    if (localStorage.theme==='dark') document.documentElement.classList.add('dark');
  </script>
</body>
</html>
