<?php
session_start();
include '../../includes/db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch the profile picture for the logged-in user
$userId = $_SESSION['user_id'];
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
  <script src="../script/user-dashboard.js" defer></script>
  <script src="/Collab/js/dark-mode.js" defer></script> <!-- Include dark-mode.js -->
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
      <!-- Dark Mode Toggle -->
      <label>
        <input type="checkbox" id="theme-toggle" hidden>
        <span style="cursor:pointer;">🌓</span>
      </label>
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
      <div class="relative ml-4">
        <button id="wishlistBtn" class="relative px-3">
          ♥
          <span id="wishlistCount" class="absolute -top-1 -right-1 bg-red-600 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">0</span>
        </button>
        <div id="wishlistDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded shadow-lg overflow-hidden">
          <ul>
            <!-- Filled by JS -->
          </ul>
          <div class="p-2 text-center">
            <a href="user-wishlist.php" class="text-indigo-600">Manage Wishlist &raquo;</a>
          </div>
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
        <?php foreach ($products as $row): ?>
          <?php
          // Fetch average rating and review count for the product
          $stmt = $pdo->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS review_count FROM reviews WHERE product_id = ?");
          $stmt->execute([$row['id']]);
          $stats = $stmt->fetch(PDO::FETCH_ASSOC);
          $avgRating = $stats['avg_rating'] ? round($stats['avg_rating'], 1) : 0;
          $reviewCount = $stats['review_count'];
          ?>
          <a href="product_detail.php?id=<?= $row['id'] ?>" class="product-card bg-gray-200 dark:bg-gray-600 p-4 rounded flex flex-col items-center"
             data-category="<?= htmlspecialchars($row['category']) ?>">
            <img src="/Collab/assets/images/<?= htmlspecialchars($row['image']) ?>"
                 class="h-24 object-contain mb-2" alt="">
            <span class="font-semibold"><?= htmlspecialchars($row['name']) ?></span>
            <span class="mt-1">₱<?= number_format($row['price'], 2) ?></span>
            <div class="flex items-center mt-2">
              <?php
              // Render star icons based on the average rating
              $fullStars = floor($avgRating);
              for ($i = 0; $i < $fullStars; $i++): ?>
                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07-3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
              <?php endfor;
              for ($i = $fullStars; $i < 5; $i++): ?>
                <svg class="w-5 h-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07-3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
              <?php endfor; ?>
              <span class="ml-2 text-gray-700"><?= htmlspecialchars($avgRating) ?></span>
              <span class="ml-1 text-gray-500">(<?= $reviewCount ?> reviews)</span>
            </div>
          </a>
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

    $('.wishlist-btn').click(function () {
      const productId = $(this).data('product');
      $.post('add_wishlist.php', { product_id: productId }, function (response) {
        fetchWishlist(); // Update the wishlist count or dropdown
        alert('Added to wishlist!');
      }).fail(function () {
        alert('Failed to add to wishlist.');
      });
    });

    function fetchWishlist() {
      console.log('Fetching wishlist items...'); // Debugging
      $.getJSON('fetch_wishlist.php', function (data) {
        console.log('Wishlist items fetched:', data); // Debugging
        $('#wishlistCount').text(data.count);
        let html = '';
        data.items.forEach(item => {
          html += `
            <li class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 flex justify-between">
              <span>${item.name}</span>
              <a href="user-wishlist.php" class="text-blue-500 text-sm">Edit</a>
            </li>`;
        });
        $('#wishlistDropdown ul').html(html);
      }).fail(function (xhr) {
        console.error('Failed to fetch wishlist items:', xhr.responseText); // Debugging
      });
    }

    // Fetch wishlist when the wishlist button is clicked
    $('#wishlistBtn').click(function (event) {
      console.log('Wishlist button clicked'); // Debugging
      event.stopPropagation();
      $('#wishlistDropdown').toggleClass('hidden');
      fetchWishlist();
    });
    
    $(document).ready(function () {
      // Toggle the wishlist dropdown
      $('#wishlistBtn').click(function (event) {
        console.log('Wishlist button clicked'); // Debugging
        event.stopPropagation(); // Prevent the click from propagating
        $('#wishlistDropdown').toggleClass('hidden'); // Toggle visibility
        fetchWishlist(); // Fetch the wishlist items when the button is clicked
      });

      // Close the dropdown when clicking outside
      $(document).click(function (event) {
        if (!$(event.target).closest('#wishlistBtn, #wishlistDropdown').length) {
          $('#wishlistDropdown').addClass('hidden'); // Hide the dropdown
        }
      });

      // Fetch wishlist items
      function fetchWishlist() {
        console.log('Fetching wishlist items...'); // Debugging
        $.getJSON('fetch_wishlist.php', function (data) {
          console.log('Wishlist items fetched:', data); // Debugging
          $('#wishlistCount').text(data.count); // Update the wishlist count
          let html = '';
          data.items.forEach(item => {
            html += `
              <li class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 flex justify-between">
                <span>${item.name}</span>
                <a href="user-wishlist.php" class="text-blue-500 text-sm">Edit</a>
              </li>`;
          });
          $('#wishlistDropdown ul').html(html); // Populate the dropdown
        }).fail(function (xhr) {
          console.error('Failed to fetch wishlist items:', xhr.responseText); // Debugging
        });
      }
    });
  </script>
</body>
</html>
