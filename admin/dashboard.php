<?php
require_once '../includes/config.php';
requireAdminLogin();
$pageTitle = 'Dashboard Overview';

// Stats
$todayRevenue = $conn->query("SELECT COALESCE(SUM(total_amount),0) AS r FROM orders WHERE DATE(created_at)=CURDATE() AND payment_status='paid'")->fetch_assoc()['r'];
$todayOrders  = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'];
$totalCustomers = $conn->query("SELECT COUNT(*) AS c FROM customers")->fetch_assoc()['c'];
$lowStock = $conn->query("SELECT COUNT(*) AS c FROM products WHERE stock <= min_stock AND is_active=1")->fetch_assoc()['c'];

// Recent orders
$recentOrders = $conn->query("
    SELECT o.*, CONCAT(c.first_name,' ',c.last_name) AS cname
    FROM orders o
    LEFT JOIN customers c ON c.id = o.customer_id
    ORDER BY o.created_at DESC LIMIT 6
");

// Top products
$topProducts = $conn->query("
    SELECT p.name, p.emoji, SUM(oi.quantity) AS sold
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    GROUP BY oi.product_id
    ORDER BY sold DESC LIMIT 4
");

// Low stock alerts
$lowStockItems = $conn->query("
    SELECT p.*, c.name AS cat_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.stock <= p.min_stock AND p.is_active=1
    ORDER BY p.stock ASC LIMIT 5
");

include 'includes/sidebar.php';
?>

<!-- STATS -->
<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon green">💰</div>
    <div>
      <h3>UGX <?= number_format($todayRevenue) ?></h3>
      <p>Today's Revenue</p>
      <div class="stat-change up">↑ Live data</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue">📋</div>
    <div>
      <h3><?= $todayOrders ?></h3>
      <p>Orders Today</p>
      <div class="stat-change up">↑ Updated now</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange">👥</div>
    <div>
      <h3><?= number_format($totalCustomers) ?></h3>
      <p>Total Customers</p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red">⚠️</div>
    <div>
      <h3><?= $lowStock ?></h3>
      <p>Low Stock Alerts</p>
      <div class="stat-change down">↓ Needs attention</div>
    </div>
  </div>
</div>

<div class="admin-grid">
  <!-- Recent Orders -->
  <div class="admin-card">
    <div class="admin-card-head">
      <h3>Recent Orders</h3>
      <a href="/freshmart/admin/orders.php" class="qa-btn" style="font-size:12px;padding:6px 12px;">View All</a>
    </div>
    <table class="admin-table">
      <thead><tr><th>Order</th><th>Customer</th><th>Amount</th><th>Status</th></tr></thead>
      <tbody>
        <?php while ($o = $recentOrders->fetch_assoc()):
          $sc = match($o['status']) { 'delivered'=>'done', 'cancelled'=>'cancelled', default=>'pending' };
        ?>
        <tr>
          <td><strong><?= $o['order_number'] ?></strong></td>
          <td><?= htmlspecialchars($o['cname'] ?? 'Guest') ?></td>
          <td>UGX <?= number_format($o['total_amount']) ?></td>
          <td><span class="status-badge <?= $sc ?>"><?= ucfirst(str_replace('_',' ',$o['status'])) ?></span></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Right column -->
  <div style="display:flex;flex-direction:column;gap:24px;">
    <!-- Quick Actions -->
    <div class="admin-card">
      <div class="admin-card-head"><h3>Quick Actions</h3></div>
      <div style="padding:16px;display:flex;gap:10px;flex-wrap:wrap;">
        <a href="/freshmart/admin/products.php?action=add" class="qa-btn">➕ Add Product</a>
        <a href="/freshmart/admin/promotions.php" class="qa-btn">🏷️ New Promo</a>
        <a href="/freshmart/admin/inventory.php" class="qa-btn">📦 Inventory</a>
        <a href="/freshmart/admin/reports.php" class="qa-btn">📊 Reports</a>
        <a href="/freshmart/admin/staff.php" class="qa-btn">👤 Staff</a>
      </div>
    </div>

    <!-- Top Products -->
    <div class="admin-card">
      <div class="admin-card-head"><h3>Top Selling Products</h3></div>
      <div style="padding:16px 22px;display:flex;flex-direction:column;gap:14px;">
        <?php
        $maxSold = 1;
        $topArr = [];
        while ($tp = $topProducts->fetch_assoc()) { $topArr[] = $tp; if ($tp['sold'] > $maxSold) $maxSold = $tp['sold']; }
        $colors = ['var(--green)', 'var(--accent)', 'var(--red)', '#4a90d9'];
        foreach ($topArr as $i => $tp):
          $pct = round(($tp['sold'] / $maxSold) * 100);
        ?>
        <div style="display:flex;align-items:center;gap:12px;">
          <span style="font-size:22px;"><?= $tp['emoji'] ?></span>
          <div style="flex:1">
            <div style="font-size:13px;font-weight:600;margin-bottom:4px;"><?= htmlspecialchars($tp['name']) ?></div>
            <div class="progress-bar-bg"><div class="progress-bar-fill" style="width:<?= $pct ?>%;background:<?= $colors[$i] ?>"></div></div>
          </div>
          <span style="font-size:12px;color:var(--gray);font-weight:600;"><?= $tp['sold'] ?> sold</span>
        </div>
        <?php endforeach; ?>
        <?php if (empty($topArr)): ?><p style="color:var(--gray);font-size:13px;">No sales data yet.</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Low Stock -->
<?php if ($lowStockItems->num_rows > 0): ?>
<div class="admin-card">
  <div class="admin-card-head" style="background:#fff8ec;">
    <h3>⚠️ Low Stock Alerts</h3>
    <a href="/freshmart/admin/inventory.php" class="qa-btn" style="font-size:12px;padding:6px 12px;">View All</a>
  </div>
  <table class="admin-table">
    <thead><tr><th>Product</th><th>Category</th><th>Current Stock</th><th>Min Level</th><th>Action</th></tr></thead>
    <tbody>
      <?php while ($ls = $lowStockItems->fetch_assoc()): ?>
      <tr>
        <td><?= $ls['emoji'] ?> <?= htmlspecialchars($ls['name']) ?></td>
        <td><?= htmlspecialchars($ls['cat_name']) ?></td>
        <td style="color:<?= $ls['stock'] <= 5 ? 'var(--red)' : '#b07a0e' ?>;font-weight:600;"><?= $ls['stock'] ?> units</td>
        <td><?= $ls['min_stock'] ?> units</td>
        <td><a href="/freshmart/admin/inventory.php" class="qa-btn" style="font-size:11px;padding:4px 10px;">Restock</a></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php
// CSS for status badges used in admin
echo '<style>
.status-badge{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.status-badge.done{background:var(--green-light);color:var(--green);}
.status-badge.pending{background:var(--accent-light);color:#7a5200;}
.status-badge.cancelled{background:var(--red-light);color:var(--red);}
</style>';
include 'includes/footer.php';
?>
