<?php

require_once __DIR__ . '/data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

$otp = trim($_POST['otp'] ?? '');
$challenge = $_SESSION['otp_challenge'] ?? null;

if (
    !$challenge ||
    ($challenge['used'] ?? false) ||
    time() > ($challenge['expires_at'] ?? 0)
) {
    redirect('login.php?step=otp&error=expired');
}

$challenge['attempts']++;
$_SESSION['otp_challenge'] = $challenge;

if ($challenge['attempts'] > OTP_MAX_ATTEMPTS) {
    unset(
        $_SESSION['otp_challenge'],
        $_SESSION['otp_token']
    );

    redirect('login.php?step=otp&error=locked');
}

$valid = false;

$token = trim(
    $_POST['challenge_token'] ??
    $_POST['token'] ??
    ''
);

// Reverse the encoding:
// URL decode → Base64 decode → JSON decode
$base64Token = rawurldecode($token);
$jsonToken = base64_decode($base64Token, true);

$decoded = $jsonToken !== false
    ? json_decode($jsonToken, true)
    : null;

$valid =
    is_array($decoded) &&
    ($decoded['challenge'] ?? '') === ($challenge['id'] ?? '') &&
    hash_equals(
        (string) ($decoded['otp'] ?? ''),
        $otp
    );

if (!$valid) {
    redirect('login.php?step=otp&error=invalid');
}

$user = !empty($challenge['user_id'])
    ? current_user_from_id((int) $challenge['user_id'])
    : null;

if (!$user) {
    redirect('login.php?step=otp&error=invalid');
}

$_SESSION['authenticated'] = true;
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['otp_challenge']['used'] = true;

unset($_SESSION['otp_token']);

redirect('dashboard.php');
?>