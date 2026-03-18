<?php
require_once '../includes/config.php';
$pageTitle = 'Checkout';

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: /freshmart/customer/cart.php');
    exit;
}

// Redirect to login if not logged in
if (!isCustomerLoggedIn()) {
    header('Location: /freshmart/customer/login.php?redirect=/freshmart/customer/checkout.php');
    exit;
}

$cid = $_SESSION['customer_id'];
$customer = $conn->query("SELECT * FROM customers WHERE id = $cid")->fetch_assoc();

// ─── PLACE ORDER ───
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_address = sanitize($conn, $_POST['delivery_address'] ?? '');
    $payment_method   = sanitize($conn, $_POST['payment_method'] ?? 'cash');
    $notes            = sanitize($conn, $_POST['notes'] ?? '');
    $promo_code       = strtoupper(sanitize($conn, $_POST['promo_code'] ?? ''));

    if (empty($delivery_address)) {
        $error = 'Please enter a delivery address.';
    } else {
        // Calculate totals
        $cart = $_SESSION['cart'];
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['qty'];
        }
        $delivery_fee = $subtotal >= 50000 ? 0 : 5000;
        $discount = 0;

        // Apply promo
        if ($promo_code) {
            $promo = $conn->query("
                SELECT * FROM promotions
                WHERE code = '$promo_code'
                  AND is_active = 1
                  AND (valid_until IS NULL OR valid_until >= CURDATE())
                  AND (max_uses = 0 OR used_count < max_uses)
                  AND min_order <= $subtotal
            ")->fetch_assoc();

            if ($promo) {
                if ($promo['discount_type'] === 'percent') {
                    $discount = round($subtotal * ($promo['discount_value'] / 100));
                } else {
                    $discount = $promo['discount_value'];
                }
            }
        }

        $total = $subtotal + $delivery_fee - $discount;

        // Generate order number
        $order_number = 'ORD-' . strtoupper(substr(md5(uniqid()), 0, 6));

        // Insert order
        $stmt = $conn->prepare("
            INSERT INTO orders (order_number, customer_id, total_amount, payment_method, payment_status, status, delivery_address, notes)
            VALUES (?, ?, ?, ?, 'pending', 'pending', ?, ?)
        ");
        $stmt->bind_param('sidsss', $order_number, $cid, $total, $payment_method, $delivery_address, $notes);

        if ($stmt->execute()) {
            $order_id = $conn->insert_id;

            // Insert order items + reduce stock
            foreach ($cart as $pid => $item) {
                $qty        = (int)$item['qty'];
                $unit_price = (float)$item['price'];
                $conn->query("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES ($order_id, $pid, $qty, $unit_price)");
                $conn->query("UPDATE products SET stock = GREATEST(0, stock - $qty) WHERE id = $pid");
            }

            // Update promo usage
            if (!empty($promo_code) && isset($promo)) {
                $conn->query("UPDATE promotions SET used_count = used_count + 1 WHERE code = '$promo_code'");
            }

            // Award loyalty points (1 point per 100 UGX spent)
            $points = floor($total / 100);
            $conn->query("UPDATE customers SET loyalty_points = loyalty_points + $points WHERE id = $cid");

            // Clear cart
            $_SESSION['cart'] = [];
            $_SESSION['last_order'] = $order_number;

            header('Location: /freshmart/customer/order_success.php?order=' . $order_number);
            exit;
        } else {
            $error = 'Order could not be placed. Please try again.';
        }
    }
}

// Cart totals for display
$cart = $_SESSION['cart'];
$subtotal = 0;
foreach ($cart as $item) $subtotal += $item['price'] * $item['qty'];
$delivery_fee = $subtotal >= 50000 ? 0 : 5000;
$total = $subtotal + $delivery_fee;

