<?php
/**
 * M'Boost — Détail de commande — Premium Design
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (!isLoggedIn()) {
    redirect(APP_URL . '/auth/login.php');
}

$userId = $_SESSION['user_id'];
$orderId = (int) ($_GET['id'] ?? 0);

if (!$orderId) {
    redirect(APP_URL . '/client/profil.php');
}

// Get order
$stmt = $pdo->prepare("
    SELECT c.* FROM commandes c
    WHERE c.id = :id AND c.user_id = :user_id
    LIMIT 1
");
$stmt->execute([':id' => $orderId, ':user_id' => $userId]);
$order = $stmt->fetch();

if (!$order) {
    redirect(APP_URL . '/client/profil.php');
}

// Get order items
$stmt = $pdo->prepare("SELECT * FROM lignes_commandes WHERE commande_id = :id");
$stmt->execute([':id' => $orderId]);
$items = $stmt->fetchAll();

// Get payment info
$stmt = $pdo->prepare("SELECT * FROM paiements WHERE commande_id = :id LIMIT 1");
$stmt->execute([':id' => $orderId]);
$payment = $stmt->fetch();

// Status progress
$statusOrder = ['en_attente', 'paye', 'en_preparation', 'en_livraison', 'livre'];
$currentIndex = array_search($order['statut'], $statusOrder);
if ($currentIndex === false) $currentIndex = -1; // annulé

$statusIcons = ['🕐', '💳', '🍹', '🚚', '✅'];
$statusLabels = ['En attente', 'Payé', 'En préparation', 'En livraison', 'Livré'];

$pageTitle = "Commande #" . $orderId;
include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-gray-50/80 to-white min-h-screen py-10 lg:py-14 page-enter">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumb -->
        <div class="flex items-center gap-3 mb-6">
            <a href="<?= APP_URL ?>/client/profil.php" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <nav class="text-sm text-gray-400">
                <a href="<?= APP_URL ?>" class="hover:text-gray-600">Accueil</a>
                <span class="mx-1.5">/</span>
                <a href="<?= APP_URL ?>/client/profil.php" class="hover:text-gray-600">Mon Profil</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-700 font-medium">Commande #<?= $orderId ?></span>
            </nav>
        </div>

        <!-- Title + Status -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-gray-900">Commande #<?= $orderId ?></h1>
                <p class="text-sm text-gray-400 mt-1">Passée le <?= formatDate($order['created_at']) ?></p>
            </div>
            <div class="sm:ml-auto">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold <?= getOrderStatusColor($order['statut']) ?>">
                    <span>●</span>
                    <?= getOrderStatusLabel($order['statut']) ?>
                </span>
            </div>
        </div>

        <?php if ($order['statut'] === 'annule'): ?>
        <!-- Cancelled Banner -->
        <div class="mb-8 flex items-start gap-3 p-5 rounded-2xl bg-red-50 border border-red-100">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-red-100 text-red-600 flex-shrink-0 text-lg">✕</span>
            <div>
                <h3 class="font-bold text-red-800">Commande annulée</h3>
                <p class="text-sm text-red-600 mt-0.5">Cette commande a été annulée.</p>
            </div>
        </div>
        <?php else: ?>
        <!-- Progress Tracker -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8 mb-8">
            <h2 class="text-sm font-bold font-display text-gray-500 uppercase tracking-wider mb-6">Suivi de commande</h2>
            <div class="flex items-start justify-between relative">
                <!-- Progress line (background) -->
                <div class="absolute top-5 left-5 right-5 h-0.5 bg-gray-100 z-0"></div>
                <!-- Progress line (active) -->
                <?php
                $progressPercent = $currentIndex >= 0 ? ($currentIndex / (count($statusOrder) - 1)) * 100 : 0;
                ?>
                <div class="absolute top-5 left-5 h-0.5 bg-gradient-to-r from-green-500 to-emerald-400 z-0 transition-all duration-500"
                     style="width: calc(<?= $progressPercent ?>% - 2.5rem)"></div>

                <?php foreach ($statusOrder as $i => $status): ?>
                <div class="relative z-10 flex flex-col items-center text-center" style="width: <?= 100 / count($statusOrder) ?>%">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shadow-sm transition-all duration-300
                        <?php if ($i <= $currentIndex): ?>
                            bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-green-200/50
                        <?php elseif ($i === $currentIndex + 1): ?>
                            bg-white border-2 border-green-300 text-green-600
                        <?php else: ?>
                            bg-gray-100 text-gray-400
                        <?php endif; ?>
                    ">
                        <?= $statusIcons[$i] ?>
                    </div>
                    <span class="mt-2 text-xs font-semibold hidden sm:block
                        <?= $i <= $currentIndex ? 'text-green-700' : 'text-gray-400' ?>
                    "><?= $statusLabels[$i] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            <!-- Left: Items & Details -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Order Items -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold font-display text-gray-900 mb-5 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-green-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        Articles commandés
                        <span class="badge badge-success ml-auto"><?= count($items) ?> jus</span>
                    </h2>

                    <div class="space-y-4">
                        <?php foreach ($items as $item): ?>
                        <div class="flex items-start gap-4 p-4 rounded-xl bg-gray-50/50 hover:bg-gray-50 transition-colors">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center text-xl flex-shrink-0 shadow-md shadow-green-200/30">
                                🥤
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold font-display text-gray-900"><?= sanitize($item['nom_jus']) ?></h3>
                                <p class="text-sm text-gray-400 mt-0.5">
                                    Taille: <span class="capitalize font-medium text-gray-500"><?= ucfirst($item['taille']) ?></span>
                                    • Qté: <span class="font-medium text-gray-500"><?= $item['quantite'] ?></span>
                                </p>
                                <?php
                                $ings = json_decode($item['ingredients_detail'] ?? '[]', true);
                                if (!empty($ings)):
                                ?>
                                <div class="flex flex-wrap gap-1 mt-2">
                                    <?php foreach ($ings as $ing): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-green-50 text-green-700 text-[10px] font-semibold">
                                        <?= htmlspecialchars($ing['nom'] ?? $ing['name'] ?? '') ?>
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-lg font-extrabold font-display text-green-600"><?= formatPrice($item['prix_total']) ?></p>
                                <?php if ($item['quantite'] > 1): ?>
                                <p class="text-xs text-gray-400"><?= formatPrice($item['prix_unitaire']) ?>/unité</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Subtotals -->
                    <div class="border-t border-gray-100 mt-6 pt-5 space-y-3 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Sous-total</span>
                            <span class="font-medium text-gray-700"><?= formatPrice($order['sous_total']) ?></span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Frais de livraison</span>
                            <span class="font-medium text-gray-700"><?= formatPrice($order['frais_livraison']) ?></span>
                        </div>
                        <div class="border-t border-gray-100 my-3"></div>
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-gray-900">Total</span>
                            <span class="text-2xl font-extrabold font-display text-green-600"><?= formatPrice($order['total']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Sidebar -->
            <div class="space-y-6">

                <!-- Delivery Info -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold font-display text-gray-900 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        Livraison
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Adresse</p>
                            <p class="text-sm text-gray-700 leading-relaxed"><?= nl2br(sanitize($order['adresse_livraison'])) ?></p>
                        </div>
                        <?php if ($order['notes']): ?>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Notes</p>
                            <p class="text-sm text-gray-600 italic"><?= sanitize($order['notes']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold font-display text-gray-900 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-orange-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        Paiement
                    </h2>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50">
                            <?php if ($order['mode_paiement'] === 'orange_money'): ?>
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 flex items-center justify-center text-lg shadow-sm">📱</div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Orange Money</p>
                                <p class="text-xs text-gray-400">Mobile Money</p>
                            </div>
                            <?php else: ?>
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-blue-500 flex items-center justify-center text-lg shadow-sm">📲</div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">MVola</p>
                                <p class="text-xs text-gray-400">Telma Mobile Money</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($payment && $payment['numero_transaction']): ?>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">N° Transaction</p>
                            <p class="text-sm font-mono font-bold text-gray-700"><?= sanitize($payment['numero_transaction']) ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($payment): ?>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Statut paiement</p>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold
                                <?php
                                switch ($payment['statut']) {
                                    case 'valide': echo 'bg-green-100 text-green-700'; break;
                                    case 'en_attente': echo 'bg-yellow-100 text-yellow-700'; break;
                                    case 'refuse': echo 'bg-red-100 text-red-700'; break;
                                    default: echo 'bg-gray-100 text-gray-700';
                                }
                                ?>
                            ">
                                <?php
                                switch ($payment['statut']) {
                                    case 'valide': echo '✅ Validé'; break;
                                    case 'en_attente': echo '🕐 En attente'; break;
                                    case 'refuse': echo '❌ Refusé'; break;
                                    default: echo $payment['statut'];
                                }
                                ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold font-display text-gray-900 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-purple-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        Historique
                    </h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between py-2 border-b border-gray-50">
                            <span class="text-gray-500">Commande passée</span>
                            <span class="font-medium text-gray-700"><?= formatDate($order['created_at']) ?></span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-gray-500">Dernière mise à jour</span>
                            <span class="font-medium text-gray-700"><?= formatDate($order['updated_at']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Action: Pay if pending -->
                <?php if ($order['statut'] === 'en_attente'): ?>
                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl border border-yellow-200/50 p-6">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center text-lg flex-shrink-0">💳</div>
                        <div>
                            <h3 class="font-bold text-yellow-800 text-sm">En attente de paiement</h3>
                            <p class="text-xs text-yellow-700/70 mt-0.5">Complétez le paiement pour que votre commande soit traitée.</p>
                        </div>
                    </div>
                    <a href="<?= APP_URL ?>/client/paiement.php?commande=<?= $orderId ?>"
                       class="mt-4 w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm
                              rounded-xl shadow-lg shadow-green-200/50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 btn-shine">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Procéder au paiement
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Back link -->
        <div class="mt-10 text-center">
            <a href="<?= APP_URL ?>/client/profil.php"
               class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-gray-600 transition-colors group">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour à mon profil
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
