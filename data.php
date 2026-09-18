<?php

require_once __DIR__ . '/bootstrap.php';

function all_products(?string $category = null): array
{
    if ($category) {
        $statement = db()->prepare('SELECT * FROM products WHERE active = 1 AND category = ? ORDER BY id');
        $statement->execute([$category]);
        return $statement->fetchAll();
    }

    return db()->query('SELECT * FROM products WHERE active = 1 ORDER BY id')->fetchAll();
}

function find_product(int $productId): ?array
{
    $statement = db()->prepare('SELECT * FROM products WHERE id = ? AND active = 1 LIMIT 1');
    $statement->execute([$productId]);
    $product = $statement->fetch();

    return $product ?: null;
}

function find_user_by_identifier(string $identifier): ?array
{
    $statement = db()->prepare('SELECT * FROM users WHERE email = ? OR mobile = ? LIMIT 1');
    $statement->execute([$identifier, $identifier]);
    $user = $statement->fetch();

    return $user ?: null;
}

function current_user_from_id(int $userId): ?array
{
    $statement = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $statement->execute([$userId]);
    $user = $statement->fetch();

    return $user ?: null;
}

function user_orders(int $userId): array
{
    $statement = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
    $statement->execute([$userId]);
    return $statement->fetchAll();
}

function user_invoices(int $userId): array
{
    $statement = db()->prepare('SELECT * FROM invoices WHERE user_id = ? ORDER BY issued_at DESC');
    $statement->execute([$userId]);
    return $statement->fetchAll();
}