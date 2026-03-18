<?php
// includes/header.php
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? $pageTitle . ' — ' : '' ?>FreshMart</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/freshmart/assets/css/style.css">
</head>
<body>

<div class="promo-strip">
  🚚 &nbsp;FREE delivery on orders over UGX 50,000 &nbsp;|&nbsp; 🎁 First order? Use code <strong>FRESH10</strong> for 10% off
</div>

<nav>
  <div class="nav-inner">
    <a class="logo" href="/freshmart/index.php">
      <div class="logo-icon">🛒</div>
      <span class="logo-text">Fresh<span>Mart</span></span>
    </a>
    <form class="nav-search" action="/freshmart/index.php" method="GET">
      <input type="text" name="search" placeholder="Search for groceries, fresh produce..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      <button type="submit">🔍</button>
    </form>
    <div class="nav-actions">
      <?php if (isCustomerLoggedIn()): ?>
        <a class="btn-nav" href="/freshmart/customer/dashboard.php">👤 <?= htmlspecialchars($_SESSION['customer_name']) ?></a>
        <a class="btn-nav" href="/freshmart/customer/logout.php">Sign Out</a>
      <?php else: ?>
        <a class="btn-nav" href="/freshmart/customer/login.php">Sign In</a>
        <a class="btn-nav primary" href="/freshmart/customer/register.php">Register</a>
      <?php endif; ?>
      <a class="cart-btn" href="/freshmart/customer/cart.php">
        🛒<span class="cart-count"><?= $cartCount ?></span>
      </a>
      <a class="btn-nav" href="/freshmart/admin/login.php" style="font-size:12px;color:var(--gray);">Admin</a>
    </div>
  </div>
</nav>

<div class="cat-bar">
  <div class="cat-inner">
    <a class="cat-link <?= !isset($_GET['cat']) ? 'active' : '' ?>" href="/freshmart/index.php">All</a>
    <?php
    $cats = $conn->query("SELECT * FROM categories WHERE is_active=1 ORDER BY id");
    while ($c = $cats->fetch_assoc()):
    ?>
    <a class="cat-link <?= (isset($_GET['cat']) && $_GET['cat'] == $c['id']) ? 'active' : '' ?>"
       href="/freshmart/index.php?cat=<?= $c['id'] ?>"><?= $c['icon'] ?> <?= htmlspecialchars($c['name']) ?></a>
    <?php endwhile; ?>
  </div>
</div>
