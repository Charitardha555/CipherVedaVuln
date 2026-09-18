<?php

require_once __DIR__ . '/data.php';
$user = require_auth();
$category = trim($_GET['category'] ?? '');
$products = all_products($category !== '' ? $category : null);
$categories = array_values(array_unique(array_column(all_products(), 'category')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solutions | Cipher Veda</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="navbar"><div class="container nav-inner"><a class="logo" href="dashboard.php"><span>CIPHER</span> VEDA</a><nav><a href="dashboard.php">Overview</a><a href="catalog.php">Solutions</a><a href="profile.php">Account</a><a href="invoice.php">Invoices</a><form class="global-search" action="search.php" method="GET"><input type="search" name="q" placeholder="Search" aria-label="Search solutions" autocomplete="off"><div class="search-results" hidden></div></form><a href="logout.php" class="nav-login">Sign Out</a></nav></div></header>
<main class="section"><div class="container">
    <div class="section-heading"><span>SOLUTION CATALOG</span><h2>Tools for modern operations</h2><p>Explore the services selected for <?= h($user['company']) ?>.</p></div>
    <div class="category-row"><a class="category-link <?= $category === '' ? 'active' : '' ?>" href="catalog.php">All solutions</a><?php foreach ($categories as $item): ?><a class="category-link <?= $category === $item ? 'active' : '' ?>" href="catalog.php?category=<?= urlencode($item) ?>"><?= h($item) ?></a><?php endforeach; ?></div>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <article class="product-card"><div class="product-meta"><span><?= h($product['category']) ?></span><strong><?= h($product['badge']) ?></strong></div><h3><?= h($product['name']) ?></h3><p><?= h($product['description']) ?></p><div class="product-footer"><strong>₹<?= money($product['price']) ?></strong><a class="btn primary" href="order.php?product_id=<?= (int) $product['id'] ?>">View solution</a></div></article>
        <?php endforeach; ?>
    </div>
</div></main>
<script src="search-bar.js"></script>
</body>
</html>
