<?php
session_start();
include '../../includes/db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch the profile picture for the logged-in user
$stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE User_ID = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Store the profile picture in the session
$_SESSION['profile_pic'] = $user['profile_pic'] ?? 'default.png';

// Load all products for client-side filtering
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $birthdate = $_POST['birthdate'];
    $contact_no = $_POST['Contact_no'];
    $address = $_POST['Address'];
    $userId = $_SESSION['user_id'];

    // Update the user's profile in the database
    $stmt = $pdo->prepare("UPDATE users SET Name = ?, birthdate = ?, Contact_no = ?, Address = ? WHERE User_ID = ?");
    $stmt->execute([$name, $birthdate, $contact_no, $address, $userId]);

    if ($stmt->rowCount() > 0) {
        // Insert a notification into the notifications table
        $notificationMessage = "Your profile was updated successfully.";
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $notifStmt->execute([$userId, $notificationMessage]);

        // Redirect back to the profile page with a success message
        header('Location: user-profile.php?updated=true');
        exit;
    } else {
        // Redirect back with no changes message
        header('Location: user-profile.php?updated=false');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>SurePlus+ Shop</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
  <!-- Header -->
  <header class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 shadow">
    <!-- Left Section: Welcome Message and Profile Picture -->
    <div class="flex items-center space-x-4">
      <a href="/Collab/dashboard/user/user-profile.php" class="relative group">
        <img src="/Collab/dashboard/user/<?= htmlspecialchars($_SESSION['profile_pic'] ?? 'uploads/default.png') ?>" 
             alt="Profile Picture" 
             class="h-10 w-10 rounded-full object-cover transition-transform duration-300 group-hover:scale-110">
        <span class="absolute inset-0 rounded-full bg-black bg-opacity-30 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
      </a>
      <span class="text-lg font-medium text-gray-900 dark:text-gray-100">
        Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?>
      </span>
    </div>

    <!-- Right Section: Navigation Links -->
    <nav class="flex space-x-6">
      <a href="#" class="hover:underline">Shop</a>
      <a href="#" class="hover:underline">Stories</a>
      <a href="#" class="hover:underline">About</a>
      <button onclick="toggleDarkMode()">🌓</button>
      <div class="relative">
        <button id="notifBtn" class="relative px-3">
          🔔
          <span id="notifCount" class="absolute -top-1 -right-1 bg-red-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">0</span>
        </button>
        <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded shadow-lg overflow-hidden">
          <ul>
            <!-- Notification items inserted by AJAX -->
          </ul>
        </div>
      </div>
    </nav>
  </header>

  <!-- Hero -->
  <section class="bg-gray-100 dark:bg-gray-700 text-center py-10">
  <main class="flex p-6 gap-6">
    <!-- Filters -->
    <aside class="w-1/4 space-y-4 text-left"> <!-- Added text-left class -->
      <h3 class="font-semibold">Categories</h3>
      <?php foreach(['Furniture','Electronics','Toys','Utensils','Pots','Bicycle'] as $cat): ?>
        <label class="inline-flex items-center text-left"> <!-- Added text-left class -->
          <input type="checkbox" class="category-checkbox form-checkbox h-4 w-4 text-blue-600"
                 value="<?= $cat ?>" checked>
          <span class="ml-2"><?= $cat ?></span>
        </label><br>
      <?php endforeach; ?>
    </aside>

    <!-- Product Grid -->
    <section class="w-3/4">

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="productGrid">
        <?php foreach($products as $row): ?>
          <div class="product-card bg-gray-200 dark:bg-gray-600 p-4 rounded flex flex-col items-center"
               data-category="<?= htmlspecialchars($row['category']) ?>">
            <img src="/Collab/assets/images/<?= htmlspecialchars($row['image']) ?>"
                 class="h-24 object-contain mb-2" alt="">
            <span class="font-semibold"><?= htmlspecialchars($row['name']) ?></span>
            <span class="mt-1">₱<?= number_format($row['price'],2) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

  <footer class="p-6 text-center bg-gray-100 dark:bg-gray-800">
    <p class="text-sm">© 2025 SurePlus+. All rights reserved.</p>
  </footer>

  <script>
    // Dark mode
    function toggleDarkMode(){
      document.documentElement.classList.toggle('dark');
      localStorage.theme = document.documentElement.classList.contains('dark')?'dark':'light';
    }
    if (localStorage.theme==='dark') document.documentElement.classList.add('dark');

    // Category filter
    function filterProducts(){
      const sel = $('.category-checkbox:checked').map((_,el)=>el.value).get();
      $('#productGrid .product-card').each(function(){
        $(this).toggle( sel.includes($(this).data('category')) );
      });
      $('#count').text( $('#productGrid .product-card:visible').length + ' products' );
    }

    $(function(){
      $('.category-checkbox').change(filterProducts);
      filterProducts(); // initial
    });

    function fetchNotifications() {
      $.ajax({
        url: '/Collab/dashboard/user/fetch_notification.php', // Correct path
        dataType: 'json',
        success: function (data) {
          console.log(data); // Debugging: Log the response
          $('#notifCount').text(data.unreadCount);
          $('#notifDropdown ul').html(data.htmlList);
        },
        error: function () {
          console.error('Failed to fetch notifications');
        }
      });
    }

    // Poll notifications every 10 seconds
    setInterval(fetchNotifications, 10000);

    // Fetch notifications when the notification button is clicked
    $('#notifBtn').click(fetchNotifications);

    // Mark a notification as read
    $(document).on('click', '.mark-read', function () {
      const notifId = $(this).data('id');
      console.log('Marking notification as read:', notifId); // Debugging

      $.post('mark_notification_read.php', { id: notifId }, function (response) {
        console.log('Notification marked as read:', response); // Debugging
        fetchNotifications(); // Refresh notifications
      }).fail(function (xhr) {
        console.error('Failed to mark notification as read', xhr.responseText); // Debugging
      });
    });

    $(document).ready(function () {
      // Toggle the notification dropdown
      $('#notifBtn').click(function () {
        console.log('Notification button clicked'); // Debugging
        $('#notifDropdown').toggleClass('hidden');
      });

      // Close the dropdown when clicking outside
      $(document).click(function (event) {
        if (!$(event.target).closest('#notifBtn, #notifDropdown').length) {
          $('#notifDropdown').addClass('hidden');
        }
      });

      // Fetch notifications when the notification button is clicked
      $('#notifBtn').click(fetchNotifications);

      // Fetch notifications periodically
      setInterval(fetchNotifications, 10000);

      // Mark a notification as read
      $(document).on('click', '.mark-read', function () {
        const notifId = $(this).data('id');
        $.post('mark_notification_read.php', { id: notifId }, function () {
          fetchNotifications();
        }).fail(function () {
          console.error('Failed to mark notification as read');
        });
      });
    });
  </script>
</body>
</html>
