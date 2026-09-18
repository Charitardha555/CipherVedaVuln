<?php

require_once __DIR__ . '/data.php';
$user = require_auth();
$profile = $user;
if (isset($_GET['id'])) {
    $profile = current_user_from_id((int) $_GET['id']);
}
if (!$profile) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Account | Cipher Veda</title><link rel="stylesheet" href="style.css"></head>
<body><header class="navbar"><div class="container nav-inner"><a class="logo" href="dashboard.php"><span>CIPHER</span> VEDA</a><nav><a href="dashboard.php">Overview</a><a href="catalog.php">Solutions</a><a href="profile.php">Account</a><a href="invoice.php">Invoices</a><form class="global-search" action="search.php" method="GET"><input type="search" name="q" placeholder="Search" aria-label="Search solutions" autocomplete="off"><div class="search-results" hidden></div></form><a href="logout.php" class="nav-login">Sign Out</a></nav></div></header>
<main class="section"><div class="container"><div class="section-heading"><span>ACCOUNT PROFILE</span><h2><?= h($profile['name'] ?? 'Account unavailable') ?></h2><p>Account details and service preferences.</p></div><?php if ($profile): ?><div class="solution-grid"><div class="solution-card"><div class="icon">COMPANY</div><h3><?= h($profile['company']) ?></h3><p>Organization account</p></div><div class="solution-card"><div class="icon">CONTACT</div><h3><?= h($profile['email']) ?></h3><p><?= h($profile['mobile']) ?></p></div><div class="solution-card"><div class="icon">PLAN</div><h3><?= h($profile['plan']) ?></h3><p><?= h(ucfirst($profile['account_status'])) ?> account</p></div></div><?php else: ?><div class="auth-card"><h1>Account unavailable</h1><p>The requested account could not be found.</p></div><?php endif; ?></div></main><script src="search-bar.js"></script></body></html>
