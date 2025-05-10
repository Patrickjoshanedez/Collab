<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php
session_start();
include '../../includes/db.php'; // assumes $conn is your mysqli connection

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $birthdate = $_POST['birthdate'] ?: null;
    $contact_no = trim($_POST['Contact_no']);
    $address = trim($_POST['Address']);

    // Profile pic upload
    $profilePicPath = null;
    if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $newName = uniqid('prof_' . $userId . '_') . '.' . $ext;
            $targetPath = $uploadDir . $newName;
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetPath)) {
                $profilePicPath = $targetPath;
            }
        }
    }

    // Build UPDATE query
    $fields = ['Name = ?', 'birthdate = ?', 'Contact_no = ?', 'Address = ?'];
    $params = [$name, $birthdate, $contact_no, $address];

    if ($profilePicPath) {
        $fields[] = 'profile_pic = ?';
        $params[] = $profilePicPath;
    }

    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE User_ID = ?";
    $params[] = $userId;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            // Insert a notification into the notifications table
            $notificationMessage = "Your profile has been updated successfully.";
            $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
            $notifStmt->execute([$userId, $notificationMessage]);

            // Redirect back to the profile page with a success message
            header('Location: user-profile.php?updated=true');
            exit;
        } else {
            // Redirect back with no changes message
            header('Location: user-profile.php?updated=false');
            exit;
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
}

// Fetch user data for display
$stmt = $pdo->prepare('SELECT Name, Contact_no, birthdate, profile_pic, Address FROM users WHERE User_ID = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate static age as of 07/05/2025
$age = '';
if (!empty($user['birthdate'])) {
    $dob = new DateTime($user['birthdate']);
    $ref = new DateTime('2025-07-05');
    $age = $dob->diff($ref)->y;
}
?>

  <div class="flex h-screen">
    <!-- Sidebar -->
    <aside id="sidebar" class="absolute md:relative bg-gray-800 text-white w-64 h-full transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
      <div class="py-4 text-center text-2xl font-bold bg-gray-900"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
      <nav class="mt-4">
        <ul class="space-y-2">
          <li><a href="user-dashboard.php" class="block px-4 py-2 hover:bg-gray-700">Dashboard</a></li>
          <li><a href="user-profile.php" class="block px-4 py-2 bg-gray-700">Profile</a></li>
          <?php if ($_SESSION['role'] === 'Admin'): ?>
            <li><a href="../../dashboard/admin/admin-dashboard.php" class="block px-4 py-2 hover:bg-gray-700">Admin Dashboard</a></li>
          <?php endif; ?>
          <li>
            <button onclick="showLogoutModal()" class="block w-full text-left px-4 py-2 hover:bg-gray-700">
              Logout
            </button>
          </li>
        </ul>
      </nav>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col">
      <!-- Mobile Topbar -->
      <header class="bg-white shadow-md p-2 md:hidden">
        <button id="open-sidebar" class="text-gray-700">&#9776;</button>
      </header>

      <!-- Profile Form -->
      <main class="flex-1 p-6 overflow-auto">
        <div class="bg-white max-w-4xl mx-auto rounded-xl shadow-lg p-8">
          <?php if (isset($_GET['updated']) && $_GET['updated'] === 'true'): ?>
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
              <p>Profile updated successfully!</p>
              <button onclick="this.parentElement.style.display='none'" class="mt-2 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Okay</button>
            </div>
          <?php endif; ?>
          <h1 class="text-2xl font-bold mb-6">Edit Profile</h1>
          <form method="POST" enctype="multipart/form-data">
            <div class="flex flex-col md:flex-row">
              <!-- Profile Image -->
              <div class="md:w-1/3 text-center mb-6 md:mb-0">
                <img id="preview_img" class="rounded-full w-32 h-32 mx-auto mb-2" src="<?= htmlspecialchars($user['profile_pic'] ?: 'default.png') ?>" alt="Profile photo">
                <label class="cursor-pointer inline-block">
                  <span class="sr-only">Choose profile photo</span>
                  <input type="file" name="profile_pic" accept="image/*" onchange="loadFile(event)"
                         class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0 file:bg-gray-100 file:text-gray-700
                                hover:file:bg-gray-200"/>
                </label>
              </div>

              <!-- Form Fields -->
              <div class="md:w-2/3 md:pl-6 space-y-4">
                <div>
                  <label class="block text-gray-700 mb-1">Name</label>
                  <input type="text" name="name" value="<?= htmlspecialchars($user['Name']) ?>" required
                         class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-indigo-500"/>
                </div>
                <div>
                  <label class="block text-gray-700 mb-1">Birthdate</label>
                  <input type="date" name="birthdate" value="<?= htmlspecialchars($user['birthdate']) ?>" required
                         class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-indigo-500"/>
                </div>
                <div>
                  <label class="block text-gray-700 mb-1">Age</label>
                  <input type="text" name="age" value="<?= $age ?>" disabled
                         class="w-full bg-gray-100 border border-gray-300 rounded px-3 py-2"/>
                </div>
                <div>
                  <label class="block text-gray-700 mb-1">Phone</label>
                  <input type="tel" name="Contact_no" value="<?= htmlspecialchars($user['Contact_no']) ?>"
                         class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-indigo-500"/>
                </div>
                <div>
                  <label class="block text-gray-700 mb-1">Address</label>
                  <textarea name="Address" rows="3" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-indigo-500"><?= htmlspecialchars($user['Address']) ?></textarea>
                </div>
                <div class="pt-4">
                  <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Save Changes</button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>

  <!-- Logout Confirmation Modal -->
  <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
      <div class="text-center">
        <svg class="w-12 h-12 text-red-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
        </svg>
        <h2 class="text-lg font-bold mb-2">Are you sure you want to log out?</h2>
        <p class="text-gray-600 mb-4">This action will end your current session.</p>
        <div class="flex justify-center space-x-4">
          <button onclick="confirmLogout()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Yes</button>
          <button onclick="hideLogoutModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">No</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Sidebar toggle
    document.getElementById('open-sidebar').addEventListener('click', function () {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('-translate-x-full');
    });

    // Image preview
    var loadFile = function (event) {
      var output = document.getElementById('preview_img');
      output.src = URL.createObjectURL(event.target.files[0]);
      output.onload = function () {
        URL.revokeObjectURL(output.src);
      }
    };

    // Age calculation
    document.querySelector('input[name="birthdate"]').addEventListener('change', function (e) {
      const birth = new Date(e.target.value);
      const refDate = new Date(2025, 4, 7);
      const diff = new Date(refDate - birth);
      this.form.age.value = Math.abs(diff.getUTCFullYear() - 1970);
    });

    // Show logout modal
    function showLogoutModal() {
      document.getElementById('logoutModal').classList.remove('hidden');
    }

    // Hide logout modal
    function hideLogoutModal() {
      document.getElementById('logoutModal').classList.add('hidden');
    }

    // Confirm logout
    function confirmLogout() {
      window.location.href = 'logout.php';
    }

  </script>
</body>
</html>