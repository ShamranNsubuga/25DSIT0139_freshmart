<?php
require_once '../includes/config.php';
requireAdminLogin();
$pageTitle = 'Staff Management';

$msg = ''; $error = '';

// Delete
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM staff WHERE id=" . (int)$_GET['delete']);
    header('Location: /freshmart/admin/staff.php?msg=deleted'); exit;
}

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = (int)($_POST['id'] ?? 0);
    $name  = sanitize($conn, $_POST['full_name'] ?? '');
    $email = sanitize($conn, $_POST['email'] ?? '');
    $phone = sanitize($conn, $_POST['phone'] ?? '');
    $role  = sanitize($conn, $_POST['role'] ?? '');
    $shift = sanitize($conn, $_POST['shift'] ?? 'morning');

    if (empty($name)) { $error = 'Full name is required.'; }
    else {
        if ($id > 0) {
            $conn->query("UPDATE staff SET full_name='$name',email='$email',phone='$phone',role='$role',shift='$shift' WHERE id=$id");
            $msg = 'Staff member updated.';
        } else {
            $conn->query("INSERT INTO staff (full_name,email,phone,role,shift) VALUES ('$name','$email','$phone','$role','$shift')");
            $msg = 'Staff member added.';
        }
    }
}

$editStaff = null;
if (isset($_GET['edit'])) {
    $editStaff = $conn->query("SELECT * FROM staff WHERE id=" . (int)$_GET['edit'])->fetch_assoc();
}

if (!$msg && isset($_GET['msg'])) $msg = $_GET['msg'] === 'deleted' ? 'Staff member removed.' : '';

$staff = $conn->query("SELECT * FROM staff ORDER BY full_name ASC");
$showForm = isset($_GET['action']) || $editStaff;

include 'includes/sidebar.php';
?>
<style>
.status-badge{padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.status-badge.done{background:var(--green-light);color:var(--green);}
.status-badge.pending{background:var(--accent-light);color:#7a5200;}
</style>

<?php if ($msg): ?><div class="alert alert-success" style="margin-bottom:20px;">✓ <?= $msg ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error" style="margin-bottom:20px;">⚠️ <?= $error ?></div><?php endif; ?>

<?php if ($showForm): ?>
<div class="admin-card" style="margin-bottom:28px;">
  <div class="admin-card-head">
    <h3><?= $editStaff ? 'Edit Staff Member' : 'Add Staff Member' ?></h3>
    <a href="/freshmart/admin/staff.php" class="qa-btn">✕ Cancel</a>
  </div>
  <div style="padding:28px;">
    <form method="POST">
      <?php if ($editStaff): ?><input type="hidden" name="id" value="<?= $editStaff['id'] ?>"><?php endif; ?>
      <div class="form-row">
        <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" value="<?= htmlspecialchars($editStaff['full_name'] ?? '') ?>" required placeholder="e.g. Alice Namutebi"></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($editStaff['email'] ?? '') ?>" placeholder="e.g. alice@freshmart.ug"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($editStaff['phone'] ?? '') ?>" placeholder="+256 700 000 000"></div>
        <div class="form-group"><label>Role</label><input type="text" name="role" value="<?= htmlspecialchars($editStaff['role'] ?? '') ?>" placeholder="e.g. Store Manager, Cashier"></div>
      </div>
      <div class="form-row" style="grid-template-columns:1fr 1fr;">
        <div class="form-group">
          <label>Shift</label>
          <select name="shift">
            <option value="morning"   <?= ($editStaff['shift']??'') === 'morning'   ? 'selected':'' ?>>Morning</option>
            <option value="afternoon" <?= ($editStaff['shift']??'') === 'afternoon' ? 'selected':'' ?>>Afternoon</option>
            <option value="night"     <?= ($editStaff['shift']??'') === 'night'     ? 'selected':'' ?>>Night</option>
            <option value="all_day"   <?= ($editStaff['shift']??'') === 'all_day'   ? 'selected':'' ?>>All Day</option>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:12px;">
        <button type="submit" class="btn-submit" style="width:auto;padding:11px 32px;"><?= $editStaff ? 'Update Staff' : 'Add Staff Member' ?></button>
        <a href="/freshmart/admin/staff.php" class="qa-btn">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<div style="display:flex;justify-content:flex-end;margin-bottom:16px;">
  <a href="?action=add" class="btn-submit" style="width:auto;padding:10px 20px;">+ Add Staff Member</a>
</div>

<div class="admin-card">
  <div class="admin-card-head"><h3>Staff Members (<?= $staff->num_rows ?>)</h3></div>
  <table class="admin-table">
    <thead><tr><th>Name</th><th>Role</th><th>Email</th><th>Phone</th><th>Shift</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php while ($s = $staff->fetch_assoc()): ?>
      <tr>
        <td><strong><?= htmlspecialchars($s['full_name']) ?></strong></td>
        <td><?= htmlspecialchars($s['role']) ?></td>
        <td><?= htmlspecialchars($s['email'] ?: '—') ?></td>
        <td><?= htmlspecialchars($s['phone'] ?: '—') ?></td>
        <td><?= ucwords(str_replace('_',' ',$s['shift'])) ?></td>
        <td><span class="status-badge <?= $s['is_active'] ? 'done' : 'pending' ?>"><?= $s['is_active'] ? 'Active' : 'On Leave' ?></span></td>
        <td style="display:flex;gap:6px;">
          <a href="?edit=<?= $s['id'] ?>" class="qa-btn" style="font-size:11px;padding:4px 10px;">Edit</a>
          <a href="?delete=<?= $s['id'] ?>" class="qa-btn btn-danger" style="font-size:11px;padding:4px 10px;" onclick="return confirm('Remove this staff member?')">Remove</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include 'includes/footer.php'; ?>
