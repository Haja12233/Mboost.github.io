<?php
/**
 * M'Boost - Admin Order Detail
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

// Check admin authentication
if (!isAdminLoggedIn()) {
    redirect(APP_URL . '/auth/admin.php');
}
ensureOrderStatusHistoryTable($pdo);

$orderId = (int) ($_GET['id'] ?? 0);
if (!$orderId) {
    redirect(APP_URL . '/admin/commandes.php');
}

// Get order
$stmt = $pdo->prepare("
    SELECT c.*, u.nom, u.prenom, u.email, u.telephone, u.adresse_livraison as user_adresse
    FROM commandes c
    JOIN users u ON c.user_id = u.id
    WHERE c.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    redirect(APP_URL . '/admin/commandes.php');
}

// Get order items
$stmt = $pdo->prepare("SELECT * FROM lignes_commandes WHERE commande_id = :id");
$stmt->execute([':id' => $orderId]);
$items = $stmt->fetchAll();

// Get payment
$stmt = $pdo->prepare("SELECT * FROM paiements WHERE commande_id = :id LIMIT 1");
$stmt->execute([':id' => $orderId]);
$payment = $stmt->fetch();

$statusOrder = ['en_attente', 'paye', 'en_preparation', 'en_livraison', 'livre'];
$statusLabels = [
    'en_attente' => 'En attente',
    'paye' => 'Payee',
    'en_preparation' => 'Preparation',
    'en_livraison' => 'En livraison',
    'livre' => 'Livree',
];
$currentStatusIndex = array_search($order['statut'], $statusOrder, true);
if ($currentStatusIndex === false) {
    $currentStatusIndex = -1;
}

$statusTimestamps = [];
$stmt = $pdo->prepare("
    SELECT nouveau_statut, created_at
    FROM commande_statuts_historique
    WHERE commande_id = :id
    ORDER BY created_at ASC, id ASC
");
$stmt->execute([':id' => $orderId]);
$statusHistory = $stmt->fetchAll();

foreach ($statusHistory as $row) {
    $s = (string) ($row['nouveau_statut'] ?? '');
    if ($s !== '' && !isset($statusTimestamps[$s])) {
        $statusTimestamps[$s] = $row['created_at'];
    }
}

if (!isset($statusTimestamps['en_attente'])) {
    $statusTimestamps['en_attente'] = $order['created_at'];
}
if (in_array($order['statut'], $statusOrder, true) && !isset($statusTimestamps[$order['statut']])) {
    $statusTimestamps[$order['statut']] = $order['updated_at'] ?? $order['created_at'];
}

$pageTitle = "Commande #" . formatOrderNumber($orderId);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> Admin - <?= $pageTitle ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- CSS Fallback -->
    <style>*{box-sizing:border-box}html{font-family:Inter,sans-serif;line-height:1.5}body{margin:0;background:#f3f4f6;color:#1f2937}.bg-white{background:#fff}.bg-gray-100{background:#f3f4f6}.bg-gray-900{background:#111827}.bg-green-50{background:#f0fdf4}.bg-green-100{background:#dcfce7}.bg-green-600{background:#16a34a}.bg-red-100{background:#fee2e2}.bg-yellow-50{background:#fefce8}.bg-blue-50{background:#eff6ff}.bg-purple-50{background:#faf5ff}.text-white{color:#fff}.text-gray-500{color:#6b7280}.text-gray-600{color:#4b5563}.text-gray-700{color:#374151}.text-gray-800{color:#1f2937}.text-green-600{color:#16a34a}.text-green-700{color:#15803d}.text-red-600{color:#dc2626}.text-yellow-800{color:#92400e}.text-blue-600{color:#2563eb}.text-orange-600{color:#ea580c}.rounded-lg{border-radius:.5rem}.rounded-xl{border-radius:.75rem}.rounded-full{border-radius:9999px}.shadow-sm{box-shadow:0 1px 2px rgba(0,0,0,.05)}.shadow{box-shadow:0 1px 3px rgba(0,0,0,.1)}.shadow-lg{box-shadow:0 10px 15px -3px rgba(0,0,0,.1)}.p-2{padding:.5rem}.p-3{padding:.75rem}.p-4{padding:1rem}.p-6{padding:1.5rem}.px-4{padding-left:1rem;padding-right:1rem}.px-6{padding-left:1.5rem;padding-right:1.5rem}.py-2{padding-top:.5rem;padding-bottom:.5rem}.py-3{padding-top:.75rem;padding-bottom:.75rem}.mb-2{margin-bottom:.5rem}.mb-4{margin-bottom:1rem}.mb-6{margin-bottom:1.5rem}.mt-4{margin-top:1rem}.gap-2{gap:.5rem}.gap-3{gap:.75rem}.gap-4{gap:1rem}.items-center{align-items:center}.justify-center{justify-content:center}.justify-between{justify-content:space-between}.w-full{width:100%}.w-64{width:16rem}.max-w-7xl{max-width:80rem}.flex{display:flex}.grid{display:grid}.hidden{display:none}.border{border:1px solid #e5e7eb}.border-t{border-top:1px solid #e5e7eb}.font-medium{font-weight:500}.font-semibold{font-weight:600}.font-bold{font-weight:700}.text-sm{font-size:.875rem}.text-xs{font-size:.75rem}.text-lg{font-size:1.125rem}.text-xl{font-size:1.25rem}.text-2xl{font-size:1.5rem}.min-h-screen{min-height:100vh}.overflow-x-auto{overflow-x:auto}.fixed{position:fixed}.sticky{position:sticky}.top-0{top:0}.inset-y-0{top:0;bottom:0}.left-0{left:0}.z-30{z-index:30}.z-50{z-index:50}.space-y-4>*+*{margin-top:1rem}.space-y-6>*+*{margin-top:1.5rem}.hover\:bg-gray-50:hover{background:#f9fafb}.hover\:bg-gray-800:hover{background:#1f2937}.hover\:text-green-700:hover{color:#15803d}.focus\:border-green-500:focus{border-color:#22c55e}.focus\:outline-none:focus{outline:2px solid transparent;outline-offset:2px}.lg\:ml-64{margin-left:16rem}.lg\:block{display:block}.lg\:hidden{display:none}</style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Inter',sans-serif;}
        .thermal-ticket {
            font-family: "Courier New", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            letter-spacing: 0.2px;
        }
        .thermal-dash {
            border-top: 1px dashed #9ca3af;
            margin: 8px 0;
        }
        .status-line {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 12px;
        }
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 9999px;
            background: #d1d5db;
            flex-shrink: 0;
        }
        .status-dot.done {
            background: #16a34a;
        }
        @media print {
            body {
                background: #fff !important;
            }
            body * {
                visibility: hidden !important;
            }
            #thermal-ticket,
            #thermal-ticket * {
                visibility: visible !important;
            }
            #thermal-ticket {
                position: absolute;
                left: 0;
                top: 0;
                width: 80mm !important;
                max-width: 80mm !important;
                margin: 0 !important;
                border: 0 !important;
                box-shadow: none !important;
                padding: 8px !important;
                border-radius: 0 !important;
                background: #fff !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-white hidden lg:block">
        <div class="flex items-center gap-3 p-6 border-b border-gray-800">
            <span class="text-2xl">🥤</span>
            <span class="font-bold text-lg"><?= APP_NAME ?> Admin</span>
        </div>
        <nav class="p-4 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg">Dashboard</a>
            <a href="commandes.php" class="flex items-center gap-3 px-4 py-3 bg-green-600 rounded-lg">Commandes</a>
            <a href="ingredients.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg">Ingrédients</a>
            <a href="clients.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg">Clients</a>
            <a href="annonces.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg">Annonces</a>
            <a href="paiements.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg">Paiements</a>
            <a href="parametres.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 rounded-lg">Paramètres</a>
        </nav>
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-800">
            <a href="<?= APP_URL ?>/auth/logout.php" class="text-red-400 hover:text-red-300 text-sm">Déconnexion</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
        <header class="bg-white shadow-sm sticky top-0 z-30">
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="commandes.php" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800">Commande #<?= formatOrderNumber($orderId) ?></h1>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 rounded-full text-sm font-medium <?= getOrderStatusColor($order['statut']) ?>">
                        <?= getOrderStatusLabel($order['statut']) ?>
                    </span>
                    <a href="historique-statuts.php?commande_id=<?= (int) $orderId ?>"
                        class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50">
                        Voir historique
                    </a>
                    <button type="button" onclick="printThermalTicket()"
                        class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">
                        Imprimer ticket
                    </button>
                </div>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <?php if ($order['statut'] !== 'annule'): ?>
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Suivi commande</h2>
                <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                    <?php foreach ($statusOrder as $index => $statusKey): ?>
                    <div class="p-3 rounded-lg border <?= $index <= $currentStatusIndex ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' ?>">
                        <p class="text-xs font-semibold <?= $index <= $currentStatusIndex ? 'text-green-700' : 'text-gray-500' ?>">
                            Etape <?= $index + 1 ?>
                        </p>
                        <p class="text-sm font-bold <?= $index <= $currentStatusIndex ? 'text-green-700' : 'text-gray-700' ?>">
                            <?= $statusLabels[$statusKey] ?>
                        </p>
                        <?php if (!empty($statusTimestamps[$statusKey])): ?>
                        <p class="text-[11px] <?= $index <= $currentStatusIndex ? 'text-green-600' : 'text-gray-400' ?>">
                            <?= date('d/m H:i', strtotime($statusTimestamps[$statusKey])) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Order Items -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Articles</h2>
                    <div class="space-y-4">
                        <?php foreach ($items as $item): ?>
                        <div class="flex items-start gap-4 pb-4 border-b border-gray-100 last:border-0">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-400 to-yellow-300 flex items-center justify-center text-xl">
                                🥤
                            </div>
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-800"><?= sanitize($item['nom_jus']) ?></h3>
                                <p class="text-sm text-gray-500"><?= ucfirst($item['taille']) ?> • Qté: <?= $item['quantite'] ?></p>
                                <?php
                                $ings = json_decode($item['ingredients_detail'] ?? '[]', true);
                                if (is_array($ings) && !empty($ings)):
                                ?>
                                <div class="mt-2">
                                    <p class="text-xs text-gray-500 font-medium mb-1">Composition du jus</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        <?php foreach ($ings as $ing): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-green-50 text-green-700 text-xs font-semibold">
                                            <?= sanitize(($ing['nom'] ?? $ing['name'] ?? 'Ingredient') . (!empty($ing['quantite']) ? ' x' . (int) $ing['quantite'] : '')) ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <p class="font-medium text-gray-800 mt-1"><?= formatPrice($item['prix_total']) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-t border-gray-200 mt-4 pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Sous-total</span>
                            <span class="font-medium"><?= formatPrice($order['sous_total']) ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Livraison</span>
                            <span class="font-medium"><?= formatPrice($order['frais_livraison']) ?></span>
                        </div>
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total</span>
                            <span class="text-green-600"><?= formatPrice($order['total']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Customer & Delivery -->
                <div class="space-y-6">
                    <div id="thermal-ticket" class="bg-white rounded-xl shadow-sm p-6 thermal-ticket">
                        <div class="text-center">
                            <p class="font-bold text-lg"><?= APP_NAME ?></p>
                            <p class="text-xs text-gray-600">Ticket suivi commande</p>
                            <p class="text-xs text-gray-600">Commande #<?= formatOrderNumber($orderId) ?></p>
                        </div>

                        <div class="thermal-dash"></div>
                        <div class="text-xs">
                            <div class="flex justify-between"><span>Date</span><span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span></div>
                            <div class="flex justify-between"><span>Client</span><span><?= sanitize($order['prenom']) ?></span></div>
                            <div class="flex justify-between"><span>Paiement</span><span><?= $order['mode_paiement'] === 'orange_money' ? 'Orange' : 'MVola' ?></span></div>
                            <div class="flex justify-between"><span>Statut</span><span><?= getOrderStatusLabel($order['statut']) ?></span></div>
                        </div>

                        <div class="thermal-dash"></div>
                        <p class="text-xs font-bold mb-2">DETAIL JUS</p>
                        <div class="space-y-2 text-xs">
                            <?php foreach ($items as $item): ?>
                            <div>
                                <p class="font-bold"><?= sanitize($item['nom_jus']) ?> (<?= ucfirst($item['taille']) ?>) x<?= (int) $item['quantite'] ?></p>
                                <?php
                                $ticketIngredients = json_decode($item['ingredients_detail'] ?? '[]', true);
                                if (is_array($ticketIngredients) && !empty($ticketIngredients)):
                                ?>
                                <ul class="pl-3">
                                    <?php foreach ($ticketIngredients as $ing): ?>
                                    <li>- <?= sanitize(($ing['nom'] ?? $ing['name'] ?? 'Ingredient') . (!empty($ing['quantite']) ? ' x' . (int) $ing['quantite'] : '')) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php else: ?>
                                <p class="text-gray-500">- Composition indisponible</p>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="thermal-dash"></div>
                        <p class="text-xs font-bold mb-2">SUIVI</p>
                        <div class="text-xs">
                            <?php if ($order['statut'] === 'annule'): ?>
                            <div class="status-line"><span class="status-dot done"></span><span>Commande annulee</span></div>
                            <?php else: ?>
                                <?php foreach ($statusOrder as $index => $statusKey): ?>
                                <div class="status-line">
                                    <span class="status-dot <?= $index <= $currentStatusIndex ? 'done' : '' ?>"></span>
                                    <span><?= $statusLabels[$statusKey] ?></span>
                                    <span style="margin-left:auto;color:#6b7280;">
                                        <?= !empty($statusTimestamps[$statusKey]) ? date('d/m H:i', strtotime($statusTimestamps[$statusKey])) : '--/-- --:--' ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="thermal-dash"></div>
                        <div class="text-xs">
                            <div class="flex justify-between"><span>Sous-total</span><span><?= formatPrice($order['sous_total']) ?></span></div>
                            <div class="flex justify-between"><span>Livraison</span><span><?= formatPrice($order['frais_livraison']) ?></span></div>
                            <div class="flex justify-between font-bold"><span>TOTAL</span><span><?= formatPrice($order['total']) ?></span></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Client</h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">Nom</p>
                                <p class="text-gray-800 font-medium"><?= sanitize($order['prenom'] . ' ' . $order['nom']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="text-gray-800"><?= sanitize($order['email']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Téléphone</p>
                                <p class="text-gray-800"><?= sanitize($order['telephone']) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Livraison</h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">Adresse</p>
                                <p class="text-gray-800"><?= nl2br(sanitize($order['adresse_livraison'])) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Mode de paiement</p>
                                <p class="text-gray-800"><?= $order['mode_paiement'] === 'orange_money' ? 'Orange Money' : 'MVola' ?></p>
                            </div>
                            <?php if ($order['notes']): ?>
                            <div>
                                <p class="text-sm text-gray-500">Notes</p>
                                <p class="text-gray-800"><?= sanitize($order['notes']) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <?php if ($payment): ?>
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Paiement</h2>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Statut</span>
                                <span class="px-2 py-1 rounded-full text-xs <?=
                                    $payment['statut'] === 'valide' ? 'bg-green-100 text-green-700' :
                                    ($payment['statut'] === 'refuse' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700')
                                ?>">
                                    <?= $payment['statut'] === 'valide' ? 'Validé' : ($payment['statut'] === 'refuse' ? 'Refusé' : 'En attente') ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Montant</span>
                                <span class="font-medium"><?= formatPrice($payment['montant']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">N° Transaction</span>
                                <span class="font-mono"><?= sanitize($payment['numero_transaction']) ?></span>
                            </div>
                            <?php if ($payment['justificatif']): ?>
                            <div class="pt-2">
                                <a href="<?= UPLOAD_URL . 'payments/' . sanitize($payment['justificatif']) ?>" target="_blank"
                                    class="text-blue-600 hover:text-blue-700 font-medium">
                                    Voir le justificatif →
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Actions</h2>
                        <form method="POST" action="commandes.php" class="space-y-3">
                            <input type="hidden" name="order_id" value="<?= $orderId ?>">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Changer le statut</label>
                                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:border-green-500 outline-none">
                                    <option value="en_attente" <?= $order['statut'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                    <option value="paye" <?= $order['statut'] === 'paye' ? 'selected' : '' ?>>Payé</option>
                                    <option value="en_preparation" <?= $order['statut'] === 'en_preparation' ? 'selected' : '' ?>>En préparation</option>
                                    <option value="en_livraison" <?= $order['statut'] === 'en_livraison' ? 'selected' : '' ?>>En livraison</option>
                                    <option value="livre" <?= $order['statut'] === 'livre' ? 'selected' : '' ?>>Livré</option>
                                    <option value="annule" <?= $order['statut'] === 'annule' ? 'selected' : '' ?>>Annulé</option>
                                </select>
                            </div>
                            <button type="submit" name="update_status" value="1"
                                class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 font-medium">
                                Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
    function printThermalTicket() {
        window.print();
    }
    </script>

</body>
</html>
