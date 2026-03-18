<?php
require_once '../includes/config.php';
requireAdminLogin();
$pageTitle = 'Products';

$msg = '';
$error = '';

// ─── DELETE ───
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("UPDATE products SET is_active = 0 WHERE id = $id");
    $msg = 'Product removed successfully.';
}

// ─── SAVE (Add / Edit) ───
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = (int)($_POST['id'] ?? 0);
    $name    = sanitize($conn, $_POST['name'] ?? '');
    $cat_id  = (int)($_POST['category_id'] ?? 0);
    $price   = (float)($_POST['price'] ?? 0);
    $old_price = !empty($_POST['old_price']) ? (float)$_POST['old_price'] : 'NULL';
    $weight  = sanitize($conn, $_POST['weight'] ?? '');
    $stock   = (int)($_POST['stock'] ?? 0);
    $min_stock = (int)($_POST['min_stock'] ?? 10);
    $emoji   = sanitize($conn, $_POST['emoji'] ?? '🛍️');
    $badge   = sanitize($conn, $_POST['badge'] ?? 'none');
    $desc    = sanitize($conn, $_POST['description'] ?? '');

    $old_price_sql = $old_price === 'NULL' ? 'NULL' : $old_price;

    if (empty($name) || $price <= 0) {
        $error = 'Product name and price are required.';
    } else {
        if ($id > 0) {
            $conn->query("UPDATE products SET
                name='$name', category_id=$cat_id, price=$price,
                old_price=$old_price_sql, weight='$weight', stock=$stock,
                min_stock=$min_stock, emoji='$emoji', badge='$badge', description='$desc'
                WHERE id=$id");
            $msg = 'Product updated successfully.';
        } else {
            $conn->query("INSERT INTO products (name,category_id,price,old_price,weight,stock,min_stock,emoji,badge,description)
                VALUES ('$name',$cat_id,$price,$old_price_sql,'$weight',$stock,$min_stock,'$emoji','$badge','$desc')");
            $msg = 'Product added successfully.';
        }
    }
}

// ─── EDIT LOAD ───
$editProduct = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editProduct = $conn->query("SELECT * FROM products WHERE id = $editId")->fetch_assoc();
}

// ─── FILTER ───
$where = "WHERE p.is_active = 1";
if (!empty($_GET['cat'])) $where .= " AND p.category_id = " . (int)$_GET['cat'];
if (!empty($_GET['search'])) {
    $s = sanitize($conn, $_GET['search']);
    $where .= " AND p.name LIKE '%$s%'";
}

$products = $conn->query("SELECT p.*, c.name AS cat_name FROM products p LEFT JOIN categories c ON c.id = p.category_id $where ORDER BY p.id DESC");
$categories = $conn->query("SELECT * FROM categories WHERE is_active=1 ORDER BY name");

$showForm = isset($_GET['action']) && $_GET['action'] === 'add' || $editProduct;

