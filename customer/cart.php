<?php
require_once '../includes/config.php';
$pageTitle = 'My Cart';

// ─── ADD TO CART (AJAX) ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    header('Content-Type: application/json');
    $pid = (int)$_POST['product_id'];
    $qty = max(1, (int)($_POST['qty'] ?? 1));

    $prod = $conn->query("SELECT id, name, price, stock FROM products WHERE id = $pid AND is_active = 1")->fetch_assoc();
    if (!$prod) { echo json_encode(['success' => false, 'message' => 'Product not found']); exit; }
    if ($prod['stock'] < 1) { echo json_encode(['success' => false, 'message' => 'Out of stock']); exit; }

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$pid] = ['id' => $pid, 'name' => $prod['name'], 'price' => $prod['price'], 'qty' => $qty];
    }
    $cartCount = array_sum(array_column($_SESSION['cart'], 'qty'));
    echo json_encode(['success' => true, 'cart_count' => $cartCount, 'product_name' => $prod['name']]);
    exit;
}

// ─── UPDATE QTY ───
if (isset($_GET['action'])) {
    $pid = (int)($_GET['pid'] ?? 0);
    if ($_GET['action'] === 'remove' && isset($_SESSION['cart'][$pid])) {
        unset($_SESSION['cart'][$pid]);
    } elseif ($_GET['action'] === 'inc' && isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid]['qty']++;
    } elseif ($_GET['action'] === 'dec' && isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid]['qty']--;
        if ($_SESSION['cart'][$pid]['qty'] < 1) unset($_SESSION['cart'][$pid]);
    }
    header('Location: /freshmart/customer/cart.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cart as $item) $subtotal += $item['price'] * $item['qty'];
$delivery = $subtotal >= 50000 ? 0 : 5000;
$total = $subtotal + $delivery;

include '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <div class="breadcrumb"><a href="/freshmart/index.php">Home</a> / Cart</div>
    <h1>🛒 My Cart</h1>
    <p><?= count($cart) ?> item<?= count($cart) !== 1 ? 's' : '' ?> in your cart</p>
  </div>
</div>

<div class="cart-wrap">
  <?php if (empty($cart)): ?>
    <div style="text-align:center;padding:64px 24px;">
      <div style="font-size:64px;margin-bottom:16px;">🛒</div>
      <h2 style="font-family:var(--font-head);margin-bottom:8px;">Your cart is empty</h2>
      <p style="color:var(--gray);margin-bottom:24px;">Looks like you haven't added anything yet.</p>
      <a href="/freshmart/index.php" class="btn-submit" style="display:inline-block;width:auto;padding:13px 32px;">Start Shopping</a>
    </div>
  <?php else: ?>
  <div class="cart-layout">
    <!-- Cart Items -->
    <div>
      <div class="table-card">
        <div class="table-card-head">Cart Items</div>
        <?php foreach ($cart as $pid => $item): ?>
        <?php
          $prod = $conn->query("SELECT emoji FROM products WHERE id = $pid")->fetch_assoc();
          $emoji = $prod['emoji'] ?? '🛍️';
        ?>
        <div class="cart-item">
          <div class="cart-emoji"><?= $emoji ?></div>
          <div style="flex:1">
            <div style="font-weight:600;font-size:14px;margin-bottom:4px;"><?= htmlspecialchars($item['name']) ?></div>
            <div style="font-size:13px;color:var(--gray);">UGX <?= number_format($item['price']) ?> each</div>
          </div>
          <div class="qty-ctrl">
            <a href="?action=dec&pid=<?= $pid ?>" class="qty-btn">−</a>
            <span style="font-weight:600;min-width:20px;text-align:center;"><?= $item['qty'] ?></span>
            <a href="?action=inc&pid=<?= $pid ?>" class="qty-btn">+</a>
          </div>
          <div style="min-width:120px;text-align:right;">
            <div style="font-weight:700;color:var(--green);font-size:15px;">UGX <?= number_format($item['price'] * $item['qty']) ?></div>
            <a href="?action=remove&pid=<?= $pid ?>" style="font-size:12px;color:var(--red);">Remove</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <a href="/freshmart/index.php" style="color:var(--green);font-size:14px;font-weight:600;">← Continue Shopping</a>
    </div>

    <!-- Order Summary -->
    <div>
      <div class="order-summary">
        <h3>Order Summary</h3>
        <div class="summary-row"><span>Subtotal</span><span>UGX <?= number_format($subtotal) ?></span></div>
        <div class="summary-row">
          <span>Delivery</span>
          <span><?= $delivery === 0 ? '<span style="color:var(--green);font-weight:600;">FREE</span>' : 'UGX ' . number_format($delivery) ?></span>
        </div>
        <?php if ($delivery > 0): ?>
          <div style="font-size:12px;color:var(--gray);margin-bottom:10px;">Add UGX <?= number_format(50000 - $subtotal) ?> more for free delivery</div>
        <?php endif; ?>
        <div class="summary-row total"><span>Total</span><span>UGX <?= number_format($total) ?></span></div>

        <div class="form-group" style="margin-top:16px;">
          <label>Promo Code</label>
          <div style="display:flex;gap:8px;">
            <input type="text" placeholder="e.g. FRESH10" style="flex:1">
            <button class="qa-btn" style="white-space:nowrap;">Apply</button>
          </div>
        </div>

        <?php if (isCustomerLoggedIn()): ?>
          <a href="/freshmart/customer/checkout.php" class="btn-submit" style="display:block;text-align:center;margin-top:8px;">Proceed to Checkout →</a>
        <?php else: ?>
          <a href="/freshmart/customer/login.php?redirect=/freshmart/customer/checkout.php" class="btn-submit" style="display:block;text-align:center;margin-top:8px;">Sign In to Checkout →</a>
        <?php endif; ?>

        <div style="text-align:center;margin-top:12px;">
          <p style="font-size:12px;color:var(--gray);">🔒 Secure checkout · 💳 Multiple payment methods</p>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
