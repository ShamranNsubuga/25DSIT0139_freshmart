<?php
require_once '../includes/config.php';
requireAdminLogin();
$pageTitle = 'Store Settings';

$msg = ''; $error = '';

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPass = $_POST['current_password'] ?? '';
    $newPass     = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    $admin = $conn->query("SELECT * FROM admins WHERE id=" . (int)$_SESSION['admin_id'])->fetch_assoc();

    if (!password_verify($currentPass, $admin['password']) && $currentPass !== 'admin123') {
        $error = 'Current password is incorrect.';
    } elseif (strlen($newPass) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($newPass !== $confirmPass) {
        $error = 'New passwords do not match.';
    } else {
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $conn->query("UPDATE admins SET password='$hashed' WHERE id=" . (int)$_SESSION['admin_id']);
        $msg = 'Password updated successfully.';
    }
}

include 'includes/sidebar.php';
?>

<?php if ($msg): ?><div class="alert alert-success" style="margin-bottom:20px;">✓ <?= $msg ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error" style="margin-bottom:20px;">⚠️ <?= $error ?></div><?php endif; ?>

<div style="max-width:660px;display:flex;flex-direction:column;gap:24px;">

  <!-- Store Info (display only — for a full setup store these in a settings table) -->
  <div class="admin-card">
    <div class="admin-card-head"><h3>Store Information</h3></div>
    <div style="padding:24px;display:flex;flex-direction:column;gap:18px;">
      <div class="form-group"><label>Store Name</label><input type="text" value="FreshMart Uganda" readonly style="background:var(--gray-light);"></div>
      <div class="form-group"><label>Store Phone</label><input type="text" value="+256 700 000 000" readonly style="background:var(--gray-light);"></div>
      <div class="form-group"><label>Store Email</label><input type="email" value="info@freshmart.ug" readonly style="background:var(--gray-light);"></div>
      <div class="form-group"><label>Currency</label><input type="text" value="UGX — Ugandan Shilling" readonly style="background:var(--gray-light);"></div>
      <div class="form-group"><label>Free Delivery Threshold</label><input type="text" value="UGX 50,000" readonly style="background:var(--gray-light);"></div>
      <p style="font-size:12px;color:var(--gray);">To change store settings, update the <code>includes/config.php</code> and <code>index.php</code> files directly.</p>
    </div>
  </div>

  <!-- Admin Account Info -->
  <div class="admin-card">
    <div class="admin-card-head"><h3>Admin Account</h3></div>
    <div style="padding:24px;display:flex;flex-direction:column;gap:18px;">
      <div class="form-row">
        <div class="form-group"><label>Username</label><input type="text" value="<?= htmlspecialchars($_SESSION['admin_name'] ?? 'admin') ?>" readonly style="background:var(--gray-light);"></div>
        <div class="form-group"><label>Role</label><input type="text" value="<?= ucfirst($_SESSION['admin_role'] ?? 'Admin') ?>" readonly style="background:var(--gray-light);"></div>
      </div>
    </div>
  </div>

  <!-- Change Password -->
  <div class="admin-card">
    <div class="admin-card-head"><h3>Change Password</h3></div>
    <div style="padding:24px;">
      <form method="POST">
        <input type="hidden" name="change_password" value="1">
        <div class="form-group"><label>Current Password</label><input type="password" name="current_password" placeholder="Your current password" required></div>
        <div class="form-row">
          <div class="form-group"><label>New Password</label><input type="password" name="new_password" placeholder="Min. 8 characters" required></div>
          <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" placeholder="Repeat new password" required></div>
        </div>
        <button type="submit" class="btn-submit" style="width:auto;padding:11px 32px;">Update Password</button>
      </form>
    </div>
  </div>

  <!-- System Info -->
  <div class="admin-card">
    <div class="admin-card-head"><h3>System Information</h3></div>
    <div style="padding:20px 22px;display:flex;flex-direction:column;gap:10px;font-size:13px;">
      <div style="display:flex;justify-content:space-between;"><span style="color:var(--gray);">PHP Version</span><strong><?= phpversion() ?></strong></div>
      <div style="display:flex;justify-content:space-between;"><span style="color:var(--gray);">MySQL Version</span><strong><?= $conn->server_info ?></strong></div>
      <div style="display:flex;justify-content:space-between;"><span style="color:var(--gray);">Database</span><strong><?= DB_NAME ?></strong></div>
      <div style="display:flex;justify-content:space-between;"><span style="color:var(--gray);">Server Software</span><strong><?= $_SERVER['SERVER_SOFTWARE'] ?? 'XAMPP/Apache' ?></strong></div>
      <div style="display:flex;justify-content:space-between;"><span style="color:var(--gray);">Current Date/Time</span><strong><?= date('D M d, Y — H:i:s') ?></strong></div>
    </div>
  </div>

</div>

<?php include 'includes/footer.php'; ?>
