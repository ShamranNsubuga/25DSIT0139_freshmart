<?php
require_once '../includes/config.php';
requireAdminLogin();
$pageTitle = 'Inventory Management';

// ─── UPDATE STOCK ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $pid      = (int)$_POST['product_id'];
    $stock    = (int)$_POST['stock'];
    $min_stock = (int)$_POST['min_stock'];
    $conn->query("UPDATE products SET stock=$stock, min_stock=$min_stock WHERE id=$pid");
    header('Location: /freshmart/admin/inventory.php?msg=updated');
    exit;
}

$msg = isset($_GET['msg']) ? 'Stock updated successfully.' : '';

// Stats
$totalUnits  = $conn->query("SELECT COALESCE(SUM(stock),0) AS s FROM products WHERE is_active=1")->fetch_assoc()['s'];
$lowCount    = $conn->query("SELECT COUNT(*) AS c FROM products WHERE stock<=min_stock AND is_active=1")->fetch_assoc()['c'];
$outOfStock  = $conn->query("SELECT COUNT(*) AS c FROM products WHERE stock=0 AND is_active=1")->fetch_assoc()['c'];
$totalProducts = $conn->query("SELECT COUNT(*) AS c FROM products WHERE is_active=1")->fetch_assoc()['c'];

$products = $conn->query("
    SELECT p.*, c.name AS cat_name
    FROM products p
    LEFT JOIN categories c ON c.id=p.category_id
    WHERE p.is_active=1
    ORDER BY p.stock ASC
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

<div class="stats-row" style="margin-bottom:28px;">
  <div class="stat-card"><div class="stat-icon green">📦</div><div><h3><?= number_format($totalUnits) ?></h3><p>Total Units in Stock</p></div></div>
  <div class="stat-card"><div class="stat-icon blue">🛍️</div><div><h3><?= $totalProducts ?></h3><p>Total Products</p></div></div>
  <div class="stat-card"><div class="stat-icon red">⚠️</div><div><h3><?= $lowCount ?></h3><p>Low Stock Items</p></div></div>
  <div class="stat-card"><div class="stat-icon orange">🚫</div><div><h3><?= $outOfStock ?></h3><p>Out of Stock</p></div></div>
</div>

<div class="admin-card">
  <div class="admin-card-head">
    <h3>Inventory — All Products</h3>
    <a href="/freshmart/admin/products.php?action=add" class="qa-btn" style="font-size:12px;padding:6px 12px;">+ Add Product</a>
  </div>
  <table class="admin-table">
    <thead>
      <tr><th>Product</th><th>Category</th><th>Current Stock</th><th>Min Level</th><th>Status</th><th>Update Stock</th></tr>
    </thead>
    <tbody>
      <?php while ($p = $products->fetch_assoc()):
        $status = $p['stock'] === 0 ? ['cancelled','Out of Stock'] :
                  ($p['stock'] <= $p['min_stock'] ? ['pending','Low Stock'] : ['done','OK']);
      ?>
      <tr>
        <td><?= $p['emoji'] ?> <strong><?= htmlspecialchars($p['name']) ?></strong></td>
        <td><?= htmlspecialchars($p['cat_name']) ?></td>
        <td style="font-weight:600;color:<?= $p['stock']===0 ? 'var(--red)' : ($p['stock']<=$p['min_stock'] ? '#b07a0e' : 'var(--green)') ?>;">
          <?= $p['stock'] ?> units
        </td>
        <td><?= $p['min_stock'] ?> units</td>
        <td><span class="status-badge <?= $status[0] ?>"><?= $status[1] ?></span></td>
        <td>
          <form method="POST" style="display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
            <input type="number" name="stock" value="<?= $p['stock'] ?>" min="0"
              style="width:80px;padding:5px 8px;border-radius:6px;border:1.5px solid var(--border);font-family:var(--font-body);font-size:13px;">
            <input type="number" name="min_stock" value="<?= $p['min_stock'] ?>" min="1"
              style="width:70px;padding:5px 8px;border-radius:6px;border:1.5px solid var(--border);font-family:var(--font-body);font-size:13px;"
              title="Min stock level">
            <button type="submit" class="qa-btn" style="font-size:11px;padding:5px 12px;">Update</button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include 'includes/footer.php'; ?>
