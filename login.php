<?php

require_once __DIR__ . '/data.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');

    if ($identifier === '') {
        $error = 'Please enter your email or mobile number.';
    } else {
        $user = find_user_by_identifier($identifier);
        $otp = (string) random_int(100000, 999999);
        $challengeId = bin2hex(random_bytes(16));
        $expiresAt = time() + OTP_TTL_SECONDS;

        $_SESSION['identifier'] = $identifier;

        $_SESSION['otp_challenge'] = [
            'id' => $challengeId,
            'user_id' => $user['id'] ?? null,
            'otp_hash' => hash('sha256', $otp),
            'expires_at' => $expiresAt,
            'attempts' => 0,
            'used' => false,
        ];

        error_log(
            APP_NAME . ' OTP delivery for ' . $identifier . ': ' . $otp
        );

        $payload = [
            'challenge' => $challengeId,
            'user_id' => $user['id'] ?? null,
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ];

        $_SESSION['otp_token'] = rawurlencode(
            base64_encode(json_encode($payload, JSON_THROW_ON_ERROR))
        );

        header('Location: login.php?step=otp');
        exit;
    }
}

$step = $_GET['step'] ?? 'identifier';
$otpToken = $_SESSION['otp_token'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h(APP_NAME) ?> | Client Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

<form class="global-search auth-search" action="search.php" method="GET">
    <input
        type="search"
        name="q"
        placeholder="Search solutions"
        aria-label="Search solutions"
        autocomplete="off"
    >
    <div class="search-results" hidden></div>
</form>

<div class="auth-wrapper">

    <div class="auth-brand">
        <a href="index.html">
            <span>CIPHER</span> VEDA
        </a>
        <p>Secure Client Portal</p>
    </div>

    <div class="auth-card">

        <?php if ($step === 'identifier'): ?>

            <div class="auth-heading">
                <span>CLIENT ACCESS</span>
                <h1>Sign in to your account</h1>
                <p>Enter your registered email address or mobile number.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert error">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <label>Email or Mobile Number</label>

                <input
                    type="text"
                    name="identifier"
                    placeholder="you@example.com"
                    autocomplete="off"
                    required
                >

                <button type="submit" class="btn primary auth-button">
                    Continue
                </button>
            </form>

        <?php else: ?>

            <div class="auth-heading">
                <span>VERIFICATION</span>
                <h1>Enter verification code</h1>
                <p>
                    Enter the six-digit verification code sent to your
                    registered contact.
                </p>
            </div>

            <form method="POST" action="verify.php">

                <?php if ($otpToken !== ''): ?>
                    <input
                        type="hidden"
                        name="challenge_token"
                        value="<?= h($otpToken) ?>"
                    >
                <?php endif; ?>

                <label>Verification Code</label>

                <input
                    type="text"
                    name="otp"
                    inputmode="numeric"
                    maxlength="6"
                    placeholder="Enter 6-digit code"
                    autocomplete="off"
                    required
                >

                <button type="submit" class="btn primary auth-button">
                    Verify &amp; Continue
                </button>

            </form>

        <?php endif; ?>

    </div>

    <a class="back-home" href="register.php">
        Create a new account
    </a>

    <a class="back-home" href="index.html">
        ← Back to Cipher Veda
    </a>

</div>

<script src="search-bar.js"></script>
</body>
</html>