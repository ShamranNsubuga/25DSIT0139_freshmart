<?php
require_once '../includes/config.php';
requireCustomerLogin();
$pageTitle = 'Order Placed!';

$order_number = sanitize($conn, $_GET['order'] ?? $_SESSION['last_order'] ?? '');

if (empty($order_number)) {
    header('Location: /freshmart/index.php');
    exit;
}

$cid = $_SESSION['customer_id'];
$order = $conn->query("
    SELECT o.*, CONCAT(c.first_name,' ',c.last_name) AS cname
    FROM orders o
    JOIN customers c ON c.id = o.customer_id
    WHERE o.order_number = '$order_number' AND o.customer_id = $cid
")->fetch_assoc();

if (!$order) {
    header('Location: /freshmart/index.php');
    exit;
}

$items = $conn->query("
    SELECT oi.*, p.name, p.emoji
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = {$order['id']}
");

include '../includes/header.php';
?>

<div style="max-width:680px;margin:0 auto;padding:48px 24px;text-align:center;">

  <!-- Success Icon -->
  <div style="width:90px;height:90px;background:var(--green-light);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:44px;margin:0 auto 24px;">✅</div>

  <h1 style="font-family:var(--font-head);font-size:32px;color:var(--dark);margin-bottom:10px;">Order Placed!</h1>
  <p style="color:var(--gray);font-size:16px;margin-bottom:8px;">Thank you, <strong><?= htmlspecialchars($order['cname']) ?></strong>! Your order has been received.</p>
  <div style="background:var(--green-light);border:1px solid #b7e0c8;border-radius:var(--radius-sm);padding:12px 20px;display:inline-block;margin-bottom:32px;">
    <span style="font-size:13px;color:var(--gray);">Order Number</span><br>
    <strong style="font-family:monospace;font-size:22px;color:var(--green);"><?= $order['order_number'] ?></strong>
  </div>

  <!-- Order Details Card -->
  <div class="table-card" style="text-align:left;margin-bottom:24px;">
    <div class="table-card-head">Order Details</div>
    <div style="padding:20px 24px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
      <div><div style="font-size:12px;color:var(--gray);margin-bottom:3px;">Date</div><strong><?= date('D M d, Y — H:i', strtotime($order['created_at'])) ?></strong></div>
      <div><div style="font-size:12px;color:var(--gray);margin-bottom:3px;">Payment Method</div><strong><?= ucwords(str_replace('_',' ',$order['payment_method'])) ?></strong></div>
      <div><div style="font-size:12px;color:var(--gray);margin-bottom:3px;">Total Paid</div><strong style="color:var(--green);font-size:18px;">UGX <?= number_format($order['total_amount']) ?></strong></div>
      <div><div style="font-size:12px;color:var(--gray);margin-bottom:3px;">Status</div><span class="badge badge-pending">Pending</span></div>
      <div style="grid-column:1/-1"><div style="font-size:12px;color:var(--gray);margin-bottom:3px;">Delivery Address</div><strong><?= htmlspecialchars($order['delivery_address']) ?></strong></div>
    </div>
  </div>

  <!-- Items -->
  <div class="table-card" style="text-align:left;margin-bottom:32px;">
    <div class="table-card-head">Items Ordered</div>
    <?php while ($item = $items->fetch_assoc()): ?>
    <div style="display:flex;align-items:center;gap:14px;padding:13px 24px;border-bottom:1px solid var(--border);">
      <div style="width:40px;height:40px;background:var(--gray-light);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:22px;"><?= $item['emoji'] ?></div>
      <div style="flex:1;">
        <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($item['name']) ?></div>
        <div style="font-size:12px;color:var(--gray);">Qty: <?= $item['quantity'] ?> × UGX <?= number_format($item['unit_price']) ?></div>
      </div>
      <strong style="color:var(--green);">UGX <?= number_format($item['quantity'] * $item['unit_price']) ?></strong>
    </div>
    <?php endwhile; ?>
  </div>

  <!-- What's Next -->
  <div style="background:var(--accent-light);border:1px solid #f5e0c0;border-radius:var(--radius);padding:20px 24px;text-align:left;margin-bottom:32px;">
    <h3 style="font-size:15px;font-weight:600;margin-bottom:12px;">🚀 What happens next?</h3>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <div style="display:flex;gap:12px;align-items:flex-start;font-size:13px;"><span style="background:var(--accent);color:white;width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;min-width:22px;">1</span><span>We confirm your order and start preparing it</span></div>
      <div style="display:flex;gap:12px;align-items:flex-start;font-size:13px;"><span style="background:var(--accent);color:white;width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;min-width:22px;">2</span><span>Your items are packed fresh and handed to our rider</span></div>
      <div style="display:flex;gap:12px;align-items:flex-start;font-size:13px;"><span style="background:var(--accent);color:white;width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;min-width:22px;">3</span><span>Delivery to your address — estimated <strong>30–60 minutes</strong></span></div>
    </div>
  </div>

  <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
    <a href="/freshmart/customer/orders.php" class="btn-hero primary">📦 Track My Orders</a>
    <a href="/freshmart/index.php" class="btn-hero outline" style="background:transparent;border-color:var(--green);color:var(--green);">🛒 Continue Shopping</a>
  </div>

</div>

<style>
.badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.badge-pending { background: var(--accent-light); color: #7a5200; }
.badge-delivered { background: var(--green-light); color: var(--green); }
</style>

<?php include '../includes/footer.php'; ?>
