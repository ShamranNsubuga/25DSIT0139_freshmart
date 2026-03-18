<?php
// admin/includes/sidebar.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? $pageTitle . ' — ' : '' ?>FreshMart Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/freshmart/assets/css/style.css">
</head>
<body>
<div class="admin-layout">

<!-- SIDEBAR -->
<div class="admin-sidebar">
  <div class="sidebar-logo">Fresh<span>Mart</span><small>Admin Panel</small></div>

  <div class="sidebar-label">Main</div>
  <a href="/freshmart/admin/dashboard.php" class="sidebar-item <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"><span class="si">📊</span> Dashboard</a>
  <a href="/freshmart/admin/orders.php" class="sidebar-item <?= $currentPage === 'orders.php' ? 'active' : '' ?>">
    <span class="si">📋</span> Orders
    <?php
    $pendingCount = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
    if ($pendingCount > 0): ?>
    <span class="sidebar-badge"><?= $pendingCount ?></span>
    <?php endif; ?>
  </a>
  <a href="/freshmart/admin/products.php" class="sidebar-item <?= $currentPage === 'products.php' ? 'active' : '' ?>"><span class="si">🛍️</span> Products</a>
  <a href="/freshmart/admin/inventory.php" class="sidebar-item <?= $currentPage === 'inventory.php' ? 'active' : '' ?>"><span class="si">📦</span> Inventory</a>

  <div class="sidebar-label">People</div>
  <a href="/freshmart/admin/customers.php" class="sidebar-item <?= $currentPage === 'customers.php' ? 'active' : '' ?>"><span class="si">👥</span> Customers</a>
  <a href="/freshmart/admin/staff.php" class="sidebar-item <?= $currentPage === 'staff.php' ? 'active' : '' ?>"><span class="si">🧑‍💼</span> Staff</a>

  <div class="sidebar-label">Store</div>
  <a href="/freshmart/admin/promotions.php" class="sidebar-item <?= $currentPage === 'promotions.php' ? 'active' : '' ?>"><span class="si">🏷️</span> Promotions</a>
  <a href="/freshmart/admin/reports.php" class="sidebar-item <?= $currentPage === 'reports.php' ? 'active' : '' ?>"><span class="si">📈</span> Reports</a>
  <a href="/freshmart/admin/settings.php" class="sidebar-item <?= $currentPage === 'settings.php' ? 'active' : '' ?>"><span class="si">⚙️</span> Settings</a>

  <div style="flex:1"></div>
  <a href="/freshmart/index.php" class="sidebar-item" target="_blank"><span class="si">🌐</span> View Store</a>
  <a href="/freshmart/admin/logout.php" class="sidebar-item" style="border-top:1px solid rgba(255,255,255,0.08)"><span class="si">🚪</span> Sign Out</a>
</div>

<!-- MAIN -->
<div class="admin-main">
<div class="admin-topbar">
  <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
  <div class="topbar-right">
    <a href="/freshmart/admin/products.php?action=add" class="btn-submit" style="width:auto;padding:9px 18px;font-size:13px;display:inline-block;">+ Add Product</a>
    <div style="font-size:20px;cursor:pointer;">🔔</div>
    <div class="admin-avatar" title="<?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>"><?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 2)) ?></div>
  </div>
</div>
<div class="admin-content">
