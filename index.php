<?php
// ─── config.php already contains the DB connection ───
// db_connect.php is a standalone alternative — include either one.
// Here we use config.php because it also provides helper functions.
require_once 'includes/config.php';
// If you ever want to use the dedicated connection file instead, swap the
// line above for:  require_once 'db_connect.php';

$pageTitle = 'Fresh Groceries Delivered';

// ─── FETCH FEATURE STRIP CONTENT FROM tbl_content ───────────────────────────
// Selecting only active rows, ordered by sort_order so admins can control
// the display sequence directly from phpMyAdmin.
$features = $conn->query("
    SELECT id, title, description, image_url
    FROM   tbl_content
    WHERE  is_active = 1
    ORDER  BY sort_order ASC, id ASC
");

// ─── FILTERS ───
$where = "WHERE p.is_active = 1";
$params = [];

if (!empty($_GET['search'])) {
    $s = sanitize($conn, $_GET['search']);
    $where .= " AND (p.name LIKE '%$s%' OR c.name LIKE '%$s%')";
}
if (!empty($_GET['cat'])) {
    $catId = (int)$_GET['cat'];
    $where .= " AND p.category_id = $catId";
}
if (!empty($_GET['deals'])) {
    $where .= " AND p.old_price IS NOT NULL";
}
if (!empty($_GET['badge'])) {
    $b = sanitize($conn, $_GET['badge']);
    $where .= " AND p.badge = '$b'";
}

$products = $conn->query("
    SELECT p.*, c.name AS cat_name, c.icon AS cat_icon
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $where
    ORDER BY p.id ASC
");

// Categories with product count
$categories = $conn->query("
    SELECT c.*, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
    WHERE c.is_active = 1
    GROUP BY c.id
");

// Deals / sale products
$deals = $conn->query("
    SELECT p.*, c.name AS cat_name FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1 AND p.old_price IS NOT NULL
    LIMIT 4
");

include 'includes/header.php';
?>

<!-- HERO -->
<div class="hero">
  <div class="hero-inner">
    <div class="hero-badge">🌱 Fresh Everyday</div>
    <h1>Fresh. Fast.<br><span>Delivered to You.</span></h1>
    <p>Shop the finest groceries from local farms and top brands — delivered straight to your door in Kampala.</p>
    <div class="hero-btns">
      <a href="#products" class="btn-hero primary">🛒 Shop Now</a>
      <?php if (isCustomerLoggedIn()): ?>
        <a href="/freshmart/customer/orders.php" class="btn-hero outline">📦 My Orders</a>
      <?php else: ?>
        <a href="/freshmart/customer/login.php" class="btn-hero outline">👤 Sign In</a>
      <?php endif; ?>
    </div>
    <div class="hero-stats">
      <div class="hero-stat"><div class="num">5,000+</div><div class="lbl">Products</div></div>
      <div class="hero-stat"><div class="num">30min</div><div class="lbl">Avg. Delivery</div></div>
      <div class="hero-stat"><div class="num">98%</div><div class="lbl">Satisfaction</div></div>
      <div class="hero-stat"><div class="num">50k+</div><div class="lbl">Happy Customers</div></div>
    </div>
  </div>
</div>

<!-- CATEGORIES -->
<div class="section">
  <div class="section-head">
    <h2 class="section-title">Shop by <span>Category</span></h2>
    <a href="/freshmart/index.php" class="view-all">Browse All →</a>
  </div>
  <div class="cat-grid">
    <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
    <a class="cat-card" href="/freshmart/index.php?cat=<?= $cat['id'] ?>">
      <div class="icon"><?= $cat['icon'] ?></div>
      <div class="name"><?= htmlspecialchars($cat['name']) ?></div>
      <div class="count"><?= $cat['product_count'] ?>+ items</div>
    </a>
    <?php endwhile; ?>
  </div>
</div>

<!-- PRODUCTS -->
<div class="section" id="products" style="padding-top:0">
  <div class="section-head">
    <h2 class="section-title">
      <?php if (!empty($_GET['search'])): ?>
        🔍 Results for "<span><?= htmlspecialchars($_GET['search']) ?></span>"
      <?php else: ?>
        🔥 <span>Featured</span> Products
      <?php endif; ?>
    </h2>
    <a href="/freshmart/index.php" class="view-all">See All →</a>
  </div>

  <?php if ($products->num_rows === 0): ?>
    <div class="alert alert-warning">No products found. <a href="/freshmart/index.php">Clear filters</a></div>
  <?php else: ?>
  <div class="product-grid">
    <?php while ($p = $products->fetch_assoc()): ?>
    <div class="product-card">
      <div class="product-img">
        <?php if ($p['badge'] === 'sale'): ?><span class="badge-sale">SALE</span>
        <?php elseif ($p['badge'] === 'new'): ?><span class="badge-new">NEW</span><?php endif; ?>
        <span style="font-size:52px"><?= $p['emoji'] ?></span>
      </div>
      <div class="product-body">
        <div class="product-cat"><?= htmlspecialchars($p['cat_name']) ?></div>
        <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
        <div class="product-weight"><?= htmlspecialchars($p['weight']) ?></div>
        <div class="product-foot">
          <div>
            <span class="price">UGX <?= number_format($p['price']) ?></span>
            <?php if ($p['old_price']): ?>
              <span class="price-old">UGX <?= number_format($p['old_price']) ?></span>
            <?php endif; ?>
          </div>
          <?php if ($p['stock'] > 0): ?>
            <button class="add-btn" onclick="addToCart(<?= $p['id'] ?>)">+</button>
          <?php else: ?>
            <span style="font-size:11px;color:var(--red);font-weight:600;">Out of stock</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
  <?php endif; ?>
</div>

<!-- FEATURES — rendered dynamically from tbl_content in the database -->
<!-- To add, remove, or edit a feature, simply update tbl_content in phpMyAdmin. -->
<div class="features-strip">
  <div class="features-inner">

    <?php if ($features && $features->num_rows > 0): ?>
      <?php while ($f = $features->fetch_assoc()): ?>
        <div class="feature">
          <div class="feature-icon"><?= htmlspecialchars($f['image_url']) ?></div>
          <div>
            <h4><?= htmlspecialchars($f['title']) ?></h4>
            <p><?= htmlspecialchars($f['description']) ?></p>
          </div>
        </div>
      <?php endwhile; ?>

    <?php else: ?>
      <!-- "No records found" fallback — shown when tbl_content is empty -->
      <div class="feature" style="grid-column:1/-1;justify-content:center;opacity:.6;">
        <div class="feature-icon">📭</div>
        <div>
          <h4>No features listed yet</h4>
          <p>Add rows to <strong>tbl_content</strong> in phpMyAdmin to display them here.</p>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<!-- DEALS SECTION -->
<div class="section">
  <div class="deals-banner">
    <div>
      <div style="background:rgba(245,166,35,0.2);border:1px solid rgba(245,166,35,0.3);color:var(--accent);border-radius:20px;padding:5px 14px;font-size:11px;font-weight:700;letter-spacing:0.5px;display:inline-flex;align-items:center;gap:6px;margin-bottom:14px;text-transform:uppercase;">⚡ Flash Deals</div>
      <h2>Weekend <span>Mega Sale!</span></h2>
      <p>Up to 40% off on fresh produce, dairy & more. Hurry — limited time only!</p>
      <a href="/freshmart/index.php?deals=1" class="btn-hero primary" style="margin-top:20px;display:inline-flex;">Shop Deals →</a>
    </div>
    <div class="countdown">
      <div class="count-box"><div class="count-num" id="cd-h">03</div><div class="count-lbl">Hours</div></div>
      <div class="count-sep">:</div>
      <div class="count-box"><div class="count-num" id="cd-m">47</div><div class="count-lbl">Minutes</div></div>
      <div class="count-sep">:</div>
      <div class="count-box"><div class="count-num" id="cd-s">22</div><div class="count-lbl">Seconds</div></div>
    </div>
  </div>
</div>

<!-- SALE PRODUCTS -->
<div class="section" style="padding-top:0">
  <div class="section-head">
    <h2 class="section-title">💰 Special <span>Deals</span></h2>
    <a href="/freshmart/index.php?deals=1" class="view-all">All Deals →</a>
  </div>
  <div class="product-grid">
    <?php while ($p = $deals->fetch_assoc()): ?>
    <div class="product-card">
      <div class="product-img">
        <span class="badge-sale">SALE</span>
        <span style="font-size:52px"><?= $p['emoji'] ?></span>
      </div>
      <div class="product-body">
        <div class="product-cat"><?= htmlspecialchars($p['cat_name']) ?></div>
        <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
        <div class="product-weight"><?= htmlspecialchars($p['weight']) ?></div>
        <div class="product-foot">
          <div>
            <span class="price">UGX <?= number_format($p['price']) ?></span>
            <span class="price-old">UGX <?= number_format($p['old_price']) ?></span>
          </div>
          <button class="add-btn" onclick="addToCart(<?= $p['id'] ?>)">+</button>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
