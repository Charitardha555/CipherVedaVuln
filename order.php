<?php

require_once __DIR__ . '/data.php';
require_auth();
$productId = (int) ($_GET['product_id'] ?? 501);
$product = find_product($productId);
if (!$product) {
    redirect('catalog.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= h($product['name']) ?> | Cipher Veda</title><link rel="stylesheet" href="style.css"></head>
<body><header class="navbar"><div class="container nav-inner"><a class="logo" href="dashboard.php"><span>CIPHER</span> VEDA</a><nav><a href="dashboard.php">Overview</a><a href="catalog.php">Solutions</a><a href="profile.php">Account</a><a href="invoice.php">Invoices</a><form class="global-search" action="search.php" method="GET"><input type="search" name="q" placeholder="Search" aria-label="Search solutions" autocomplete="off"><div class="search-results" hidden></div></form><a href="logout.php" class="nav-login">Sign Out</a></nav></div></header>
<main class="section"><div class="container product-detail"><div class="product-copy"><span class="section-label"><?= h($product['category']) ?></span><h1><?= h($product['name']) ?></h1><p><?= h($product['description']) ?></p><div class="feature-list"><span>Connected workspace and reporting</span><span>Role-based business controls</span><span>Priority implementation support</span></div></div><div class="auth-card"><div class="product-meta"><span><?= h($product['sku']) ?></span><strong><?= h($product['badge']) ?></strong></div><div class="price-display"><span>Starting from</span><strong>₹<?= money($product['price']) ?></strong></div><form method="POST" action="checkout.php?stage=cart"><input type="hidden" name="action" value="add_product"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><label for="quantity">Quantity</label><input id="quantity" type="number" name="quantity" min="1" max="10" value="1" required><input type="hidden" name="price" value="<?= h($product['price']) ?>"><button class="btn primary auth-button" type="submit">Add to checkout</button></form></div></div></main><script src="search-bar.js"></script></body></html>
