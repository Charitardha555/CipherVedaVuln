<?php

require_once __DIR__ . '/data.php';

$user = require_auth();
$products = array_slice(all_products(), 0, 3);
$orders = user_orders((int) $user['id']);
$invoices = user_invoices((int) $user['id']);

function portal_status(string $status): string
{
    return ucwords(str_replace('_', ' ', $status));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard | Cipher Veda</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="navbar">
    <div class="container nav-inner">
        <a class="logo" href="index.html"><span>CIPHER</span> VEDA</a>
        <nav>
            <a href="dashboard.php">Overview</a>
            <a href="catalog.php">Solutions</a>
            <a href="profile.php">Account</a>
            <a href="invoice.php">Invoices</a>
            <form class="global-search" action="search.php" method="GET">
                <input type="search" name="q" placeholder="Search" aria-label="Search solutions" autocomplete="off">
                <div class="search-results" hidden></div>
            </form>
            <a href="logout.php" class="nav-login">Sign Out</a>
        </nav>
    </div>
</header>

<main class="section dashboard-section">
    <div class="container">
        <div class="section-heading">
            <span>CLIENT PORTAL</span>
            <h2>Good morning, <?= h($user['name']) ?></h2>
            <p>Keep your services, orders, billing, and account preferences in one place.</p>
        </div>

        <div class="metric-grid portal-metrics">
            <div class="metric"><span>Account plan</span><strong><?= h($user['plan']) ?></strong><small>Active</small></div>
            <div class="metric"><span>Orders</span><strong><?= count($orders) ?></strong><small>Recent activity</small></div>
            <div class="metric"><span>Invoices</span><strong><?= count($invoices) ?></strong><small>Billing records</small></div>
            <div class="metric"><span>Support</span><strong>24/7</strong><small>Priority coverage</small></div>
        </div>

        <div class="section-heading compact-heading">
            <span>RECOMMENDED FOR YOU</span>
            <h2>Business solutions</h2>
        </div>

        <div class="solution-grid">
            <?php foreach ($products as $product): ?>
                <a class="solution-card" href="order.php?product_id=<?= (int) $product['id'] ?>">
                    <div class="icon"><?= h($product['category']) ?></div>
                    <h3><?= h($product['name']) ?></h3>
                    <p><?= h($product['description']) ?></p>
                    <strong class="card-price">₹<?= money($product['price']) ?></strong>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="portal-columns">
            <section class="portal-panel">
                <div class="panel-heading"><span>RECENT ORDERS</span><a href="catalog.php">Browse solutions</a></div>
                <?php if ($orders): ?>
                    <?php foreach (array_slice($orders, 0, 3) as $order): ?>
                        <div class="list-row">
                            <div><strong><?= h($order['order_number']) ?></strong><small><?= h($order['created_at']) ?></small></div>
                            <div class="row-value"><strong>₹<?= money($order['total']) ?></strong><small><?= h(portal_status($order['status'])) ?></small></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-state">Your recent orders will appear here.</p>
                <?php endif; ?>
            </section>

            <section class="portal-panel">
                <div class="panel-heading"><span>ACCOUNT</span><a href="profile.php">View profile</a></div>
                <div class="account-summary"><strong><?= h($user['company']) ?></strong><span><?= h($user['email']) ?></span><span><?= h($user['mobile']) ?></span></div>
                <a class="btn secondary panel-action" href="invoice.php">Open billing center</a>
            </section>
        </div>
    </div>
</main>
<script src="search-bar.js"></script>
</body>
</html>
