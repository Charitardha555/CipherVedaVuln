<?php

require_once __DIR__ . '/data.php';
$user = require_auth();
$stage = $_GET['stage'] ?? 'cart';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_product') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $product = find_product($productId);
        $quantity = max(1, min(10, (int) ($_POST['quantity'] ?? 1)));
        if (!$product) {
            redirect('catalog.php');
        }
        $_SESSION['checkout'] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'client_price' => (float) $product['price'],
        ];
        redirect('checkout.php?stage=cart');
    }

    if ($action === 'cart') {
        redirect('checkout.php?stage=address');
    }

    if (!isset($_SESSION['checkout'])) {
        redirect('catalog.php');
    }

    if (isset($_POST['client_price'])) {
        $_SESSION['checkout']['client_price'] = (float) $_POST['client_price'];
    }

    if ($action === 'address') {
        $_SESSION['checkout']['address'] = [
            'name' => trim($_POST['name'] ?? $user['name']),
            'company' => trim($_POST['company'] ?? $user['company']),
            'line1' => trim($_POST['line1'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'postal_code' => trim($_POST['postal_code'] ?? ''),
        ];
        redirect('checkout.php?stage=shipping');
    }

    if ($action === 'shipping') {
        $_SESSION['checkout']['shipping'] = $_POST['shipping'] ?? 'standard';
        $_SESSION['checkout']['client_discount'] = (float) ($_POST['discount'] ?? 0);
        redirect('checkout.php?stage=review');
    }

    if ($action === 'review') {
        $_SESSION['checkout']['client_subtotal'] = (float) ($_POST['subtotal'] ?? 0);
        $_SESSION['checkout']['client_tax'] = (float) ($_POST['tax'] ?? 0);
        $_SESSION['checkout']['client_shipping'] = (float) ($_POST['shipping_amount'] ?? 0);
        $_SESSION['checkout']['client_total'] = (float) ($_POST['total'] ?? 0);
        redirect('payment.php');
    }
}

$checkout = $_SESSION['checkout'] ?? null;
$product = $checkout ? find_product((int) $checkout['product_id']) : null;
if (!$checkout || !$product) {
    redirect('catalog.php');
}

$quantity = max(1, min(10, (int) $checkout['quantity']));
$unitPrice = (float) $checkout['client_price'];
$subtotal = $unitPrice * $quantity;
$discount = min($subtotal, max(0, (float) ($checkout['client_discount'] ?? 0)));
$shipping = ($checkout['shipping'] ?? 'standard') === 'priority' ? 1499 : 0;
$tax = round(($subtotal - $discount) * 0.18, 2);
$total = $subtotal - $discount + $tax + $shipping;
$address = $checkout['address'] ?? ['name' => $user['name'], 'company' => $user['company'], 'line1' => '', 'city' => '', 'postal_code' => ''];
$stageNames = ['cart' => 'Cart', 'address' => 'Details', 'shipping' => 'Delivery', 'review' => 'Review'];
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Checkout | Cipher Veda</title><link rel="stylesheet" href="style.css"></head>
<body><header class="navbar"><div class="container nav-inner"><a class="logo" href="dashboard.php"><span>CIPHER</span> VEDA</a><nav><a href="dashboard.php">Overview</a><a href="catalog.php">Solutions</a><a href="profile.php">Account</a><form class="global-search" action="search.php" method="GET"><input type="search" name="q" placeholder="Search" aria-label="Search solutions" autocomplete="off"><div class="search-results" hidden></div></form><a href="logout.php" class="nav-login">Sign Out</a></nav></div></header>
<main class="section"><div class="container checkout-layout"><div><div class="section-heading compact-heading"><span>CHECKOUT</span><h2><?= h($stageNames[$stage] ?? 'Checkout') ?></h2><div class="checkout-steps"><?php foreach ($stageNames as $key => $label): ?><span class="<?= $key === $stage ? 'active' : '' ?>"><?= h($label) ?></span><?php endforeach; ?></div></div>
<?php if ($stage === 'cart'): ?><form class="auth-card" method="POST"><input type="hidden" name="action" value="cart"><input type="hidden" name="client_price" value="<?= h($unitPrice) ?>"><div class="checkout-item"><div><span><?= h($product['category']) ?></span><h3><?= h($product['name']) ?></h3><p>Quantity: <?= $quantity ?></p></div><strong>₹<?= money($unitPrice * $quantity) ?></strong></div><button class="btn primary auth-button" type="submit">Continue to details</button></form>
<?php elseif ($stage === 'address'): ?><form class="auth-card" method="POST"><input type="hidden" name="action" value="address"><input type="hidden" name="client_price" value="<?= h($unitPrice) ?>"><label>Contact name</label><input name="name" value="<?= h($address['name']) ?>" required><label>Company</label><input name="company" value="<?= h($address['company']) ?>" required><label>Address</label><input name="line1" value="<?= h($address['line1']) ?>" required><label>City</label><input name="city" value="<?= h($address['city']) ?>" required><label>Postal code</label><input name="postal_code" value="<?= h($address['postal_code']) ?>" required><button class="btn primary auth-button" type="submit">Continue to delivery</button></form>
<?php elseif ($stage === 'shipping'): ?><form class="auth-card" method="POST"><input type="hidden" name="action" value="shipping"><input type="hidden" name="client_price" value="<?= h($unitPrice) ?>"><label>Delivery option</label><select name="shipping"><option value="standard">Standard delivery · Free</option><option value="priority">Priority delivery · ₹1,499</option></select><label>Discount code</label><input name="discount" value="0" inputmode="decimal"><button class="btn primary auth-button" type="submit">Continue to review</button></form>
<?php else: ?><form class="auth-card" method="POST"><input type="hidden" name="action" value="review"><input type="hidden" name="client_price" value="<?= h($unitPrice) ?>"><div class="summary-row"><span>Subtotal</span><strong>₹<?= money($subtotal) ?></strong></div><div class="summary-row"><span>Discount</span><strong>-₹<?= money($discount) ?></strong></div><div class="summary-row"><span>Tax</span><strong>₹<?= money($tax) ?></strong></div><div class="summary-row"><span>Delivery</span><strong>₹<?= money($shipping) ?></strong></div><div class="summary-row total-row"><span>Total</span><strong>₹<?= money($total) ?></strong></div><input type="hidden" name="subtotal" value="<?= h($subtotal) ?>"><input type="hidden" name="tax" value="<?= h($tax) ?>"><input type="hidden" name="shipping_amount" value="<?= h($shipping) ?>"><input type="hidden" name="total" value="<?= h($total) ?>"><button class="btn primary auth-button" type="submit">Continue to payment</button></form><?php endif; ?></div>
<aside class="order-summary"><span>ORDER SUMMARY</span><h3><?= h($product['name']) ?></h3><p><?= h($product['sku']) ?> · <?= $quantity ?> unit<?= $quantity === 1 ? '' : 's' ?></p><div class="summary-row"><span>Current total</span><strong>₹<?= money($total) ?></strong></div><a href="catalog.php" class="btn secondary panel-action">Change solution</a></aside></div></main><script src="search-bar.js"></script></body></html>
