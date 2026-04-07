<?php
/**
 * M'Boost — Valider la commande (Checkout) — Premium Design
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = APP_URL . '/client/commande.php';
    redirect(APP_URL . '/auth/login.php');
}

if (empty($_SESSION['cart'])) {
    redirect(APP_URL . '/client/panier.php');
}

$userId = $_SESSION['user_id'];
$error = '';

$stmt = $pdo->prepare("SELECT adresse_livraison FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

$deliveryFee = (float) getParam('frais_livraison', 'frais_livraison', DELIVERY_FEE_DEFAULT);

$cart = $_SESSION['cart'];
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['prix'] * $item['quantite'];
}
$total = $subtotal + $deliveryFee;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCSRFOrAbort();
    $adresse_livraison = trim($_POST['adresse_livraison'] ?? '');
    $mode_paiement = $_POST['mode_paiement'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($adresse_livraison)) {
        $error = 'Veuillez indiquer une adresse de livraison.';
    } elseif (!in_array($mode_paiement, ['orange_money', 'mvola'])) {
        $error = 'Veuillez sélectionner un mode de paiement.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO commandes (user_id, adresse_livraison, sous_total, frais_livraison, total, statut, mode_paiement, notes)
                VALUES (:user_id, :adresse, :sous_total, :frais, :total, 'en_attente', :mode, :notes)
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':adresse' => $adresse_livraison,
                ':sous_total' => $subtotal,
                ':frais' => $deliveryFee,
                ':total' => $total,
                ':mode' => $mode_paiement,
                ':notes' => $notes
            ]);

            $commandeId = $pdo->lastInsertId();
            logOrderStatusChange($pdo, (int) $commandeId, null, 'en_attente', null, 'Creation commande client');

            foreach ($cart as $key => $item) {
                $stmt = $pdo->prepare("
                    INSERT INTO lignes_commandes (commande_id, nom_jus, taille, quantite, prix_unitaire, prix_total, ingredients_detail)
                    VALUES (:commande_id, :nom_jus, :taille, :quantite, :prix_unitaire, :prix_total, :ingredients)
                ");
                $stmt->execute([
                    ':commande_id' => $commandeId,
                    ':nom_jus' => $item['nom'],
                    ':taille' => $item['taille'],
                    ':quantite' => $item['quantite'],
                    ':prix_unitaire' => $item['prix'],
                    ':prix_total' => $item['prix'] * $item['quantite'],
                    ':ingredients' => json_encode($item['ingredients'] ?? [])
                ]);
            }

            $stmt = $pdo->prepare("
                INSERT INTO paiements (commande_id, montant, mode_paiement, numero_transaction, statut)
                VALUES (:commande_id, :montant, :mode, '', 'en_attente')
            ");
            $stmt->execute([
                ':commande_id' => $commandeId,
                ':montant' => $total,
                ':mode' => $mode_paiement
            ]);

            $pdo->commit();
            $_SESSION['cart'] = [];
            redirect(APP_URL . '/client/paiement.php?commande=' . $commandeId);

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Une erreur est survenue lors de la création de la commande. Veuillez réessayer.';
        }
    }
}

$pageTitle = "Valider la commande";
include __DIR__ . '/../includes/header.php';
?>

<section class="bg-gradient-to-b from-gray-50/80 to-white min-h-screen py-10 lg:py-14 page-enter" x-data="{ selectedPayment: '' }">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Progress Bar -->
        <div class="flex items-center justify-center gap-3 mb-10">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-green-600 text-white flex items-center justify-center text-xs font-bold">✓</div>
                <span class="text-sm font-medium text-green-700 hidden sm:block">Panier</span>
            </div>
            <div class="w-12 h-0.5 bg-green-300"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-green-600 text-white flex items-center justify-center text-xs font-bold">2</div>
                <span class="text-sm font-medium text-green-700 hidden sm:block">Commande</span>
            </div>
            <div class="w-12 h-0.5 bg-gray-200"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gray-200 text-gray-400 flex items-center justify-center text-xs font-bold">3</div>
                <span class="text-sm font-medium text-gray-400 hidden sm:block">Paiement</span>
            </div>
        </div>

        <h1 class="text-3xl font-extrabold font-display text-gray-900 mb-2">Valider votre commande</h1>
        <p class="text-gray-500 mb-8">Vérifiez les détails et choisissez votre mode de paiement.</p>

        <?php if ($error): ?>
        <div class="mb-6 flex items-start gap-3 p-4 rounded-2xl bg-red-50 border border-red-100 animate-scale-in">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-red-100 text-red-600 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </span>
            <p class="text-sm text-red-700 font-medium"><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <form method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <?= csrfInput() ?>
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Delivery Address -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold font-display text-gray-900 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-green-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        Adresse de livraison
                    </h2>
                    <textarea name="adresse_livraison" rows="3" required
                              class="input-modern w-full resize-none"
                              placeholder="Votre adresse complète à Mahajanga..."><?= htmlspecialchars($user['adresse_livraison'] ?? '') ?></textarea>
                </div>

                <!-- Payment Method -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold font-display text-gray-900 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-orange-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        Mode de paiement
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="border-2 rounded-2xl p-5 cursor-pointer transition-all duration-200 hover:border-orange-300"
                               :class="selectedPayment === 'orange_money' ? 'border-orange-500 bg-orange-50 shadow-md shadow-orange-100' : 'border-gray-100'">
                            <input type="radio" name="mode_paiement" value="orange_money" required class="hidden" x-model="selectedPayment">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-gradient-to-br from-orange-400 to-orange-500 rounded-2xl flex items-center justify-center text-2xl shadow-md shadow-orange-200/50">📱</div>
                                <div>
                                    <p class="font-bold text-gray-900">Orange Money</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Paiement rapide & sécurisé</p>
                                </div>
                            </div>
                        </label>

                        <label class="border-2 rounded-2xl p-5 cursor-pointer transition-all duration-200 hover:border-blue-300"
                               :class="selectedPayment === 'mvola' ? 'border-blue-500 bg-blue-50 shadow-md shadow-blue-100' : 'border-gray-100'">
                            <input type="radio" name="mode_paiement" value="mvola" required class="hidden" x-model="selectedPayment">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-500 rounded-2xl flex items-center justify-center text-2xl shadow-md shadow-blue-200/50">📲</div>
                                <div>
                                    <p class="font-bold text-gray-900">MVola</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Telma Mobile Money</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold font-display text-gray-900 mb-4 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                        </div>
                        Notes <span class="text-gray-400 font-normal text-sm">(optionnel)</span>
                    </h2>
                    <textarea name="notes" rows="2"
                              class="input-modern w-full resize-none"
                              placeholder="Instructions spéciales pour la livraison..."></textarea>
                </div>
            </div>

            <!-- Right: Summary -->
            <div class="lg:col-span-1">
                <div class="sticky top-24 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold font-display text-gray-900 mb-5">Récapitulatif</h2>

                    <div class="space-y-3 mb-5 max-h-48 overflow-y-auto pr-1">
                        <?php foreach ($cart as $item): ?>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center text-lg flex-shrink-0">🥤</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate"><?= htmlspecialchars($item['nom']) ?></p>
                                <p class="text-xs text-gray-400">x<?= $item['quantite'] ?> • <?= $item['taille'] ?></p>
                            </div>
                            <p class="text-sm font-bold text-gray-700"><?= formatPrice($item['prix'] * $item['quantite']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-t border-gray-100 my-4"></div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Sous-total</span>
                            <span class="font-medium text-gray-700"><?= formatPrice($subtotal) ?></span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Livraison</span>
                            <span class="font-medium text-gray-700"><?= formatPrice($deliveryFee) ?></span>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 my-4"></div>

                    <div class="flex justify-between items-center mb-6">
                        <span class="text-lg font-bold text-gray-900">Total</span>
                        <span class="text-2xl font-extrabold font-display text-green-600"><?= formatPrice($total) ?></span>
                    </div>

                    <button type="submit"
                            class="w-full py-3.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold text-sm
                                   rounded-xl shadow-lg shadow-green-200/50 hover:shadow-xl hover:shadow-green-300/50
                                   hover:-translate-y-0.5 transition-all duration-300 btn-shine flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Confirmer la commande
                    </button>

                    <a href="<?= APP_URL ?>/client/panier.php"
                       class="block text-center mt-3 text-sm text-gray-400 hover:text-gray-600 transition-colors">
                        ← Modifier le panier
                    </a>
                </div>
            </div>
        </form>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
