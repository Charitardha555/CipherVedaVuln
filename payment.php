<?php

require_once __DIR__ . '/data.php';
$user = require_auth();
$checkout = $_SESSION['checkout'] ?? null;
$product = $checkout ? find_product((int) $checkout['product_id']) : null;
if (!$checkout || !$product) {
    redirect('catalog.php');
}
$quantity = max(1, min(10, (int) $checkout['quantity']));
$serverSubtotal = (float) $product['price'] * $quantity;
$serverShipping = ($checkout['shipping'] ?? 'standard') === 'priority' ? 1499 : 0;
$serverTax = round($serverSubtotal * 0.18, 2);
$serverTotal = $serverSubtotal + $serverTax + $serverShipping;
$displayTotal = (float) ($checkout['client_total'] ?? $serverTotal);
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Payment | Cipher Veda</title><link rel="stylesheet" href="style.css"></head>
<body><header class="navbar"><div class="container nav-inner"><a class="logo" href="dashboard.php"><span>CIPHER</span> VEDA</a><nav><a href="dashboard.php">Overview</a><a href="catalog.php">Solutions</a><a href="profile.php">Account</a><form class="global-search" action="search.php" method="GET"><input type="search" name="q" placeholder="Search" aria-label="Search solutions" autocomplete="off"><div class="search-results" hidden></div></form><a href="logout.php" class="nav-login">Sign Out</a></nav></div></header>
<main class="section"><div class="container payment-layout"><div class="auth-card"><div class="auth-heading"><span>SECURE CHECKOUT</span><h1>Complete payment</h1><p>Your payment is processed through the Cipher Veda secure payment gateway.</p></div><form method="POST" action="payment_callback.php"><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><input type="hidden" name="client_price" value="<?= h($checkout['client_price']) ?>"><input type="hidden" name="amount" value="<?= h($displayTotal) ?>"><input type="hidden" name="currency" value="INR"><label>Payment method</label><select name="method"><option>Corporate card</option><option>Bank transfer</option><option>Purchase order</option></select><label>Account name</label><input name="account_name" value="<?= h($user['name']) ?>" required><button class="btn primary auth-button" type="submit">Pay ₹<?= money($displayTotal) ?></button></form></div><aside class="order-summary"><span>PAYMENT GATEWAY</span><h3>Cipher Pay</h3><p>Transaction authorization · INR</p><div class="summary-row total-row"><span>Amount due</span><strong>₹<?= money($displayTotal) ?></strong></div><span class="gateway-status">Gateway operational</span></aside></div></main><script src="search-bar.js"></script></body></html>
