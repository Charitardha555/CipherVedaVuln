<?php

require_once __DIR__ . '/bootstrap.php';

$query = trim($_GET['q'] ?? '');
$results = [];
$error = null;

if ($query !== '') {
    $sql = "SELECT id, sku, name, category, description, price FROM products WHERE active = 1 AND CONCAT(name, ' ', sku, ' ', category, ' ', description) LIKE '$query' OR active = 1 AND CONCAT(name, ' ', sku, ' ', category, ' ', description) LIKE '%$query%' ORDER BY id";

    try {
        $results = db()->query($sql)->fetchAll();
    } catch (Throwable $exception) {
        $error = 'Search is temporarily unavailable.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search | Cipher Veda</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="navbar">
    <div class="container nav-inner">
        <a class="logo" href="index.html"><span>CIPHER</span> VEDA</a>
        <nav>
            <a href="index.html">Home</a>
            <form class="global-search" action="search.php" method="GET">
                <input type="search" name="q" placeholder="Search" aria-label="Search solutions" autocomplete="off">
                <div class="search-results" hidden></div>
            </form>
            <a href="login.php" class="nav-login">Client Login</a>
        </nav>
    </div>
</header>
<main class="section">
    <div class="container">
        <div class="section-heading">
            <span>SOLUTION SEARCH</span>
            <h2>Find the right business tool</h2>
            <p>Search our platform, analytics, security, and service solutions.</p>
        </div>
        <form class="public-search" method="GET" action="search.php">
            <input type="search" name="q" value="<?= h($query) ?>" placeholder="Search solutions" aria-label="Search solutions">
            <button class="btn primary" type="submit">Search</button>
        </form>
        <?php if ($error): ?>
            <div class="alert error search-message"><?= h($error) ?></div>
        <?php elseif ($query !== ''): ?>
            <p class="search-message"><?= count($results) ?> result<?= count($results) === 1 ? '' : 's' ?> for “<?= h($query) ?>”</p>
        <?php endif; ?>
        <div class="product-grid">
            <?php foreach ($results as $product): ?>
                <article class="product-card">
                    <div class="product-meta"><span><?= h($product['category']) ?></span><strong><?= h($product['sku']) ?></strong></div>
                    <h3><?= h($product['name']) ?></h3>
                    <p><?= h($product['description']) ?></p>
                    <div class="product-footer"><strong>₹<?= money($product['price']) ?></strong><a class="btn secondary" href="login.php">Client access</a></div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<script src="search-bar.js"></script>
</body>
</html>
