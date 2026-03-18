<?php
require_once '../includes/config.php';

if (isAdminLoggedIn()) {
    header('Location: /freshmart/admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter your username and password.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? AND is_active = 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        // Accept password_verify OR plain 'admin123' for demo
        if ($admin && (password_verify($password, $admin['password']) || $password === 'admin123')) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];
            header('Location: /freshmart/admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid credentials. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — FreshMart</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/freshmart/assets/css/style.css">
</head>
<body>
<div class="admin-login-page">

  <!-- LEFT PANEL -->
  <div class="admin-login-left">
    <div class="content">
      <div class="big-icon">🏪</div>
      <h2>FreshMart Admin</h2>
      <p>Manage your supermarket — products, orders, inventory, staff, and analytics all in one place.</p>
      <div class="admin-feature-list">
        <div class="admin-feature-item"><span>📊</span> Real-time sales dashboard</div>
        <div class="admin-feature-item"><span>📦</span> Inventory management & alerts</div>
        <div class="admin-feature-item"><span>👥</span> Customer & staff management</div>
        <div class="admin-feature-item"><span>🏷️</span> Promotions & discount control</div>
        <div class="admin-feature-item"><span>📈</span> Sales reports & analytics</div>
        <div class="admin-feature-item"><span>🔔</span> Low stock & order alerts</div>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="admin-login-right">
    <div class="admin-login-form">
      <div style="font-size:36px;margin-bottom:12px;">🔐</div>
      <h1>Admin Login</h1>
      <p>Sign in to manage FreshMart operations</p>

      <?php if ($error): ?>
        <div class="alert alert-error" style="margin-top:20px;">⛔ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" style="margin-top:24px;">
        <div class="form-group">
          <label>Admin Username</label>
          <div class="input-icon">
            <span class="icon">👤</span>
            <input type="text" name="username" placeholder="Enter admin username" value="<?= htmlspecialchars($_POST['username'] ?? 'admin') ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label>Password</label>
          <div class="input-icon">
            <span class="icon">🔒</span>
            <input type="password" name="password" placeholder="Enter admin password" value="admin123" required>
          </div>
          <a href="#" style="font-size:12px;color:var(--green);font-weight:600;float:right;margin-top:6px;">Forgot password?</a>
        </div>
        <div class="form-group" style="margin-top:8px;">
          <label>Admin Role</label>
          <select name="role">
            <option value="superadmin">Super Admin</option>
            <option value="manager">Store Manager</option>
            <option value="inventory">Inventory Manager</option>
            <option value="cashier">Cashier</option>
          </select>
        </div>
        <div class="checkbox-row" style="margin-bottom:22px;">
          <input type="checkbox" id="remember" name="remember" checked>
          <label for="remember">Keep me signed in on this device</label>
        </div>
        <button type="submit" class="btn-submit">Access Admin Dashboard</button>
      </form>

      <div style="margin-top:20px;padding:14px;background:var(--accent-light);border-radius:var(--radius-sm);border:1px solid #f5e0c0;">
        <p style="font-size:12px;color:#7a5200;font-weight:500;">🔑 Demo credentials: <strong>admin / admin123</strong></p>
      </div>
      <div style="text-align:center;margin-top:20px;">
        <a href="/freshmart/index.php" style="font-size:13px;color:var(--gray);">← Back to Store</a>
      </div>
    </div>
  </div>

</div>
</body>
</html>
