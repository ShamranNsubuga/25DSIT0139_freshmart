<?php
require_once '../includes/config.php';
requireCustomerLogin();
$pageTitle = 'My Orders';

$cid = $_SESSION['customer_id'];
$orders = $conn->query("
    SELECT o.*, COUNT(oi.id) AS item_count
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    WHERE o.customer_id = $cid
    GROUP BY o.id
    ORDER BY o.created_at DESC
");

include '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <div class="breadcrumb"><a href="/freshmart/index.php">Home</a> / <a href="/freshmart/customer/dashboard.php">Dashboard</a> / Orders</div>
    <h1>📦 My Orders</h1>
    <p>Track all your FreshMart orders</p>
  </div>
</div>

<div class="dash-wrap">
  <div class="table-card">
    <div class="table-card-head"><span>Order History (<?= $orders->num_rows ?>)</span></div>
    <?php if ($orders->num_rows === 0): ?>
      <div style="padding:40px;text-align:center;color:var(--gray);">
        <div style="font-size:48px;margin-bottom:12px;">📭</div>
        <p>No orders yet. <a href="/freshmart/index.php" style="color:var(--green);font-weight:600;">Start shopping!</a></p>
      </div>
    <?php else: ?>
    <table class="data-table">
      <thead>
        <tr>
          <th>Order #</th>
          <th>Date</th>
          <th>Items</th>
          <th>Total</th>
          <th>Payment</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($o = $orders->fetch_assoc()): ?>
        <?php
          $statusClass = match($o['status']) {
              'delivered' => 'badge-delivered',
              'cancelled' => 'badge-cancelled',
              'in_transit' => 'badge-transit',
              default => 'badge-pending'
          };
          $statusLabel = match($o['status']) {
              'in_transit' => 'In Transit',
              default => ucfirst($o['status'])
          };
        ?>
        <tr>
          <td><strong><?= $o['order_number'] ?></strong></td>
          <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
          <td><?= $o['item_count'] ?> items</td>
          <td><strong>UGX <?= number_format($o['total_amount']) ?></strong></td>
          <td><?= ucwords(str_replace('_',' ', $o['payment_method'])) ?></td>
          <td><span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
