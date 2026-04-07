<?php
/**
 * M'Boost - API: Add to Cart
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();
header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifie']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Donnees invalides']);
    exit;
}

$csrfToken = (string) ($input['csrf_token'] ?? '');
if (!verifyCSRF($csrfToken)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'error' => 'Requete invalide (CSRF)']);
    exit;
}

$ingredients = $input['ingredients'] ?? [];
$size = $input['size'] ?? 'moyen';
$name = $input['name'] ?? 'Jus personnalise';

if (empty($ingredients) || !is_array($ingredients)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Aucun ingredient selectionne']);
    exit;
}

if (!in_array($size, ['petit', 'moyen', 'grand'], true)) {
    $size = 'moyen';
}

$normalizedIngredients = [];
foreach ($ingredients as $row) {
    $id = (int) ($row['id'] ?? 0);
    $qty = (int) ($row['quantite'] ?? 1);
    if ($id <= 0) {
        continue;
    }
    $normalizedIngredients[$id] = ($normalizedIngredients[$id] ?? 0) + max(1, min($qty, 20));
}

if (empty($normalizedIngredients) || count($normalizedIngredients) > 25) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Composition invalide']);
    exit;
}

$ids = array_keys($normalizedIngredients);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT id, nom, prix FROM ingredients WHERE actif = 1 AND id IN ($placeholders)");
$stmt->execute($ids);
$rows = $stmt->fetchAll();

if (count($rows) !== count($ids)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Un ou plusieurs ingredients sont invalides']);
    exit;
}

$subtotal = 0.0;
$ingredientsDetail = [];
foreach ($rows as $row) {
    $id = (int) $row['id'];
    $qty = $normalizedIngredients[$id];
    $unitPrice = (float) $row['prix'];
    $lineTotal = $unitPrice * $qty;

    $subtotal += $lineTotal;
    $ingredientsDetail[] = [
        'id' => $id,
        'nom' => $row['nom'],
        'quantite' => $qty,
        'prix' => $unitPrice,
        'sous_total' => $lineTotal,
    ];
}

$multiplier = (float) (JUICE_SIZES[$size]['multiplier'] ?? 1.0);
$serverPrice = round($subtotal * $multiplier);

$cartKey = 'jus_' . time() . '_' . rand(1000, 9999);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$_SESSION['cart'][$cartKey] = [
    'nom' => sanitize((string) $name),
    'taille' => $size,
    'quantite' => 1,
    'prix' => (float) $serverPrice,
    'ingredients' => $ingredientsDetail,
];

echo json_encode([
    'success' => true,
    'cart_count' => getCartCount(),
    'cart_key' => $cartKey,
]);