include 'includes/sidebar.php';
?>
<style>.status-badge{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}.status-badge.done{background:var(--green-light);color:var(--green);}.status-badge.pending{background:var(--accent-light);color:#7a5200;}.status-badge.cancelled{background:var(--red-light);color:var(--red);}</style>

<?php if ($msg): ?><div class="alert alert-success" style="margin-bottom:20px;">✓ <?= $msg ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error" style="margin-bottom:20px;">⚠️ <?= $error ?></div><?php endif; ?>

<!-- ADD / EDIT FORM -->
<?php if ($showForm): ?>
<div class="admin-card" style="margin-bottom:28px;">
  <div class="admin-card-head">
    <h3><?= $editProduct ? 'Edit Product' : 'Add New Product' ?></h3>
    <a href="/freshmart/admin/products.php" class="qa-btn">✕ Cancel</a>
  </div>
  <div style="padding:28px;">
    <form method="POST">
      <?php if ($editProduct): ?><input type="hidden" name="id" value="<?= $editProduct['id'] ?>"><?php endif; ?>
      <div class="form-row">
        <div class="form-group">
          <label>Product Name *</label>
          <input type="text" name="name" value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" required placeholder="e.g. Fresh Whole Milk 1L">
        </div>
        <div class="form-group">
          <label>Category *</label>
          <select name="category_id" required>
            <option value="">Select category...</option>
            <?php $categories->data_seek(0); while ($c = $categories->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>" <?= ($editProduct['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= $c['icon'] ?> <?= htmlspecialchars($c['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
      <div class="form-row" style="grid-template-columns:1fr 1fr 1fr 1fr;">
        <div class="form-group">
          <label>Price (UGX) *</label>
          <input type="number" name="price" value="<?= $editProduct['price'] ?? '' ?>" required min="1" placeholder="e.g. 4500">
        </div>
        <div class="form-group">
          <label>Old Price (UGX)</label>
          <input type="number" name="old_price" value="<?= $editProduct['old_price'] ?? '' ?>" min="1" placeholder="Leave blank if no sale">
        </div>
        <div class="form-group">
          <label>Stock Quantity</label>
          <input type="number" name="stock" value="<?= $editProduct['stock'] ?? 0 ?>" min="0">
        </div>
        <div class="form-group">
          <label>Min. Stock Level</label>
          <input type="number" name="min_stock" value="<?= $editProduct['min_stock'] ?? 10 ?>" min="1">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Weight / Volume</label>
          <input type="text" name="weight" value="<?= htmlspecialchars($editProduct['weight'] ?? '') ?>" placeholder="e.g. 1 Litre, 500g, 30 pcs">
        </div>
        <div class="form-group">
          <label>Emoji Icon</label>
          <input type="text" name="emoji" value="<?= $editProduct['emoji'] ?? '🛍️' ?>" placeholder="e.g. 🥛" maxlength="4">
        </div>
        <div class="form-group">
          <label>Badge</label>
          <select name="badge">
            <option value="none" <?= ($editProduct['badge'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
            <option value="new" <?= ($editProduct['badge'] ?? '') === 'new' ? 'selected' : '' ?>>New</option>
            <option value="sale" <?= ($editProduct['badge'] ?? '') === 'sale' ? 'selected' : '' ?>>Sale</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="3" placeholder="Brief product description..."><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
      </div>
      <div style="display:flex;gap:12px;">
        <button type="submit" class="btn-submit" style="width:auto;padding:11px 32px;"><?= $editProduct ? 'Update Product' : 'Add Product' ?></button>
        <a href="/freshmart/admin/products.php" class="qa-btn">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- FILTER + TABLE -->
<div class="filter-row">
  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;width:100%;align-items:center;">
    <input type="text" name="search" placeholder="🔍 Search products..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <select name="cat">
      <option value="">All Categories</option>
      <?php $categories->data_seek(0); while ($c = $categories->fetch_assoc()): ?>
      <option value="<?= $c['id'] ?>" <?= ($_GET['cat'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
      <?php endwhile; ?>
    </select>
    <button type="submit" class="qa-btn">Filter</button>
    <a href="/freshmart/admin/products.php" class="qa-btn">Clear</a>
    <a href="/freshmart/admin/products.php?action=add" class="btn-submit" style="width:auto;padding:10px 20px;margin-left:auto;">+ Add New Product</a>
  </form>
</div>

<div class="admin-card">
  <div class="admin-card-head"><h3>Products (<?= $products->num_rows ?>)</h3></div>
  <table class="admin-table">
    <thead>
      <tr><th>Product</th><th>SKU</th><th>Category</th><th>Price</th><th>Old Price</th><th>Stock</th><th>Badge</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php while ($p = $products->fetch_assoc()): ?>
      <tr>
        <td><?= $p['emoji'] ?> <strong><?= htmlspecialchars($p['name']) ?></strong></td>
        <td style="color:var(--gray);font-size:12px;">SKU-<?= str_pad($p['id'],4,'0',STR_PAD_LEFT) ?></td>
        <td><?= htmlspecialchars($p['cat_name']) ?></td>
        <td><strong>UGX <?= number_format($p['price']) ?></strong></td>
        <td><?= $p['old_price'] ? 'UGX ' . number_format($p['old_price']) : '—' ?></td>
        <td style="color:<?= $p['stock'] <= $p['min_stock'] ? 'var(--red)' : 'var(--green)' ?>;font-weight:600;"><?= $p['stock'] ?></td>
        <td><?= ucfirst($p['badge']) ?></td>
        <td><span class="status-badge <?= $p['stock'] > 0 ? 'done' : 'cancelled' ?>"><?= $p['stock'] > 0 ? 'Active' : 'Out of Stock' ?></span></td>
        <td style="display:flex;gap:6px;">
          <a href="?edit=<?= $p['id'] ?>" class="qa-btn" style="font-size:11px;padding:4px 10px;">Edit</a>
          <a href="?delete=<?= $p['id'] ?>" class="qa-btn btn-danger" style="font-size:11px;padding:4px 10px;" onclick="return confirm('Delete this product?')">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include 'includes/footer.php'; ?>
