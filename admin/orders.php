<?php
require_once '../includes/config.php';
requireAdminLogin();
$pageTitle = 'Order Management';

// ─── UPDATE STATUS ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $oid    = (int)$_POST['order_id'];
    $status = sanitize($conn, $_POST['status']);
    $conn->query("UPDATE orders SET status='$status' WHERE id=$oid");
    header('Location: /freshmart/admin/orders.php?msg=updated');
    exit;
}

$msg = isset($_GET['msg']) ? 'Order status updated successfully.' : '';

// ─── FILTERS ───
$where = "WHERE 1=1";
if (!empty($_GET['status'])) $where .= " AND o.status='" . sanitize($conn,$_GET['status']) . "'";
if (!empty($_GET['search'])) {
    $s = sanitize($conn, $_GET['search']);
    $where .= " AND (o.order_number LIKE '%$s%' OR CONCAT(c.first_name,' ',c.last_name) LIKE '%$s%')";
}
if (!empty($_GET['date'])) {
    $d = sanitize($conn, $_GET['date']);
    $where .= " AND DATE(o.created_at)='$d'";
}

$orders = $conn->query("
    SELECT o.*, CONCAT(c.first_name,' ',c.last_name) AS cname, c.phone AS cphone,
           COUNT(oi.id) AS item_count
    FROM orders o
    LEFT JOIN customers c ON c.id = o.customer_id
    LEFT JOIN order_items oi ON oi.order_id = o.id
    $where
    GROUP BY o.id
    ORDER BY o.created_at DESC
");

include 'includes/sidebar.php';
?>
<style>
.status-badge{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.status-badge.done{background:var(--green-light);color:var(--green);}
.status-badge.pending{background:var(--accent-light);color:#7a5200;}
.status-badge.cancelled{background:var(--red-light);color:var(--red);}
</style>

<?php if ($msg): ?><div class="alert alert-success" style="margin-bottom:20px;">✓ <?= $msg ?></div><?php endif; ?>

<div class="filter-row">
  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;width:100%;align-items:center;">
    <input type="text" name="search" placeholder="🔍 Search order # or customer..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="width:240px;">
    <select name="status">
      <option value="">All Statuses</option>
      <option value="pending"    <?= ($_GET['status']??'') === 'pending'    ? 'selected':'' ?>>Pending</option>
      <option value="confirmed"  <?= ($_GET['status']??'') === 'confirmed'  ? 'selected':'' ?>>Confirmed</option>
      <option value="in_transit" <?= ($_GET['status']??'') === 'in_transit' ? 'selected':'' ?>>In Transit</option>
      <option value="delivered"  <?= ($_GET['status']??'') === 'delivered'  ? 'selected':'' ?>>Delivered</option>
      <option value="cancelled"  <?= ($_GET['status']??'') === 'cancelled'  ? 'selected':'' ?>>Cancelled</option>
    </select>
    <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
    <button type="submit" class="qa-btn">Filter</button>
    <a href="/freshmart/admin/orders.php" class="qa-btn">Clear</a>
    <span style="margin-left:auto;font-size:13px;color:var(--gray);"><?= $orders->num_rows ?> orders found</span>
  </form>
</div>

<div class="admin-card">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Order #</th><th>Customer</th><th>Date</th><th>Items</th>
        <th>Total</th><th>Payment</th><th>Status</th><th>Update Status</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($o = $orders->fetch_assoc()):
        $sc = match($o['status']) {
          'delivered'=>'done','cancelled'=>'cancelled',
          'in_transit'=>'pending','confirmed'=>'pending',default=>'pending'
        };
      ?>
      <tr>
        <td><strong><?= $o['order_number'] ?></strong></td>
        <td>
          <div style="font-weight:600;"><?= htmlspecialchars($o['cname'] ?? 'Guest') ?></div>
          <div style="font-size:11px;color:var(--gray);"><?= htmlspecialchars($o['cphone'] ?? '') ?></div>
        </td>
        <td><?= date('M d, Y', strtotime($o['created_at'])) ?><br><span style="font-size:11px;color:var(--gray);"><?= date('H:i', strtotime($o['created_at'])) ?></span></td>
        <td><?= $o['item_count'] ?></td>
        <td><strong>UGX <?= number_format($o['total_amount']) ?></strong></td>
        <td><?= ucwords(str_replace('_',' ',$o['payment_method'])) ?></td>
        <td><span class="status-badge <?= $sc ?>"><?= ucfirst(str_replace('_',' ',$o['status'])) ?></span></td>
        <td>
          <form method="POST" style="display:flex;gap:6px;align-items:center;">
            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
            <select name="status" style="padding:5px 8px;border-radius:6px;border:1.5px solid var(--border);font-family:var(--font-body);font-size:12px;">
              <option value="pending"    <?= $o['status']==='pending'    ?'selected':'' ?>>Pending</option>
              <option value="confirmed"  <?= $o['status']==='confirmed'  ?'selected':'' ?>>Confirmed</option>
              <option value="in_transit" <?= $o['status']==='in_transit' ?'selected':'' ?>>In Transit</option>
              <option value="delivered"  <?= $o['status']==='delivered'  ?'selected':'' ?>>Delivered</option>
              <option value="cancelled"  <?= $o['status']==='cancelled'  ?'selected':'' ?>>Cancelled</option>
            </select>
            <button type="submit" class="qa-btn" style="font-size:11px;padding:4px 10px;">Save</button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include 'includes/footer.php'; ?>
