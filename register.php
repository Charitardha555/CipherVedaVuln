<?php

require_once __DIR__ . '/data.php';

$error = null;
$values = [
    'name' => '',
    'email' => '',
    'mobile' => '',
    'company' => '',
    'plan' => 'Professional',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $field => $value) {
        $values[$field] = trim($_POST[$field] ?? $value);
    }

    if ($values['name'] === '' || $values['email'] === '' || $values['mobile'] === '' || $values['company'] === '') {
        $error = 'Complete all account details to continue.';
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } else {
        try {
            $statement = db()->prepare('SELECT id FROM users WHERE email = ? OR mobile = ? LIMIT 1');
            $statement->execute([$values['email'], $values['mobile']]);
            if ($statement->fetch()) {
                $error = 'An account already uses that email or mobile number.';
            } else {
                $nextId = (int) db()->query('SELECT COALESCE(MAX(id), 1000) + 1 FROM users')->fetchColumn();
                $insert = db()->prepare('INSERT INTO users (id, name, email, mobile, company, plan, account_status) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $insert->execute([$nextId, $values['name'], $values['email'], $values['mobile'], $values['company'], $values['plan'], 'active']);
                $cart = db()->prepare('INSERT INTO carts (user_id, status) VALUES (?, ?)');
                $cart->execute([$nextId, 'open']);
                redirect('login.php?registered=1');
            }
        } catch (Throwable $exception) {
            $error = 'Registration is temporarily unavailable.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | <?= h(APP_NAME) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
<form class="global-search auth-search" action="search.php" method="GET"><input type="search" name="q" placeholder="Search solutions" aria-label="Search solutions" autocomplete="off"><div class="search-results" hidden></div></form>
<div class="auth-wrapper">
    <div class="auth-brand"><a href="index.html"><span>CIPHER</span> VEDA</a><p>Client Portal</p></div>
    <div class="auth-card">
        <div class="auth-heading"><span>NEW ACCOUNT</span><h1>Create your account</h1><p>Set up access to your Cipher Veda workspace.</p></div>
        <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
        <form method="POST">
            <label>Full name</label><input name="name" value="<?= h($values['name']) ?>" required>
            <label>Work email</label><input type="email" name="email" value="<?= h($values['email']) ?>" required>
            <label>Mobile number</label><input name="mobile" value="<?= h($values['mobile']) ?>" required>
            <label>Company</label><input name="company" value="<?= h($values['company']) ?>" required>
            <label>Plan</label><select name="plan"><option <?= $values['plan'] === 'Professional' ? 'selected' : '' ?>>Professional</option><option <?= $values['plan'] === 'Business' ? 'selected' : '' ?>>Business</option><option <?= $values['plan'] === 'Enterprise' ? 'selected' : '' ?>>Enterprise</option></select>
            <button type="submit" class="btn primary auth-button">Create account</button>
        </form>
    </div>
    <a class="back-home" href="login.php">Already have an account? Sign in</a>
</div>
<script src="search-bar.js"></script>
</body>
</html>
