<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    // Display a warning message and redirect
    echo "<script>
        alert('You do not have admin privileges.');
        window.location.href = '../../pages/login.php';
    </script>";
    exit;
}

include __DIR__ . '/../../includes/db.php';

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

        // After admin adds a product
        $msg = "New item '" . htmlspecialchars($name, ENT_QUOTES) . "' was added by " . htmlspecialchars($_SESSION['username'], ENT_QUOTES);
        $admins = $pdo->query("SELECT User_ID FROM users WHERE Role = 'Admin'");
        foreach ($admins as $row) {
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, admin_only) VALUES (?, ?, 1)");
            if ($stmt->execute([$row['User_ID'], $msg])) {
                echo "Notification sent to Admin ID: " . $row['User_ID'] . "<br>";
            } else {
                echo "Failed to send notification to Admin ID: " . $row['User_ID'] . "<br>";
            }
        }
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