<?php
require_once '../includes/config.php';
requireAdminLogin();
$pageTitle = 'Customer Management';

// Toggle active
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE customers SET is_active = NOT is_active WHERE id=$id");
    header('Location: /freshmart/admin/customers.php');
    exit;
}

$search = sanitize($conn, $_GET['search'] ?? '');
$where = "WHERE 1=1";
if ($search) $where .= " AND (c.first_name LIKE '%$search%' OR c.last_name LIKE '%$search%' OR c.email LIKE '%$search%')";

$customers = $conn->query("
    SELECT c.*,
           COUNT(o.id) AS order_count,
           COALESCE(SUM(o.total_amount),0) AS total_spent
    FROM customers c
    LEFT JOIN orders o ON o.customer_id = c.id
    $where
    GROUP BY c.id
    ORDER BY c.created_at DESC
");

include 'includes/sidebar.php';
?>
<style>
.status-badge{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.status-badge.done{background:var(--green-light);color:var(--green);}
.status-badge.cancelled{background:var(--red-light);color:var(--red);}
</style>

<div class="filter-row">
  <form method="GET" style="display:flex;gap:12px;width:100%;">
    <input type="text" name="search" placeholder="🔍 Search by name or email..." value="<?= htmlspecialchars($search) ?>" style="width:300px;">
    <button type="submit" class="qa-btn">Search</button>
    <a href="/freshmart/admin/customers.php" class="qa-btn">Clear</a>
    <span style="margin-left:auto;font-size:13px;color:var(--gray);"><?= $customers->num_rows ?> customers</span>
  </form>
</div>

<div class="admin-card">
  <table class="admin-table">
    <thead>
      <tr><th>Name</th><th>Email</th><th>Phone</th><th>City</th><th>Orders</th><th>Total Spent</th><th>Loyalty Pts</th><th>Joined</th><th>Status</th><th>Action</th></tr>
    </thead>
    <tbody>
      <?php while ($c = $customers->fetch_assoc()): ?>
      <tr>
        <td><strong><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></strong></td>
        <td><?= htmlspecialchars($c['email']) ?></td>
        <td><?= htmlspecialchars($c['phone'] ?: '—') ?></td>
        <td><?= htmlspecialchars($c['city'] ?: '—') ?></td>
        <td><?= $c['order_count'] ?></td>
        <td>UGX <?= number_format($c['total_spent']) ?></td>
        <td>⭐ <?= number_format($c['loyalty_points']) ?></td>
        <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
        <td><span class="status-badge <?= $c['is_active'] ? 'done' : 'cancelled' ?>"><?= $c['is_active'] ? 'Active' : 'Suspended' ?></span></td>
        <td>
          <a href="?toggle=<?= $c['id'] ?>" class="qa-btn <?= $c['is_active'] ? 'btn-danger' : '' ?>" style="font-size:11px;padding:4px 10px;"
             onclick="return confirm('<?= $c['is_active'] ? 'Suspend' : 'Activate' ?> this customer?')">
            <?= $c['is_active'] ? 'Suspend' : 'Activate' ?>
          </a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include 'includes/footer.php'; ?>
