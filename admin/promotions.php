<?php
require_once '../includes/config.php';
requireAdminLogin();
$pageTitle = 'Promotions & Discounts';

$msg = ''; $error = '';

// Delete
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM promotions WHERE id=" . (int)$_GET['delete']);
    header('Location: /freshmart/admin/promotions.php?msg=deleted'); exit;
}

// Toggle active
if (isset($_GET['toggle'])) {
    $conn->query("UPDATE promotions SET is_active = NOT is_active WHERE id=" . (int)$_GET['toggle']);
    header('Location: /freshmart/admin/promotions.php'); exit;
}

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $code     = strtoupper(sanitize($conn, $_POST['code'] ?? ''));
    $desc     = sanitize($conn, $_POST['description'] ?? '');
    $dtype    = sanitize($conn, $_POST['discount_type'] ?? 'percent');
    $dval     = (float)($_POST['discount_value'] ?? 0);
    $min_ord  = (float)($_POST['min_order'] ?? 0);
    $max_uses = (int)($_POST['max_uses'] ?? 0);
    $vfrom    = sanitize($conn, $_POST['valid_from'] ?? '');
    $vuntil   = sanitize($conn, $_POST['valid_until'] ?? '');

    if (empty($code) || $dval <= 0) { $error = 'Promo code and discount value are required.'; }
    else {
        if ($id > 0) {
            $conn->query("UPDATE promotions SET code='$code',description='$desc',discount_type='$dtype',
                discount_value=$dval,min_order=$min_ord,max_uses=$max_uses,
                valid_from='$vfrom',valid_until='$vuntil' WHERE id=$id");
            $msg = 'Promotion updated.';
        } else {
            $conn->query("INSERT INTO promotions (code,description,discount_type,discount_value,min_order,max_uses,valid_from,valid_until)
                VALUES ('$code','$desc','$dtype',$dval,$min_ord,$max_uses,'$vfrom','$vuntil')");
            $msg = 'Promotion created.';
        }
    }
}

$editPromo = null;
if (isset($_GET['edit'])) $editPromo = $conn->query("SELECT * FROM promotions WHERE id=" . (int)$_GET['edit'])->fetch_assoc();
if (!$msg && isset($_GET['msg']) && $_GET['msg'] === 'deleted') $msg = 'Promotion removed.';

$promos = $conn->query("SELECT * FROM promotions ORDER BY id DESC");
$showForm = isset($_GET['action']) || $editPromo;

include 'includes/sidebar.php';
?>
<style>
.status-badge{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.status-badge.done{background:var(--green-light);color:var(--green);}
.status-badge.cancelled{background:var(--red-light);color:var(--red);}
</style>

<?php if ($msg): ?><div class="alert alert-success" style="margin-bottom:20px;">✓ <?= $msg ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error" style="margin-bottom:20px;">⚠️ <?= $error ?></div><?php endif; ?>

<?php if ($showForm): ?>
<div class="admin-card" style="margin-bottom:28px;">
  <div class="admin-card-head">
    <h3><?= $editPromo ? 'Edit Promotion' : 'Create New Promotion' ?></h3>
    <a href="/freshmart/admin/promotions.php" class="qa-btn">✕ Cancel</a>
  </div>
  <div style="padding:28px;">
    <form method="POST">
      <?php if ($editPromo): ?><input type="hidden" name="id" value="<?= $editPromo['id'] ?>"><?php endif; ?>
      <div class="form-row">
        <div class="form-group">
          <label>Promo Code * <span style="font-weight:400;color:var(--gray)">(auto-uppercased)</span></label>
          <input type="text" name="code" value="<?= htmlspecialchars($editPromo['code'] ?? '') ?>" required placeholder="e.g. SAVE20" style="text-transform:uppercase;">
        </div>
        <div class="form-group"><label>Description</label><input type="text" name="description" value="<?= htmlspecialchars($editPromo['description'] ?? '') ?>" placeholder="e.g. New customer discount"></div>
      </div>
      <div class="form-row" style="grid-template-columns:1fr 1fr 1fr 1fr;">
        <div class="form-group">
          <label>Discount Type</label>
          <select name="discount_type">
            <option value="percent" <?= ($editPromo['discount_type']??'') === 'percent' ? 'selected':'' ?>>Percentage (%)</option>
            <option value="fixed"   <?= ($editPromo['discount_type']??'') === 'fixed'   ? 'selected':'' ?>>Fixed Amount (UGX)</option>
          </select>
        </div>
        <div class="form-group"><label>Discount Value *</label><input type="number" name="discount_value" value="<?= $editPromo['discount_value'] ?? '' ?>" required min="0.01" step="0.01" placeholder="e.g. 10 or 5000"></div>
        <div class="form-group"><label>Min. Order (UGX)</label><input type="number" name="min_order" value="<?= $editPromo['min_order'] ?? 0 ?>" min="0" placeholder="0 = no minimum"></div>
        <div class="form-group"><label>Max Uses</label><input type="number" name="max_uses" value="<?= $editPromo['max_uses'] ?? 0 ?>" min="0" placeholder="0 = unlimited"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Valid From</label><input type="date" name="valid_from" value="<?= $editPromo['valid_from'] ?? '' ?>"></div>
        <div class="form-group"><label>Valid Until</label><input type="date" name="valid_until" value="<?= $editPromo['valid_until'] ?? '' ?>"></div>
      </div>
      <div style="display:flex;gap:12px;">
        <button type="submit" class="btn-submit" style="width:auto;padding:11px 32px;"><?= $editPromo ? 'Update Promotion' : 'Create Promotion' ?></button>
        <a href="/freshmart/admin/promotions.php" class="qa-btn">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
  <a href="?action=add" class="btn-submit" style="width:auto;padding:10px 20px;">+ Create Promotion</a>
</div>

<div class="admin-card">
  <table class="admin-table">
    <thead><tr><th>Code</th><th>Description</th><th>Discount</th><th>Min Order</th><th>Max Uses</th><th>Used</th><th>Valid Until</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php while ($p = $promos->fetch_assoc()):
        $expired = $p['valid_until'] && $p['valid_until'] < date('Y-m-d');
        $active  = $p['is_active'] && !$expired;
      ?>
      <tr>
        <td><strong style="font-family:monospace;font-size:14px;"><?= htmlspecialchars($p['code']) ?></strong></td>
        <td><?= htmlspecialchars($p['description']) ?></td>
        <td><?= $p['discount_type']==='percent' ? $p['discount_value'].'%' : 'UGX '.number_format($p['discount_value']) ?></td>
        <td><?= $p['min_order'] > 0 ? 'UGX '.number_format($p['min_order']) : 'None' ?></td>
        <td><?= $p['max_uses'] > 0 ? $p['max_uses'] : '∞' ?></td>
        <td><?= $p['used_count'] ?></td>
        <td><?= $p['valid_until'] ? date('M d, Y', strtotime($p['valid_until'])) : '—' ?></td>
        <td>
          <span class="status-badge <?= $active ? 'done' : 'cancelled' ?>">
            <?= $expired ? 'Expired' : ($p['is_active'] ? 'Active' : 'Inactive') ?>
          </span>
        </td>
        <td style="display:flex;gap:6px;">
          <a href="?edit=<?= $p['id'] ?>" class="qa-btn" style="font-size:11px;padding:4px 8px;">Edit</a>
          <a href="?toggle=<?= $p['id'] ?>" class="qa-btn" style="font-size:11px;padding:4px 8px;"><?= $p['is_active'] ? 'Deactivate' : 'Activate' ?></a>
          <a href="?delete=<?= $p['id'] ?>" class="qa-btn btn-danger" style="font-size:11px;padding:4px 8px;" onclick="return confirm('Delete this promo?')">Del</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include 'includes/footer.php'; ?>
