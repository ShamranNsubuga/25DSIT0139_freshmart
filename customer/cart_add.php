<?php
// customer/cart_add.php — AJAX endpoint for adding to cart
require_once '../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$pid = (int)$_POST['product_id'];
$qty = max(1, (int)($_POST['qty'] ?? 1));

$prod = $conn->query("SELECT id, name, price, stock FROM products WHERE id = $pid AND is_active = 1")->fetch_assoc();

if (!$prod) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}
if ($prod['stock'] < 1) {
    echo json_encode(['success' => false, 'message' => 'Out of stock']);
    exit;
}

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if (isset($_SESSION['cart'][$pid])) {
    $_SESSION['cart'][$pid]['qty'] += $qty;
} else {
    $_SESSION['cart'][$pid] = [
        'id'    => $pid,
        'name'  => $prod['name'],
        'price' => $prod['price'],
        'qty'   => $qty
    ];
}

$cartCount = array_sum(array_column($_SESSION['cart'], 'qty'));
echo json_encode([
    'success'      => true,
    'cart_count'   => $cartCount,
    'product_name' => $prod['name']
]);
