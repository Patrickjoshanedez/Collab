<?php
include 'includes/db.php'; // Include your database connection file

// Fetch the latest products (limit to 3 for the "Latest Arrivals" section)
$stmt = $pdo->query("SELECT name, price, image FROM products ORDER BY id DESC LIMIT 3");
$latestProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
  >
  <title>SurePlus+</title>
  <link rel="stylesheet" href="css/index-style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

  <?php
  if (isset($_GET['message']) && $_GET['message'] === 'login_required') {
      echo '<p style="color: red; text-align: center; font-size: 1.2rem; margin-top: 1rem; font-weight: bold;">You need to login first to access this page.</p>';
  }
  ?>

  <!-- Sticky Header -->
  <header id="header">
    <div class="nav-container">
      <strong>SurePlus+</strong>
      <nav style="display:flex; gap:1rem; align-items:center;">
        <a href="#">Shop</a>
        <a href="pages/login.php">Login</a>
        <a href="#">About</a>
        <a href="#"><svg style="width:1rem;vertical-align:middle"><!--icon--></svg></a>
        <!-- Dark Mode Toggle -->
        <label>
          <input type="checkbox" id="theme-toggle" hidden>
          <span style="cursor:pointer;">🌓</span>
        </label>
      </nav>
    </div>
  </header>

  <!-- Hero 1 -->
  <section>
    <h1>Preloved items for all</h1>
    <p class="subtitle">Sa SurePlus+ sure na mam manage mo ang iyong stocks</p>
    <button onclick="window.location.href='pages/login.php?message=login_required'">Shop All</button>
    <div style="margin-top:2rem;">
      <!-- Exact filename & no hidden class -->
      <img src="images/banner.jpeg" alt="Banner" class="hero-img">
    </div>
  </section>

  <!-- Hero 2 / Latest Arrivals -->
  <section>
    <h1>Our Latest Arrivals</h1>
    <p class="subtitle">Preloved items at your disposal</p>
    <button>Shop All</button>
    <div class="grid-3">
      <?php foreach ($latestProducts as $product): ?>
        <div class="card">
          <img src="assets/images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
          <div class="card-content">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <p>₱<?= number_format($product['price'], 2) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="footer-grid">
      <div>
        <h3>Shop</h3>
        <ul>
          <li><a href="#">All Products</a></li>
          <li><a href="#">Categories</a></li>
          <li><a href="#">Best Sellers</a></li>
        </ul>
      </div>
      <div>
        <h3>Company</h3>
        <ul>
          <li><a href="#">About Us</a></li>
          <li><a href="#">Stories</a></li>
          <li><a href="#">Careers</a></li>
        </ul>
      </div>
      <div>
        <h3>Support</h3>
        <ul>
          <li><a href="#">Help Center</a></li>
        </ul>
      </div>
      <div>
        <h3>Stay Connected</h3>
        <div class="newsletter">
          <input type="email" placeholder="Your email">
          <button>Subscribe</button>
        </div>
        <div class="social-icons">
          <a href="#" class="me-2"><i class="bi bi-facebook"></i></a>
          <a href="#" class="me-2"><i class="bi bi-twitter"></i></a>
          <a href="#"><i class="bi bi-instagram"></i></a>
        </div>
      </div>
    </div>
    <p style="text-align:center; margin-top:2rem; color:var(--muted);">
      © 2025 SurePlus+. All rights reserved.
    </p>
  </footer>

  <script>
    // Sticky header shrink on scroll :contentReference[oaicite:9]{index=9}
    const header = document.getElementById('header');
    window.addEventListener('scroll', () => {
      header.classList.toggle('shrink', window.scrollY > 50);
    });

    // Scroll-reveal for cards :contentReference[oaicite:10]{index=10}
    const cards = document.querySelectorAll('.card');
    const obs = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
          obs.unobserve(e.target);
        }
      });
    }, { threshold: 0.1 });
    cards.forEach(card => obs.observe(card));
  </script>

  <!-- Include the external dark mode script -->
  <script src="js/dark-mode.js"></script>
</body>
</html>
