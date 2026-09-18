<?php

require_once __DIR__ . '/data.php';
$user = require_auth();
$invoices = user_invoices((int) $user['id']);
$selected = null;
$number = trim($_GET['number'] ?? '');
if ($number !== '') {
    $statement = db()->prepare('SELECT invoices.*, orders.order_number FROM invoices JOIN orders ON orders.id = invoices.order_id WHERE invoices.invoice_number = ? LIMIT 1');
    $statement->execute([$number]);
    $selected = $statement->fetch() ?: null;
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Invoices | Cipher Veda</title><link rel="stylesheet" href="style.css"></head>
<body><header class="navbar"><div class="container nav-inner"><a class="logo" href="dashboard.php"><span>CIPHER</span> VEDA</a><nav><a href="dashboard.php">Overview</a><a href="catalog.php">Solutions</a><a href="profile.php">Account</a><a href="invoice.php">Invoices</a><form class="global-search" action="search.php" method="GET"><input type="search" name="q" placeholder="Search" aria-label="Search solutions" autocomplete="off"><div class="search-results" hidden></div></form><a href="logout.php" class="nav-login">Sign Out</a></nav></div></header>
<main class="section"><div class="container"><div class="section-heading"><span>BILLING CENTER</span><h2>Invoices</h2><p>Review your account billing and payment history.</p></div><?php if ($selected): ?><div class="security-box"><div><span class="section-label"><?= h($selected['invoice_number']) ?></span><h2>Payment received</h2><p>Order <?= h($selected['order_number']) ?> · <?= h($selected['issued_at']) ?></p></div><div><span class="section-label">TOTAL</span><h2>₹<?= money($selected['amount']) ?></h2><span class="gateway-status">Paid</span></div></div><?php endif; ?><section class="portal-panel invoice-panel"><div class="panel-heading"><span>STATEMENTS</span><span><?= count($invoices) ?> records</span></div><?php if ($invoices): ?><?php foreach ($invoices as $invoice): ?><a class="list-row" href="invoice.php?number=<?= urlencode($invoice['invoice_number']) ?>"><div><strong><?= h($invoice['invoice_number']) ?></strong><small><?= h($invoice['issued_at']) ?></small></div><div class="row-value"><strong>₹<?= money($invoice['amount']) ?></strong><small><?= h(ucfirst($invoice['status'])) ?></small></div></a><?php endforeach; ?><?php else: ?><p class="empty-state">Invoices will appear after your first completed order.</p><?php endif; ?></section></div></main><script src="search-bar.js"></script></body></html>
