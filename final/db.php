<?php
$host = '127.0.0.1';
$user = 'root';
$password = '';
$dbName = 'online_order_system';

$mysqli = new mysqli($host, $user, $password);
if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

$mysqli->query("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$mysqli->select_db($dbName);

$mysqli->query(
    "CREATE TABLE IF NOT EXISTS orders (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        customer VARCHAR(255) NOT NULL,
        subtotal INT UNSIGNED NOT NULL DEFAULT 0,
        discount INT UNSIGNED NOT NULL DEFAULT 0,
        total INT UNSIGNED NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$mysqli->query(
    "CREATE TABLE IF NOT EXISTS order_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id INT UNSIGNED NOT NULL,
        product VARCHAR(100) NOT NULL,
        qty INT UNSIGNED NOT NULL DEFAULT 0,
        price INT UNSIGNED NOT NULL DEFAULT 0,
        subtotal INT UNSIGNED NOT NULL DEFAULT 0,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

function db_escape($value)
{
    global $mysqli;
    return $mysqli->real_escape_string($value);
}
