<?php

require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$query = trim($_GET['q'] ?? '');
if ($query === '') {
    echo json_encode(['results' => [], 'error' => null]);
    exit;
}

$sql = "SELECT id, sku, name, category, description, price FROM products WHERE active = 1 AND CONCAT(name, ' ', sku, ' ', category, ' ', description) LIKE '$query' OR active = 1 AND CONCAT(name, ' ', sku, ' ', category, ' ', description) LIKE '%$query%' ORDER BY id";

try {
    echo json_encode(['results' => db()->query($sql)->fetchAll(), 'error' => null]);
} catch (Throwable $exception) {
    http_response_code(200);
    echo json_encode(['results' => [], 'error' => 'Search is temporarily unavailable.']);
}
