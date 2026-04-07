<?php
/**
 * M'Boost - API: Get Cart Count
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();
header('Content-Type: application/json');

echo json_encode([
    'count' => getCartCount()
]);
