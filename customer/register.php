<?php
require_once '../includes/config.php';
$pageTitle = 'Create Account';

if (isCustomerLoggedIn()) {
    header('Location: /freshmart/customer/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname   = sanitize($conn, $_POST['fname'] ?? '');
    $lname   = sanitize($conn, $_POST['lname'] ?? '');
    $email   = sanitize($conn, $_POST['email'] ?? '');
    $phone   = sanitize($conn, $_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $cpass   = $_POST['cpass'] ?? '';
    $address = sanitize($conn, $_POST['address'] ?? '');
    $city    = sanitize($conn, $_POST['city'] ?? '');
    $gender  = sanitize($conn, $_POST['gender'] ?? '');

    if (empty($fname) || empty($lname) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $cpass) {
        $error = 'Passwords do not match.';
    } elseif (!isset($_POST['terms'])) {
        $error = 'You must accept the Terms of Service to continue.';
    } else {
        // Check email exists
        $check = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'An account with this email already exists. Please sign in.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                INSERT INTO customers (first_name, last_name, email, phone, password, address, city, gender)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('ssssssss', $fname, $lname, $email, $phone, $hashed, $address, $city, $gender);
            if ($stmt->execute()) {
                $_SESSION['flash_success'] = 'Account created! Welcome to FreshMart, ' . $fname . '! Please sign in.';
                header('Location: /freshmart/customer/login.php');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account — FreshMart</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/freshmart/assets/css/style.css">
</head>
<body>
<div class="promo-strip">🚚 FREE delivery on orders over UGX 50,000 | 🎁 Use code <strong>FRESH10</strong> for 10% off your first order</div>
<nav><div class="nav-inner">
  <a class="logo" href="/freshmart/index.php"><div class="logo-icon">🛒</div><span class="logo-text">Fresh<span>Mart</span></span></a>
  <div style="font-size:13px;color:var(--gray);">Create Your Account</div>
  <a class="btn-nav" href="/freshmart/index.php">← Back to Shop</a>
</div></nav>

<div class="auth-page">
  <div class="auth-wrap">
    <div class="auth-banner">
      <div style="font-size:52px;">🎉</div>
      <div>
        <h2>Join FreshMart Today</h2>
        <p>Create a free account and start enjoying fresh groceries delivered to your door.</p>
        <div class="auth-perks">
          <span class="auth-perk">🎁 10% off first order</span>
          <span class="auth-perk">⭐ Earn loyalty points</span>
          <span class="auth-perk">📦 Order tracking</span>
          <span class="auth-perk">💰 Exclusive deals</span>
        </div>
      </div>
    </div>

    <div class="auth-card">
      <div class="auth-tabs">
        <a href="/freshmart/customer/login.php" class="auth-tab">Sign In</a>
        <button class="auth-tab active">Create Account</button>
      </div>
      <div class="auth-body">
        <?php if ($error): ?><div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST">
          <div class="form-row">
            <div class="form-group">
              <label>First Name *</label>
              <input type="text" name="fname" placeholder="e.g. Philip" value="<?= htmlspecialchars($_POST['fname'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label>Last Name *</label>
              <input type="text" name="lname" placeholder="e.g. Mugisha" value="<?= htmlspecialchars($_POST['lname'] ?? '') ?>" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Email Address *</label>
              <input type="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input type="tel" name="phone" placeholder="+256 700 000 000" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Password * <span style="font-weight:400;color:var(--gray)">(min. 8 chars)</span></label>
              <input type="password" name="password" placeholder="Create a strong password" required>
            </div>
            <div class="form-group">
              <label>Confirm Password *</label>
              <input type="password" name="cpass" placeholder="Repeat your password" required>
            </div>
          </div>
          <div class="form-group">
            <label>Delivery Address</label>
            <input type="text" name="address" placeholder="e.g. Kisubi, Entebbe Road, Plot 5" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>City / Town</label>
              <input type="text" name="city" placeholder="e.g. Kampala" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Gender</label>
              <select name="gender">
                <option value="">Select...</option>
                <option value="Male" <?= ($_POST['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= ($_POST['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= ($_POST['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Prefer not to say</option>
              </select>
            </div>
          </div>
          <div class="checkbox-row">
            <input type="checkbox" id="terms" name="terms" <?= isset($_POST['terms']) ? 'checked' : '' ?> required>
            <label for="terms">I agree to the <a href="#" style="color:var(--green);font-weight:600;">Terms of Service</a> and <a href="#" style="color:var(--green);font-weight:600;">Privacy Policy</a></label>
          </div>
          <div class="checkbox-row">
            <input type="checkbox" id="offers" name="offers" checked>
            <label for="offers">Send me deals, promotions and fresh arrivals</label>
          </div>
          <button type="submit" class="btn-submit">Create My Account — It's Free!</button>
        </form>
        <div class="form-footer">Already have an account? <a href="/freshmart/customer/login.php">Sign in here</a></div>
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
