<?php
require_once '../includes/config.php';
$pageTitle = 'Sign In';

if (isCustomerLoggedIn()) {
    header('Location: /freshmart/customer/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ? AND is_active = 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();

        if ($customer && password_verify($password, $customer['password'])) {
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['first_name'];
            $_SESSION['customer_email'] = $customer['email'];
            $redirect = $_GET['redirect'] ?? '/freshmart/customer/dashboard.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Incorrect email or password. Please try again.';
        }
    }
}

// Flash message from register page
if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — FreshMart</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/freshmart/assets/css/style.css">
</head>
<body>
<div class="promo-strip">🚚 FREE delivery on orders over UGX 50,000 | 🎁 Use code <strong>FRESH10</strong> for 10% off your first order</div>
<nav><div class="nav-inner">
  <a class="logo" href="/freshmart/index.php"><div class="logo-icon">🛒</div><span class="logo-text">Fresh<span>Mart</span></span></a>
  <div style="font-size:13px;color:var(--gray);">Secure Login</div>
  <a class="btn-nav" href="/freshmart/index.php">← Back to Shop</a>
</div></nav>

<div class="auth-page">
  <div class="auth-wrap">

    <!-- Banner -->
    <div class="auth-banner">
      <div style="font-size:52px;">🛍️</div>
      <div>
        <h2>Welcome to FreshMart</h2>
        <p>Sign in to track orders, earn loyalty points, and enjoy personalised deals.</p>
        <div class="auth-perks">
          <span class="auth-perk">✅ Order Tracking</span>
          <span class="auth-perk">✅ Loyalty Points</span>
          <span class="auth-perk">✅ Saved Addresses</span>
          <span class="auth-perk">✅ Exclusive Deals</span>
        </div>
      </div>
    </div>

    <div class="auth-card">
      <div class="auth-tabs">
        <button class="auth-tab active">Sign In</button>
        <a href="/freshmart/customer/register.php" class="auth-tab">Create Account</a>
      </div>
      <div class="auth-body">
        <?php if ($error): ?><div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div><?php endif; ?>

        <form method="POST">
          <div class="form-row">
            <div class="form-group">
              <label>Email Address</label>
              <div class="input-icon">
                <span class="icon">✉️</span>
                <input type="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label>Password</label>
              <div class="input-icon">
                <span class="icon">🔒</span>
                <input type="password" name="password" placeholder="Enter your password" required>
              </div>
            </div>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <div class="checkbox-row" style="margin:0">
              <input type="checkbox" id="remember" name="remember">
              <label for="remember">Remember me</label>
            </div>
            <a href="#" style="font-size:12px;color:var(--green);font-weight:600;">Forgot Password?</a>
          </div>
          <button type="submit" class="btn-submit">Sign In to FreshMart</button>
        </form>

        <div class="form-divider"><span>or use demo account</span></div>
        <div class="alert alert-warning" style="font-size:12px;">
          🔑 Demo: <strong>sarah@email.com</strong> / <strong>password</strong>
        </div>
        <div class="form-footer">Don't have an account? <a href="/freshmart/customer/register.php">Create one free</a></div>
      </div>
    </div>

    <div class="trust-badges">
      <div class="trust-badge">🔒<br>SSL Secured</div>
      <div class="trust-badge">🛡️<br>Data Protected</div>
      <div class="trust-badge">✅<br>Verified Business</div>
      <div class="trust-badge">📞<br>24/7 Support</div>
    </div>
  </div>
</div>
<div class="toast" id="toast"></div>
<script src="/freshmart/assets/js/main.js"></script>
</body>
</html>
