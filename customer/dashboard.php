<?php
require_once '../includes/config.php';
requireCustomerLogin();
$pageTitle = 'My Dashboard';

$cid = $_SESSION['customer_id'];
$customer = $conn->query("SELECT * FROM customers WHERE id = $cid")->fetch_assoc();

// Order stats
$stats = $conn->query("
    SELECT
        COUNT(*) AS total_orders,
        SUM(total_amount) AS total_spent,
        SUM(CASE WHEN status='pending' OR status='confirmed' OR status='in_transit' THEN 1 ELSE 0 END) AS active_orders
    FROM orders WHERE customer_id = $cid
")->fetch_assoc();

// Recent orders
$orders = $conn->query("
    SELECT o.*, COUNT(oi.id) AS item_count
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    WHERE o.customer_id = $cid
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 5
");

include '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <div class="breadcrumb"><a href="/freshmart/index.php">Home</a> / My Account</div>
    <h1>👋 Welcome back, <?= htmlspecialchars($customer['first_name']) ?>!</h1>
    <p>Manage your orders, account details, and loyalty points</p>
  </div>
</div>

<div class="dash-wrap">

  <!-- Stats -->
  <div class="dash-grid">
    <div class="dash-card"><div class="d-icon">📦</div><div class="d-num"><?= $stats['total_orders'] ?></div><div class="d-label">Total Orders</div></div>
    <div class="dash-card"><div class="d-icon">⭐</div><div class="d-num"><?= number_format($customer['loyalty_points']) ?></div><div class="d-label">Loyalty Points</div></div>
    <div class="dash-card"><div class="d-icon">🚚</div><div class="d-num"><?= $stats['active_orders'] ?></div><div class="d-label">Active Orders</div></div>
    <div class="dash-card"><div class="d-icon">💰</div><div class="d-num" style="font-size:16px;">UGX <?= number_format($stats['total_spent'] ?? 0) ?></div><div class="d-label">Total Spent</div></div>
  </div>

  <!-- Quick Links -->
  <div style="display:flex;gap:12px;margin-bottom:28px;flex-wrap:wrap;">
    <a href="/freshmart/index.php" class="qa-btn">🛒 Shop Now</a>
    <a href="/freshmart/customer/orders.php" class="qa-btn">📦 All Orders</a>
    <a href="/freshmart/customer/cart.php" class="qa-btn">🛒 My Cart</a>
    <a href="/freshmart/customer/profile.php" class="qa-btn">👤 Edit Profile</a>
  </div>

  <!-- Recent Orders -->
  <div class="table-card">
    <div class="table-card-head">
      <span>Recent Orders</span>
      <a href="/freshmart/customer/orders.php" class="qa-btn" style="font-size:12px;">View All</a>
    </div>
    <?php if ($orders->num_rows === 0): ?>
      <div style="padding:32px;text-align:center;color:var(--gray);">
        <div style="font-size:40px;margin-bottom:12px;">🛒</div>
        <p>No orders yet. <a href="/freshmart/index.php" style="color:var(--green);font-weight:600;">Start shopping!</a></p>
      </div>
    <?php else: ?>
    <table class="data-table">
      <thead><tr><th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th></tr></thead>
      <tbody>
        <?php while ($o = $orders->fetch_assoc()): ?>
        <tr>
          <td><strong><?= $o['order_number'] ?></strong></td>
          <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
          <td><?= $o['item_count'] ?> items</td>
          <td>UGX <?= number_format($o['total_amount']) ?></td>
          <td><?= ucwords(str_replace('_',' ',$o['payment_method'])) ?></td>
          <td>
            <?php
            $statusClass = match($o['status']) {
                'delivered' => 'badge-delivered',
                'cancelled' => 'badge-cancelled',
                default => 'badge-pending'
            };
            ?>
            <span class="badge <?= $statusClass ?>"><?= ucfirst(str_replace('_',' ',$o['status'])) ?></span>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- Account Details -->
  <div class="table-card">
    <div class="table-card-head">Account Details</div>
    <div style="padding:24px;display:grid;grid-template-columns:1fr 1fr;gap:20px;">
      <div><div style="font-size:12px;color:var(--gray);margin-bottom:4px;">Full Name</div><div style="font-weight:600;"><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></div></div>
      <div><div style="font-size:12px;color:var(--gray);margin-bottom:4px;">Email</div><div style="font-weight:600;"><?= htmlspecialchars($customer['email']) ?></div></div>
      <div><div style="font-size:12px;color:var(--gray);margin-bottom:4px;">Phone</div><div style="font-weight:600;"><?= htmlspecialchars($customer['phone'] ?: 'Not set') ?></div></div>
      <div><div style="font-size:12px;color:var(--gray);margin-bottom:4px;">City</div><div style="font-weight:600;"><?= htmlspecialchars($customer['city'] ?: 'Not set') ?></div></div>
      <div style="grid-column:1/-1"><div style="font-size:12px;color:var(--gray);margin-bottom:4px;">Delivery Address</div><div style="font-weight:600;"><?= htmlspecialchars($customer['address'] ?: 'Not set') ?></div></div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