include '../includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <div class="breadcrumb">
      <a href="/freshmart/index.php">Home</a> /
      <a href="/freshmart/customer/cart.php">Cart</a> / Checkout
    </div>
    <h1>🔐 Secure Checkout</h1>
    <p>Complete your order — fast, safe, and easy</p>
  </div>
</div>

<div class="cart-wrap">

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:20px;">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
  <div class="cart-layout">

    <!-- LEFT: Delivery + Payment -->
    <div style="display:flex;flex-direction:column;gap:24px;">

      <!-- Delivery Address -->
      <div class="table-card">
        <div class="table-card-head">📍 Delivery Details</div>
        <div style="padding:24px;display:flex;flex-direction:column;gap:18px;">
          <div class="form-group">
            <label>Full Delivery Address *</label>
            <input type="text" name="delivery_address"
              value="<?= htmlspecialchars($_POST['delivery_address'] ?? $customer['address'] ?? '') ?>"
              placeholder="e.g. Plot 12, Ntinda Road, Kampala" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Recipient Name</label>
              <input type="text" value="<?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>" readonly style="background:var(--gray-light);">
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input type="text" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>" readonly style="background:var(--gray-light);">
            </div>
          </div>
          <div class="form-group">
            <label>Delivery Notes <span style="font-weight:400;color:var(--gray)">(optional)</span></label>
            <textarea name="notes" rows="3" placeholder="e.g. Call on arrival, Leave at gate, etc."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
          </div>
          <div style="background:var(--green-light);border:1px solid #b7e0c8;border-radius:var(--radius-sm);padding:12px 16px;font-size:13px;color:var(--green);display:flex;align-items:center;gap:10px;">
            🚚 <span><?= $delivery_fee === 0 ? '<strong>Free delivery</strong> on this order!' : 'Estimated delivery: <strong>30–60 minutes</strong>' ?></span>
          </div>
        </div>
      </div>

      <!-- Payment Method -->
      <div class="table-card">
        <div class="table-card-head">💳 Payment Method</div>
        <div style="padding:24px;display:flex;flex-direction:column;gap:12px;">

          <?php
          $methods = [
            'mobile_money' => ['icon'=>'📱','label'=>'Mobile Money','desc'=>'MTN MoMo or Airtel Money'],
            'card'         => ['icon'=>'💳','label'=>'Debit / Credit Card','desc'=>'Visa, Mastercard'],
            'cash'         => ['icon'=>'💵','label'=>'Cash on Delivery','desc'=>'Pay when your order arrives'],
          ];
          foreach ($methods as $value => $m):
            $checked = ($_POST['payment_method'] ?? 'cash') === $value;
          ?>
          <label style="display:flex;align-items:center;gap:16px;padding:16px;border:1.5px solid <?= $checked ? 'var(--green)' : 'var(--border)' ?>;border-radius:var(--radius-sm);cursor:pointer;transition:var(--transition);"
                 onclick="this.style.borderColor='var(--green)';document.querySelectorAll('.pay-opt').forEach(e=>e.style.borderColor='var(--border)');this.style.borderColor='var(--green)';"
                 class="pay-opt">
            <input type="radio" name="payment_method" value="<?= $value ?>" <?= $checked ? 'checked' : '' ?> style="accent-color:var(--green);width:18px;height:18px;">
            <span style="font-size:24px;"><?= $m['icon'] ?></span>
            <div>
              <div style="font-weight:600;font-size:14px;"><?= $m['label'] ?></div>
              <div style="font-size:12px;color:var(--gray);"><?= $m['desc'] ?></div>
            </div>
          </label>
          <?php endforeach; ?>

        </div>
      </div>

      <!-- Order Items Summary -->
      <div class="table-card">
        <div class="table-card-head">🛒 Your Items (<?= count($cart) ?>)</div>
        <?php foreach ($cart as $pid => $item):
          $prod = $conn->query("SELECT emoji FROM products WHERE id = $pid")->fetch_assoc();
        ?>
        <div style="display:flex;align-items:center;gap:14px;padding:14px 22px;border-bottom:1px solid var(--border);">
          <div style="width:44px;height:44px;background:var(--gray-light);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:24px;"><?= $prod['emoji'] ?? '🛍️' ?></div>
          <div style="flex:1;">
            <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($item['name']) ?></div>
            <div style="font-size:12px;color:var(--gray);">Qty: <?= $item['qty'] ?> × UGX <?= number_format($item['price']) ?></div>
          </div>
          <div style="font-weight:700;color:var(--green);">UGX <?= number_format($item['price'] * $item['qty']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>

    <!-- RIGHT: Order Summary -->
    <div>
      <div class="order-summary" style="position:sticky;top:90px;">
        <h3>Order Summary</h3>

        <div class="summary-row"><span>Subtotal</span><span>UGX <?= number_format($subtotal) ?></span></div>
        <div class="summary-row">
          <span>Delivery Fee</span>
          <span><?= $delivery_fee === 0 ? '<span style="color:var(--green);font-weight:600;">FREE</span>' : 'UGX ' . number_format($delivery_fee) ?></span>
        </div>

        <!-- Promo Code -->
        <div style="margin:16px 0;padding:14px;background:var(--bg);border-radius:var(--radius-sm);border:1px solid var(--border);">
          <label style="font-size:12px;font-weight:600;display:block;margin-bottom:8px;">🏷️ Promo Code</label>
          <div style="display:flex;gap:8px;">
            <input type="text" name="promo_code" placeholder="e.g. FRESH10"
              value="<?= htmlspecialchars($_POST['promo_code'] ?? '') ?>"
              style="flex:1;padding:9px 12px;border-radius:var(--radius-sm);border:1.5px solid var(--border);font-family:var(--font-body);font-size:13px;text-transform:uppercase;">
            <button type="submit" name="check_promo" class="qa-btn" style="white-space:nowrap;font-size:12px;">Apply</button>
          </div>
          <?php if (!empty($_POST['promo_code'])):
            $pc = strtoupper(sanitize($conn, $_POST['promo_code']));
            $checkPromo = $conn->query("SELECT * FROM promotions WHERE code='$pc' AND is_active=1 AND (valid_until IS NULL OR valid_until>=CURDATE()) AND min_order<=$subtotal")->fetch_assoc();
            if ($checkPromo):
              $d = $checkPromo['discount_type']==='percent' ? $checkPromo['discount_value'].'% off' : 'UGX '.number_format($checkPromo['discount_value']).' off';
            ?>
            <div style="color:var(--green);font-size:12px;font-weight:600;margin-top:6px;">✓ Code applied — <?= $d ?></div>
            <?php else: ?>
            <div style="color:var(--red);font-size:12px;margin-top:6px;">✗ Invalid or expired promo code</div>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <div class="summary-row total">
          <span>Total</span>
          <span>UGX <?= number_format($total) ?></span>
        </div>

        <!-- Points earned -->
        <div style="background:var(--accent-light);border:1px solid #f5e0c0;border-radius:var(--radius-sm);padding:10px 14px;margin-bottom:16px;font-size:12px;color:#7a5200;">
          ⭐ You'll earn <strong><?= floor($total / 100) ?> loyalty points</strong> on this order!
        </div>

        <button type="submit" class="btn-submit" style="font-size:16px;padding:15px;">
          Place Order — UGX <?= number_format($total) ?>
        </button>

        <div style="text-align:center;margin-top:14px;">
          <p style="font-size:12px;color:var(--gray);">🔒 Secured checkout &nbsp;|&nbsp; ✅ Order confirmation sent</p>
        </div>

        <div style="margin-top:16px;border-top:1px solid var(--border);padding-top:14px;">
          <a href="/freshmart/customer/cart.php" style="font-size:13px;color:var(--green);font-weight:600;display:flex;align-items:center;gap:6px;">← Edit Cart</a>
        </div>
      </div>
    </div>

  </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
