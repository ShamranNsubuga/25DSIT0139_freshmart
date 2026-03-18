<?php
require_once '../includes/config.php';
requireAdminLogin();
$pageTitle = 'Reports & Analytics';

// Summary stats
$monthRevenue   = $conn->query("SELECT COALESCE(SUM(total_amount),0) AS r FROM orders WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE()) AND payment_status='paid'")->fetch_assoc()['r'];
$monthOrders    = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetch_assoc()['c'];
$avgOrderValue  = $conn->query("SELECT COALESCE(AVG(total_amount),0) AS a FROM orders WHERE payment_status='paid'")->fetch_assoc()['a'];
$returnRate     = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status='cancelled'")->fetch_assoc()['c'];
$totalOrders    = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$returnRatePct  = $totalOrders > 0 ? round(($returnRate / $totalOrders) * 100, 1) : 0;

// Monthly revenue for chart (last 6 months)
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $ts = strtotime("-$i months");
    $m  = date('m', $ts);
    $y  = date('Y', $ts);
    $lbl = date('M', $ts);
    $rev = $conn->query("SELECT COALESCE(SUM(total_amount),0) AS r FROM orders WHERE MONTH(created_at)=$m AND YEAR(created_at)=$y AND payment_status='paid'")->fetch_assoc()['r'];
    $monthlyData[] = ['label' => $lbl, 'revenue' => $rev];
}

$maxRev = max(array_column($monthlyData, 'revenue')) ?: 1;

// Top products
$topProducts = $conn->query("
    SELECT p.name, p.emoji, SUM(oi.quantity) AS sold, SUM(oi.quantity * oi.unit_price) AS revenue
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    GROUP BY oi.product_id
    ORDER BY sold DESC LIMIT 5
");

// Orders by status
$byStatus = $conn->query("SELECT status, COUNT(*) AS c FROM orders GROUP BY status");

include 'includes/sidebar.php';
?>

<div class="stats-row" style="margin-bottom:28px;">
  <div class="stat-card"><div class="stat-icon green">💰</div><div><h3>UGX <?= number_format($monthRevenue) ?></h3><p>This Month's Revenue</p></div></div>
  <div class="stat-card"><div class="stat-icon blue">📋</div><div><h3><?= $monthOrders ?></h3><p>Orders This Month</p></div></div>
  <div class="stat-card"><div class="stat-icon orange">🛒</div><div><h3>UGX <?= number_format($avgOrderValue) ?></h3><p>Avg. Order Value</p></div></div>
  <div class="stat-card"><div class="stat-icon red">↩️</div><div><h3><?= $returnRatePct ?>%</h3><p>Cancellation Rate</p></div></div>
</div>

<div class="admin-grid" style="margin-bottom:24px;">
  <!-- Revenue Chart -->
  <div class="admin-card">
    <div class="admin-card-head"><h3>Monthly Revenue (Last 6 Months)</h3></div>
    <div style="padding:24px;">
      <div style="display:flex;align-items:flex-end;gap:12px;height:180px;">
        <?php foreach ($monthlyData as $md):
          $pct = $maxRev > 0 ? round(($md['revenue'] / $maxRev) * 100) : 0;
          $isCurrentMonth = $md['label'] === date('M');
        ?>
        <div style="display:flex;flex-direction:column;align-items:center;gap:6px;flex:1;">
          <div style="font-size:11px;color:var(--gray);font-weight:600;">UGX <?= $md['revenue'] > 0 ? number_format($md['revenue']/1000).'k' : '0' ?></div>
          <div style="background:<?= $isCurrentMonth ? 'var(--green-mid)' : 'var(--green)' ?>;
                      width:100%;border-radius:4px 4px 0 0;
                      height:<?= max(4, $pct) ?>%;
                      <?= $isCurrentMonth ? 'outline:2px solid var(--green);' : '' ?>
                      min-height:4px;transition:height 0.3s;"></div>
          <div style="font-size:11px;color:<?= $isCurrentMonth ? 'var(--green)' : 'var(--gray)' ?>;font-weight:<?= $isCurrentMonth ? '700' : '400' ?>;"><?= $md['label'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:20px;border-top:1px solid var(--border);padding-top:16px;flex-wrap:wrap;gap:8px;">
        <button class="qa-btn" onclick="window.print()">📥 Print Report</button>
        <button class="qa-btn">📊 Export CSV</button>
        <button class="qa-btn">📧 Email Report</button>
      </div>
    </div>
  </div>

  <!-- Orders by Status -->
  <div style="display:flex;flex-direction:column;gap:24px;">
    <div class="admin-card">
      <div class="admin-card-head"><h3>Orders by Status</h3></div>
      <div style="padding:20px 22px;display:flex;flex-direction:column;gap:12px;">
        <?php
        $statusColors = ['delivered'=>'var(--green)','pending'=>'var(--accent)','cancelled'=>'var(--red)','in_transit'=>'#4a90d9','confirmed'=>'#7c5cbf'];
        while ($s = $byStatus->fetch_assoc()):
          $color = $statusColors[$s['status']] ?? 'var(--gray)';
          $pct   = $totalOrders > 0 ? round(($s['c'] / $totalOrders) * 100) : 0;
        ?>
        <div>
          <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
            <span style="font-weight:600;"><?= ucfirst(str_replace('_',' ',$s['status'])) ?></span>
            <span style="color:var(--gray);"><?= $s['c'] ?> (<?= $pct ?>%)</span>
          </div>
          <div class="progress-bar-bg" style="width:100%;height:8px;">
            <div class="progress-bar-fill" style="width:<?= $pct ?>%;background:<?= $color ?>;height:100%;"></div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-head"><h3>Top Selling Products</h3></div>
      <table class="admin-table">
        <thead><tr><th>Product</th><th>Sold</th><th>Revenue</th></tr></thead>
        <tbody>
          <?php while ($tp = $topProducts->fetch_assoc()): ?>
          <tr>
            <td><?= $tp['emoji'] ?> <?= htmlspecialchars($tp['name']) ?></td>
            <td><?= $tp['sold'] ?></td>
            <td>UGX <?= number_format($tp['revenue']) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
