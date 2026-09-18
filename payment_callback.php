<?php

require_once __DIR__ . '/data.php';
$user = require_auth();
$checkout = $_SESSION['checkout'] ?? null;
$product = $checkout ? find_product((int) $checkout['product_id']) : null;
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$checkout || !$product) {
    redirect('catalog.php');
}

$quantity = max(1, min(10, (int) $checkout['quantity']));
$serverSubtotal = (float) $product['price'] * $quantity;
$serverShipping = ($checkout['shipping'] ?? 'standard') === 'priority' ? 1499 : 0;
$serverTax = round($serverSubtotal * 0.18, 2);
$serverTotal = $serverSubtotal + $serverTax + $serverShipping;
$amount = (float) ($_POST['amount'] ?? 0);
$checkout['client_price'] = (float) ($_POST['client_price'] ?? $checkout['client_price']);
$chargedSubtotal = $checkout['client_price'] * $quantity;
$chargedTax = round($chargedSubtotal * 0.18, 2);
$chargedAmount = $chargedSubtotal + $chargedTax + $serverShipping;
$orderNumber = 'CV-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
$transactionId = 'TXN-' . strtoupper(bin2hex(random_bytes(5)));
$invoiceNumber = 'INV-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
$pdo = db();
$pdo->beginTransaction();
try {
    $order = $pdo->prepare('INSERT INTO orders (order_number, user_id, status, subtotal, tax, shipping, total, client_total, checkout_stage, address_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $order->execute([$orderNumber, $user['id'], 'paid', $serverSubtotal, $serverTax, $serverShipping, $chargedAmount, $amount, 10, json_encode($checkout['address'] ?? [])]);
    $orderId = (int) $pdo->lastInsertId();
    $item = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, line_total) VALUES (?, ?, ?, ?, ?, ?)');
    $item->execute([$orderId, $product['id'], $product['name'], $quantity, $checkout['client_price'] ?? $product['price'], $chargedAmount]);
    $payment = $pdo->prepare('INSERT INTO payment_attempts (order_id, transaction_id, gateway_reference, amount, client_amount, currency, method, status, response_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $payment->execute([$orderId, $transactionId, 'GW-' . strtoupper(bin2hex(random_bytes(4))), $chargedAmount, $amount, $_POST['currency'] ?? 'INR', $_POST['method'] ?? 'Corporate card', 'captured', '00']);
    $invoice = $pdo->prepare('INSERT INTO invoices (invoice_number, order_id, user_id, amount, status) VALUES (?, ?, ?, ?, ?)');
    $invoice->execute([$invoiceNumber, $orderId, $user['id'], $chargedAmount, 'paid']);
    $pdo->commit();
    $_SESSION['completed_order'] = ['number' => $orderNumber, 'invoice' => $invoiceNumber, 'total' => $chargedAmount, 'transaction' => $transactionId];
    unset($_SESSION['checkout']);
} catch (Throwable $exception) {
    $pdo->rollBack();
    http_response_code(500);
    exit('Payment could not be completed.');
}
redirect('invoice.php?number=' . urlencode($invoiceNumber));
