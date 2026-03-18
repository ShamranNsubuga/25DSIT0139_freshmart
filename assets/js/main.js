// assets/js/main.js

// ─── TOAST ───
function showToast(msg) {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

// ─── COUNTDOWN ───
function startCountdown(endTime) {
  function tick() {
    const now = Math.floor(Date.now() / 1000);
    let diff = endTime - now;
    if (diff < 0) diff = 0;
    const h = Math.floor(diff / 3600);
    const m = Math.floor((diff % 3600) / 60);
    const s = diff % 60;
    const pad = n => String(n).padStart(2, '0');
    const hEl = document.getElementById('cd-h');
    const mEl = document.getElementById('cd-m');
    const sEl = document.getElementById('cd-s');
    if (hEl) hEl.textContent = pad(h);
    if (mEl) mEl.textContent = pad(m);
    if (sEl) sEl.textContent = pad(s);
  }
  tick();
  setInterval(tick, 1000);
}

// Start from a fixed point 12 hours from midnight
const midnight = new Date(); midnight.setHours(23,59,59,0);
startCountdown(Math.floor(midnight.getTime() / 1000));

// ─── CART: ADD ITEM via AJAX ───
function addToCart(productId) {
  fetch('/freshmart/customer/cart_add.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'product_id=' + productId + '&qty=1'
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const badge = document.querySelector('.cart-count');
      if (badge) badge.textContent = data.cart_count;
      showToast('✓ ' + data.product_name + ' added to cart!');
    }
  })
  .catch(() => showToast('⚠️ Could not add to cart. Please try again.'));
}

// ─── CATEGORY FILTER ───
document.querySelectorAll('.cat-link').forEach(link => {
  link.addEventListener('click', () => {
    document.querySelectorAll('.cat-link').forEach(l => l.classList.remove('active'));
    link.classList.add('active');
  });
});

// ─── AUTO-DISMISS FLASH MESSAGES ───
setTimeout(() => {
  document.querySelectorAll('.alert').forEach(a => {
    a.style.transition = 'opacity 0.5s';
    a.style.opacity = '0';
    setTimeout(() => a.remove(), 500);
  });
}, 4000);
