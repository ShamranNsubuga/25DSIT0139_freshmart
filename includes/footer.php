<?php // includes/footer.php ?>
<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div class="footer-brand">
        <div style="font-family:var(--font-head);font-size:22px;color:white;">Fresh<span style="color:var(--accent);">Mart</span></div>
        <p>Uganda's favourite online supermarket. Delivering freshness to your door since 2020.</p>
        <div style="display:flex;gap:10px;margin-top:16px;">
          <div class="social-icon">📘</div>
          <div class="social-icon">📸</div>
          <div class="social-icon">🐦</div>
        </div>
      </div>
      <div class="footer-col">
        <h4>Shop</h4>
        <a href="/freshmart/index.php">All Products</a>
        <a href="/freshmart/index.php?deals=1">Fresh Deals</a>
        <a href="/freshmart/index.php?badge=new">New Arrivals</a>
      </div>
      <div class="footer-col">
        <h4>Account</h4>
        <a href="/freshmart/customer/login.php">Sign In</a>
        <a href="/freshmart/customer/register.php">Register</a>
        <a href="/freshmart/customer/orders.php">My Orders</a>
        <a href="/freshmart/customer/dashboard.php">Dashboard</a>
      </div>
      <div class="footer-col">
        <h4>Help</h4>
        <a href="#">Delivery Info</a>
        <a href="#">Returns</a>
        <a href="#">FAQs</a>
        <a href="#">Contact Us</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> FreshMart Uganda. All rights reserved.</p>
      <p>💳 Mobile Money · Visa · Mastercard · Cash on Delivery</p>
    </div>
  </div>
</footer>
<div class="toast" id="toast"></div>
<script src="/freshmart/assets/js/main.js"></script>
</body>
</html>
